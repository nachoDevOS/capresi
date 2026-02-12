<?php

namespace App\Http\Controllers;

use App\Models\Cashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

// Models
use App\Models\Loan;
use App\Models\LoanDay;
use App\Models\LoanDayAgent;
use App\Models\User;
use App\Models\Route;
use App\Models\RouteCollector;
use App\Models\PawnRegisterPayment;
use App\Models\CashierMovement;
use App\Models\HistoryReportDailyList;
use App\Models\PawnRegister;
use App\Models\PawnRegisterDetail;
use App\Models\PawnRegisterMonthAgent;
use PhpParser\Node\Stmt\Return_;

class ReportController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    //:::::::::::::::Daily Collection::::::::::::::
    public function loanListLate(){
        return view('report.dailyDebtor.report');
    }

    public function loanListLateList(Request $request){
        $data = DB::table('loan_routes as lr')
            ->join('loans as l', 'l.id', 'lr.loan_id')
            ->join('loan_days as ld', 'ld.loan_id', 'l.id')
            ->join('people as p', 'p.id', 'l.people_id')


            ->where('l.deleted_at', null)
            ->where('ld.deleted_at', null)
            ->where('lr.deleted_at', null)

            ->where('l.debt', '>', 0)

            ->where('ld.debt', '>', 0)
            ->where('ld.late', 1)

            ->select('p.first_name', 'p.last_name1', 'last_name2', 'p.ci', 'l.code', 'l.dateDelivered', 'p.cell_phone', 'p.street', 'p.home', 'p.zone',
                'l.day', 'l.amountTotal', 'l.amountLoan', 'l.amountPorcentage', 'l.date',
                DB::raw("SUM(ld.late) as diasAtrasado"), DB::raw("SUM(ld.debt) as montoAtrasado")
            )
            ->groupBy('l.id','p.id')
            ->get();
        if($request->print){
            $start = $request->start;
            $finish = $request->finish;
            return view('report.dailyDebtor.print', compact('data'));
        }else{
            return view('report.dailyDebtor.list', compact('data'));
        }
    }

    public function general_index(){
        $this->custom_authorize('browse_printgeneral');
        return view('report.general.browse');
    }



    public function general_list(Request $request){

        $date = $request->date;
        // dump($request);
        $loans_payments = LoanDayAgent::with(['loanDay.loan.loanRoute.route', 'loanDay.loan.people', 'agent'])
            ->where('deleted_at', null)
            ->whereDate('created_at', $date)
            
            ->get();

        

        // dump( $loans_payments->groupBy('oanDay.loan.loanRoute.route'));

        // dump( $loans_payments->select('agent_id', 'type', DB::raw('sum(amount) as amount'))->groupBy('agent_id'));

        // $loans_payments = LoanDayAgent::with(['loanDay.loan.current_loan_route.route', 'loanDay.loan.people'])
        //     ->where('deleted_at', null)
        //     // ->where()
        //     ->whereDate('created_at', $date)->get()->SUM('amount'); 
        
        // $loans_payments->groupBy('agent');
        // dump($loans_payments->groupBy('agent'), JSON_PRETTY_PRINT);       

        $pawn_register_payment = PawnRegisterPayment::where('date', $date)->get();

        $cashier_cash_out = CashierMovement::with(['cashierMovementCategory'])
                ->where('type', 'egreso')
                ->where('status', 'Aceptado')
                ->where('deleted_at', null)
                ->where('transferCashier_id', null)
                ->whereDate('created_at', $date)
                ->get();

        $loans = Loan::with(['people'])
            ->where('status', 'entregado')
            ->where('deleted_at', null)
            ->whereDate('dateDelivered', date('Y-m-d', strtotime($date)))
            ->get();

        $show_details = 0;

        $cashiers = Cashier::with(['movements' => function($q)use($date){
                $q->where('deleted_at', NULL)
                    ->where('type', 'ingreso')
                    ->where('status', 'Aceptado')
                    ->where('deleted_at', null)
                    ->whereDate('created_at', $date);
            }, 'user'])
            ->where('deleted_at', NULL)
            ->whereDate('created_at', $date)
            ->get();

            

        // Para prendario
        $pawns = PawnRegister::with(['person', 'details'])
            ->whereIn('status', ['entregado', 'recogida', 'expiro'])
            ->where('deleted_at', null)
            ->whereDate('dateDelivered', date('Y-m-d', strtotime($date)))
            ->get();


        $prendario = PawnRegisterMonthAgent::with(['transaction', 'agent', 'pawnRegister.person'])
            // ->where('agent_id', $request->agent_id)
            ->whereDate('created_at',  date('Y-m-d', strtotime($date)))
            // ->whereRaw($type)
            ->where('deleted_at', null)
            ->select('transaction_id', 'agent_id', 'agentType','deleted_at', DB::raw('sum(amount) as amount'), 'pawnRegister_id')
            ->groupBy('transaction_id')
            ->orderBy('transaction_id', 'DESC')
            ->get();


        // dump($cashiers);
        // return 1;





        if($request->print){
            return view('report.general.print', compact('loans_payments','cashiers', 'pawn_register_payment', 'cashier_cash_out', 'loans', 'show_details', 'date',
                                                        'pawns', 'prendario'));
        }else{
            return view('report.general.list', compact('loans_payments','cashiers', 'pawn_register_payment', 'cashier_cash_out', 'loans', 'show_details',
                                                        'pawns', 'prendario'));
        }
    }



    public function loanGestion()
    {
        $this->custom_authorize('browse_printloanGestion');
        return view('report.loanGestion.report');
    }

    public function loanGestionList(Request $request)
    {
        $start = $request->start;
        $finish = $request->finish;

        
        $datas = DB::table('loans as l')
            ->whereDate('l.dateDelivered', '>=', $request->start)
            ->whereDate('l.dateDelivered', '<=', $request->finish)
            ->where('l.deleted_at', null)
            ->where('l.status', 'entregado')

            ->select(DB::raw('DATE_FORMAT(l.dateDelivered, "%Y-%m") as yearMonthDate'), DB::raw('MONTH(l.dateDelivered) as monthDate'), DB::raw('YEAR(l.dateDelivered) as yearDate'),
                DB::raw("SUM(l.amountLoan) as amountLoan"), 

                DB::raw("SUM(l.amountPorcentage) as amountPorcentage"),
                DB::raw("SUM(l.amountLoan) + SUM(l.amountPorcentage) as capitalPorcentage"),
                DB::raw("(SELECT SUM(ld.amount - ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE
                    ls.dateDelivered >= '$start'
                    AND ls.dateDelivered <= '$finish'
                    AND MONTH(ls.dateDelivered) = monthDate
                    AND YEAR(ls.dateDelivered) = yearDate

                    AND ld.status = 1 
                    AND ld.deleted_at IS NULL 
                    
                    AND ls.status = 'entregado'
                    AND ls.deleted_at IS NULL) as pagado"),


                // DB::raw("(SELECT SUM(ld.debt) 
                //     FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                //     WHERE 
                //     ls.dateDelivered >= '$start'
                //     AND ls.dateDelivered <= '$finish'
                //     AND MONTH(ls.dateDelivered) = monthDate
                //     AND YEAR(ls.dateDelivered) = yearDate
                //     AND ld.status = 1 
                //     AND ld.deleted_at IS NULL 
                //     AND ld.debt != 0
                //     AND ls.status = 'entregado'
                //     AND ls.mora = 0
                //     AND ls.deleted_at IS NULL) as deuda"),
                DB::raw("IFNULL(
                    (SELECT SUM(ld.debt) 
                     FROM loan_days ld 
                     INNER JOIN loans ls ON ld.loan_id = ls.id 
                     WHERE 
                        ls.dateDelivered >= '$start'
                        AND ls.dateDelivered <= '$finish'
                        AND MONTH(ls.dateDelivered) = monthDate
                        AND YEAR(ls.dateDelivered) = yearDate
                        AND ld.status = 1 
                        AND ld.deleted_at IS NULL 
                        AND ld.debt != 0
                        AND ls.status = 'entregado'
                        AND ls.mora = 0
                        AND ls.deleted_at IS NULL
                    ), 0) as deuda"),

                DB::raw("(SELECT SUM(ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE
                    ls.dateDelivered >= '$start'
                    AND ls.dateDelivered <= '$finish'
                    AND MONTH(ls.dateDelivered) = monthDate
                    AND YEAR(ls.dateDelivered) = yearDate
                    AND ld.status = 1 
                    AND ld.deleted_at IS NULL 
                    AND ld.debt != 0
                    AND ls.status = 'entregado'
                    AND ls.mora = 1
                    AND ls.deleted_at IS NULL) as mora")
            )
            ->groupBy('monthDate', 'yearDate')
            ->orderBy('yearMonthDate', 'ASC')
            ->get();

        // dump(json_encode($datas, JSON_PRETTY_PRINT));


        if($request->print){
            return view('report.loanGestion.print', compact('start', 'finish','datas'));
        }else{
            return view('report.loanGestion.list', compact('datas'));
        }
    }

    // ::::::::::::::::::::::::::::::::::::::::::::

    // Ya no sirve ha sido mejorado
    public function loanRangeGestion()
    {
        $this->custom_authorize('browse_printloanRangeGestion');
        return view('report.loanRangeGestion.report');
    }

    public function loanRangeGestionList(Request $request)
    {
        $start = $request->start;
        $finish = $request->finish;
        // dump($request);


        $datas = DB::table('loans as l')
            ->where('l.dateDelivered', '>=', $start)
            ->where('l.dateDelivered', '<=', $finish)
            ->where('l.deleted_at', null)
            ->where('l.status', 'entregado')


            ->select(DB::raw('MONTH(l.dateDelivered) as monthDate'), DB::raw('YEAR(l.dateDelivered) as yearDate'), 
                DB::raw("SUM(l.amountLoan) as capital"),
                DB::raw("SUM(l.amountLoan) + SUM(l.amountPorcentage) as amountLoan"),

                DB::raw("(SELECT SUM(ld.amount - ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE 
                    ls.dateDelivered >= '$start'
                    AND ls.dateDelivered <= '$finish'
                    AND YEAR(ls.dateDelivered) = yearDate
                    AND MONTH(ls.dateDelivered) = monthDate

                    AND ld.status = 1 
                    AND ld.deleted_at IS NULL 
                
                    AND ls.status = 'entregado'
                    AND ls.deleted_at IS NULL) as pagado"),

                // DB::raw("(SELECT SUM(ld.debt) 
                //     FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                //     WHERE 
                //     ls.dateDelivered >= '$start'
                //     AND ls.dateDelivered <= '$finish'
                //     AND YEAR(ls.dateDelivered) = yearDate
                //     AND MONTH(ls.dateDelivered) = monthDate

                //     AND ld.status = 1 
                //     AND ld.deleted_at IS NULL 
                //     AND ld.debt != 0
                //     AND ls.status = 'entregado'
                //     AND ls.deleted_at IS NULL) as deuda"),


                DB::raw("IFNULL(
                    (SELECT SUM(ld.debt) 
                     FROM loan_days ld 
                     INNER JOIN loans ls ON ld.loan_id = ls.id 
                     WHERE 
                        ls.dateDelivered >= '$start'
                        AND ls.dateDelivered <= '$finish'
                        AND MONTH(ls.dateDelivered) = monthDate
                        AND YEAR(ls.dateDelivered) = yearDate
                        AND ld.status = 1 
                        AND ld.deleted_at IS NULL 
                        AND ld.debt != 0
                        AND ls.status = 'entregado'
                        AND ls.mora = 0
                        AND ls.deleted_at IS NULL
                    ), 0) as deuda"),

                DB::raw("(SELECT SUM(ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE
                    ls.dateDelivered >= '$start'
                    AND ls.dateDelivered <= '$finish'
                    AND MONTH(ls.dateDelivered) = monthDate
                    AND YEAR(ls.dateDelivered) = yearDate
                    AND ld.status = 1 
                    AND ld.deleted_at IS NULL 
                    AND ld.debt != 0
                    AND ls.status = 'entregado'
                    AND ls.mora = 1
                    AND ls.deleted_at IS NULL) as mora")
            )
            ->groupBy('yearDate', 'monthDate')
            ->get();

        if($request->print){
            return view('report.loanRangeGestion.print', compact('start', 'finish','datas'));
        }else{
            return view('report.loanRangeGestion.list', compact('start', 'finish', 'datas'));
        }
    }

    public function loanDetailGestion()
    {
        $this->custom_authorize('browse_printloanDetailGestion');
        return view('report.loanDetailGestion.report');
    }

    public function loanDetailGestionList(Request $request)
    {
        $start = $request->start;
        $finish = $request->finish;
        $datas = DB::table('loans as l')
            ->whereYear('l.dateDelivered', '>=', $start)
            ->whereYear('l.dateDelivered', '<=', $finish)
            ->where('l.deleted_at', null)
            ->where('l.status', 'entregado')

            ->select( DB::raw('YEAR(l.dateDelivered) as yearDate'), 
                DB::raw("SUM(l.amountLoan) as capital"),
                DB::raw("SUM(l.amountPorcentage) as interes"),
                DB::raw("SUM(l.amountLoan) + SUM(l.amountPorcentage) as amountLoan"),

            DB::raw("(SELECT SUM(ld.amount - ld.debt) 
                  FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                  WHERE YEAR(ls.dateDelivered) >= $start
                  AND YEAR(ls.dateDelivered) <= $finish
                  AND YEAR(ls.dateDelivered) = yearDate
                  AND ld.status = 1 
                  AND ld.deleted_at IS NULL 

                  AND ls.status = 'entregado'
                  AND ls.deleted_at IS NULL) as pagado"),

            // DB::raw("(SELECT SUM(ld.debt) 
            //         FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
            //         WHERE
            //             YEAR(ls.dateDelivered) >= $start
            //             AND YEAR(ls.dateDelivered) <= $finish
            //             AND YEAR(ls.dateDelivered) = yearDate
            //             AND ld.status = 1 
            //             AND ld.deleted_at IS NULL 
            //             AND ld.debt != 0
            //             AND ls.status = 'entregado'
            //             AND ls.deleted_at IS NULL
            //         ) as deuda"),
            DB::raw("IFNULL(
                    (SELECT SUM(ld.debt) 
                     FROM loan_days ld 
                     INNER JOIN loans ls ON ld.loan_id = ls.id 
                     WHERE 
                        YEAR(ls.dateDelivered) >= '$start'
                        AND YEAR(ls.dateDelivered) <= '$finish'
                        AND YEAR(ls.dateDelivered) = yearDate
                        AND ld.status = 1 
                        AND ld.deleted_at IS NULL 
                        AND ld.debt != 0
                        AND ls.status = 'entregado'
                        AND ls.mora = 0
                        AND ls.deleted_at IS NULL
                    ), 0) as deuda"),

            DB::raw("(SELECT SUM(ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE
                        YEAR(ls.dateDelivered) >= '$start'
                        AND YEAR(ls.dateDelivered) <= '$finish'
                        AND YEAR(ls.dateDelivered) = yearDate
                        AND ld.status = 1 
                        AND ld.deleted_at IS NULL 
                        AND ld.debt != 0
                        AND ls.status = 'entregado'
                        AND ls.mora = 1
                        AND ls.deleted_at IS NULL
                    ) as mora")
            )
            ->groupBy('yearDate')
            ->get();
        // dump($datas);

        if($request->print){
            return view('report.loanDetailGestion.print', compact('start', 'finish', 'datas'));
        }else{
            return view('report.loanDetailGestion.list', compact('start', 'finish', 'datas'));
        }
    }

    public function bonusCollection()
    {
        $this->custom_authorize('browse_printbonusCollection');
        // dump(Carbon::now());
        // dump(date('Y-m-d H:m:s'));
        return view('report.bonusCollection.report');
    }

    public function bonusCollectionList(Request $request)
    {
        $date = date('Y-m-d');
        $start = $request->start;
        $finish = $request->finish;

        // $datas = HistoryReportDailyList::with(['historyDetail' => function($q) {
        //     $q->select(
        //         'historyReport_id',
        //         DB::raw('SUM(dailyPayment) as totalDailyPayment')
        //     )->groupBy('historyReport_id');
        // }, 'route', 'agent'])
        // ->whereDate('created_at', '>=', $start)
        // ->whereDate('created_at', '<=', $finish)
        // ->where('agent_id', '!=', null)
        // ->where('type', 'inicio')
        // ->select('id', 'route_id', 'agent_id', 'created_at')
        // ->get();



        $datas = HistoryReportDailyList::with(['route', 'agent'])
        ->join('history_report_daily_list_details', 'history_report_daily_list_details.historyReport_id', '=', 'history_report_daily_lists.id')
        ->select(
            // DB::raw("CONCAT('RUTA ', history_report_daily_lists.route_id, ' - ', agents.name) as recaudo")
            DB::raw('SUM(history_report_daily_list_details.dailyPayment) as totalDailyPayment'), 
            'history_report_daily_lists.route_id', 'history_report_daily_lists.agent_id', 'history_report_daily_lists.created_at'
        )
        ->whereDate('history_report_daily_lists.created_at', '>=', $start)
        ->whereDate('history_report_daily_lists.created_at', '<=', $finish)
        ->where('history_report_daily_lists.agent_id', '!=', null)
        ->where('history_report_daily_lists.type', 'inicio')
        ->groupBy('history_report_daily_lists.route_id', 'history_report_daily_lists.agent_id')
        ->get();



        $cStart = date('Y-m-d', strtotime($start.' + 1 days'));
        $cFinish = date('Y-m-d', strtotime($finish.' + 1 days'));

        $ok=true;
        while ($ok) {
            $cashier = Cashier::where('deleted_at', null)->whereDate('created_at', $cStart)->first();
            if ($cashier || $cStart == date('Y-m-d')) {
                $ok=false;
            }
            else
            {
                // $ok=false;
                $cStart = date('Y-m-d', strtotime($cStart.' + 1 days'));
            }
        }
        $ok=true;
        while ($ok) {
            $cashier = Cashier::where('deleted_at', null)->whereDate('created_at', $cFinish)->first();
            if ($cashier || $cFinish == date('Y-m-d')) {
                $ok=false;
            }
            else
            {
                $cFinish = date('Y-m-d', strtotime($cFinish.' + 1 days'));
            }
        }
        $cashiers = Cashier::join('cashier_movements', 'cashier_movements.cashier_id', '=', 'cashiers.id')
        ->join('users', 'users.id', '=', 'cashiers.user_id')
        ->whereDate('cashiers.created_at', '>=', $cStart)
        ->whereDate('cashiers.created_at', '<=', $cFinish)
        ->where('cashiers.deleted_at', null)
        ->where('cashier_movements.type', 'ingreso')
        ->select(
            DB::raw('SUM(cashier_movements.amount) as amount'), 
            'users.name', 'cashiers.user_id'
        )
        ->groupBy('cashiers.user_id')

        ->get();


        if($request->print){
            return view('report.bonusCollection.print', compact('start', 'finish', 'datas', 'cashiers'));
        }else{
            return view('report.bonusCollection.list', compact('start', 'finish', 'datas', 'cashiers'));
        }
    }


    public function loanRecovery()
    {
        $this->custom_authorize('browse_printloanRecovery');
        $user = User::where('status', 1)->get();
        return view('report.loanRecovery.report', compact('user'));
    }

    public function loanRecoveryList(Request $request)
    {

        $start = $request->start;
        $finish = $request->finish;

        $type = 1;
        if($request->type=='Efectivo')
        {
            $type = "t.type='Efectivo'";
        }
        if($request->type=='Qr')
        {
            $type = "t.type='Qr'";
        }

        $data = DB::table('loan_day_agents as lda')
                        ->join('loan_days as ld', 'ld.id', 'lda.loanDay_id')
                        ->join('loans as l', 'l.id', 'ld.loan_id')
                        ->join('people as p', 'p.id', 'l.people_id')
                        ->join('users as u', 'u.id', 'lda.agent_id')
                        ->join('transactions as t', 't.id', 'lda.transaction_id')

                        ->where('lda.deleted_at', null)
                        ->where('lda.recovery', 'si')
                        ->whereDate('t.created_at', '>=', date('Y-m-d', strtotime($start)))
                        ->whereDate('t.created_at', '<=', date('Y-m-d', strtotime($finish)))

                        ->where('lda.agent_id', $request->agent_id)
                        ->whereRaw($type)
                        // ->whereRaw($query_filter)
                        ->select('p.first_name', 'u.name', 'p.last_name1', 'last_name2', 'p.ci', 'ld.date as dateDay', 'u.name',
                                'l.id as loan', 'l.code', 'l.amountTotal', 'lda.id as loanDayAgent_id', DB::raw('SUM(lda.amount)as amount'),
                                't.created_at as loanDayAgent_fecha', 't.transaction', 't.type')
                        ->groupBy('loan', 'transaction')
                        ->orderBy('loanDayAgent_fecha', 'ASC')
                        ->get();
        $agent = User::where('id', $request->agent_id)->first()->name;
        $amount = $data->SUM('amount');

        
        
        // dump($data);

        if($request->print){
            return view('report.loanRecovery.print', compact('start', 'finish', 'data', 'agent', 'amount'));
        }else{
            return view('report.loanRecovery.list', compact('start', 'finish', 'data'));
        }

        // if($request->print){
        //     $date = $request->date;
        //     return view('report.cashier.dailyCollection.print', compact('data', 'date', 'agent', 'amount', 'ci'));
        // }else{
        //     return view('report.cashier.dailyCollection.list', compact('data', 'cashier'));
        // }
    }
}
