<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\People;
use App\Models\Loan;
use App\Models\LoanDay;
use App\Models\RouteCollector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Cashier;
use App\Models\CashierMovement;
use App\Models\PawnRegister;
use App\Models\PawnRegisterMonthAgent;
use App\Models\PaymentsPeriod;

class ReportCashierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    //:::::::::::: PARA RECAUDACION DIARIA DE LOS CAJEROS Y COBRADORES EN MOTOS::::::::    
    public function loanCollection()
    {        
        $route = Route::where('status', 1)->where('deleted_at', null)->get();
        $query_filter = 'id='.Auth::user()->id;
        if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador')){
            $query_filter=1;
        }
        $user = User::whereRaw($query_filter)->where('status', 1)->get();
        // return 1;
        return view('report.cashier.dailyCollection.report', compact('route', 'user'));
    }

    public function loanCollectionList(Request $request){

        $prestamos = $request->prestamos;
        $date = $request->date;

        // dump($prestamos);
        $type = 1;
        if($request->type=='Efectivo')
        {
            $type = "t.type='Efectivo'";
        }
        if($request->type=='Qr')
        {
            $type = "t.type='Qr'";
        }
        $cashier = Cashier::where('user_id', Auth::user()->id)
            ->where('status', '!=', 'cerrada')
            ->where('deleted_at', NULL)->count();

        $agent = User::where('id', $request->agent_id)->first()->name;
        $ci = User::where('id', $request->agent_id)->first()->ci;

        $diario = DB::table('loan_day_agents as lda')
                        ->join('loan_days as ld', 'ld.id', 'lda.loanDay_id')
                        ->join('loans as l', 'l.id', 'ld.loan_id')
                        ->join('people as p', 'p.id', 'l.people_id')
                        ->join('users as u', 'u.id', 'lda.agent_id')
                        ->join('transactions as t', 't.id', 'lda.transaction_id')

                        ->where('lda.deleted_at', null)
                        ->whereDate('t.created_at', date('Y-m-d', strtotime($request->date)))
                        ->where('lda.agent_id', $request->agent_id)
                        ->whereRaw($type)
                        // ->whereRaw($query_filter)
                        ->select('p.first_name', 'u.name', 'p.last_name1', 'last_name2', 'p.ci', 'ld.date as dateDay', 'u.name',
                                'l.id as loan', 'l.code', 'l.amountTotal', 'lda.id as loanDayAgent_id', DB::raw('SUM(lda.amount)as amount'),
                                't.created_at as loanDayAgent_fecha', 't.transaction', 't.type')
                        ->groupBy('loan', 'transaction')
                        ->orderBy('loanDayAgent_fecha', 'ASC')
                        ->get();
        $amountDiario = $diario->SUM('amount');

        $prendario = PawnRegisterMonthAgent::with(['transaction', 'agent', 'pawnRegister.person'])
            ->where('agent_id', $request->agent_id)
            ->whereDate('created_at', date('Y-m-d', strtotime($request->date)))
            // ->whereRaw($type)
            ->where('deleted_at', null)
            ->select('transaction_id', 'agent_id', 'agentType','deleted_at', DB::raw('sum(amount) as amount'), 'pawnRegister_id')
            ->groupBy('transaction_id')
            ->orderBy('transaction_id', 'DESC')
            ->get();

        $amountPrendario = $prendario->SUM('amount');
        if($request->print){
            $date = $request->date;
            return view('report.cashier.dailyCollection.print', compact('diario', 'prestamos', 'prendario', 'date', 'agent', 'amountPrendario', 'amountDiario', 'ci'));
        }else{
            return view('report.cashier.dailyCollection.list', compact('diario', 'prestamos', 'prendario', 'cashier'));
        }

    
        

        // if($request->prestamos == 'diario')
        // {
        
        //     if($request->print){
        //         $date = $request->date;
        //         return view('report.cashier.dailyCollection.print', compact('data', 'date', 'agent', 'amount', 'ci'));
        //     }else{
        //         return view('report.cashier.dailyCollection.list', compact('data', 'cashier'));
        //     }
        // }
        // else
        // {
            

        //     if($request->print){
        //         $date = $request->date;
        //         return view('report.cashier.dailyCollection.printPawn', compact('data', 'date', 'agent', 'amount', 'ci'));
        //     }else{
        //         return view('report.cashier.dailyCollection.listPawn', compact('data', 'cashier'));
        //     }
        // }
        
    }


    // para obtener los prestamos entregados del dia o una fecha en especifica
    public function loanDelivered()
    {   
        // $user = User::where('id', Auth::user()->id)->get();
        $query_filter = 'id='.Auth::user()->id;
        if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('administrador')){
            $query_filter=1;
        }
        $user = User::whereRaw($query_filter)->get();
        
        return view('report.cashier.loanDelivered.report', compact('user'));
    }


    public function loanDeliveredList(Request $request)
    {
        $prestamos = $request->prestamos;
        $date = $request->date;

        $agent = User::where('id', $request->agent_id)->first()->name;
        $ci = User::where('id', $request->agent_id)->first()->ci;
        $cashier = Cashier::where('user_id', Auth::user()->id)
                    ->where('status', '!=', 'cerrada')
                    ->where('deleted_at', NULL)->count();

        $dataDiario = Loan::with(['people', 'agentDelivered'])->where('status', 'entregado')
            ->where('delivered_userId', $request->agent_id)
            ->whereDate('dateDelivered', date('Y-m-d', strtotime($request->date)))
            ->get();
        $amountDiario = $dataDiario->SUM('amountLoan');


        


        $dataPrendario = PawnRegister::with(['person', 'details', 'agentDelivered'])
                ->whereDate('dateDelivered', date('Y-m-d', strtotime($request->date)))
                ->where('deleted_at', null)
                ->whereIn('status', ['entregado', 'recogida', 'expiro'])
                ->where('delivered_userId', $request->agent_id)
                ->get();
        $amountPrendario = 0;
        foreach ($dataPrendario as $item) {
            foreach ($item->details as $detail)
            {
                $amountPrendario += $detail->quantity * $detail->price;
            }
        }


        if($request->print){
            return view('report.cashier.loanDelivered.print', compact('dataDiario', 'amountDiario', 'dataPrendario', 'amountPrendario', 'prestamos', 'date', 'agent'));
        }
        else
        {
            return view('report.cashier.loanDelivered.list', compact('dataDiario', 'amountDiario', 'dataPrendario', 'amountPrendario', 'prestamos', 'cashier'));
        }  
            // // dump($data);
            // if($request->print){
            //     $date = $request->date;
            //     return view('report.cashier.loanDelivered.printPawn', compact('data', 'date', 'amount', 'agent'));
            // }else{
            //     return view('report.cashier.loanDelivered.listPawn', compact('data', 'cashier'));
            // }  


        // if($request->prestamos == 'diario')
        // {
        //     $data = Loan::with(['people', 'agentDelivered'])->where('status', 'entregado')
        //             ->where('delivered_userId', $request->agent_id)
        //             ->whereDate('dateDelivered', date('Y-m-d', strtotime($request->date)))
        //             ->get();

        //     $amount = $data->SUM('amountLoan');
        //     if($request->print){
        //         $date = $request->date;
        //         return view('report.cashier.loanDelivered.print', compact('data', 'date', 'amount', 'agent'));
        //     }else{
        //         return view('report.cashier.loanDelivered.list', compact('data', 'cashier'));
        //     }   
        // }
        // else
        // {
        //     $data = PawnRegister::with(['person', 'details', 'agentDelivered'])
        //         ->whereDate('dateDelivered', date('Y-m-d', strtotime($request->date)))
        //         ->where('deleted_at', null)
        //         ->where('delivered_userId', $request->agent_id)
        //         ->get();
        //     $amount = 0;
        //     foreach ($data as $item) {
        //         foreach ($item->details as $detail)
        //         {
        //             $amount += $detail->quantity * $detail->price;
        //         }
        //     }
        //     // dump($data);
        //     if($request->print){
        //         $date = $request->date;
        //         return view('report.cashier.loanDelivered.printPawn', compact('data', 'date', 'amount', 'agent'));
        //     }else{
        //         return view('report.cashier.loanDelivered.listPawn', compact('data', 'cashier'));
        //     }   
        // }             
    }


    // PARA GENERAR LAS LISTA DE COBROS POR RUTAS DE ACUERDO A LOS COBRADORES AGENTES

    public function dailyList()
    {
        $route = Route::where('status', 1)->where('deleted_at', null)->get();

        if(Auth::user()->hasRole('cobrador') || Auth::user()->hasRole('cajeros'))
        {
            $aux = RouteCollector::where('status',1)->where('deleted_at', null)->where('user_id', Auth::user()->id)->first();
            
            $route = Route::where('status', 1)->where('id', $aux?$aux->route_id:0)->where('deleted_at', null)->get();
        }

        return view('report.cashier.dailyListCobro.report', compact('route'));
    }
    public function dailyListList(Request $request)
    {
        if($request->route_id  == 'todo'){
            $query_filter = 1;
            $message = 'Todas Las Rutas';
        }else{
            $query_filter = 'lr.route_id = '.$request->route_id;
            $message = Route::where('id', $request->route_id)->where('deleted_at', null)->select('name')->first()->name;
        }

        $data = DB::table('loan_routes as lr')
                    ->join('loans as l', 'l.id', 'lr.loan_id')
                    ->join('people as p', 'p.id', 'l.people_id')
                    ->join('routes as r', 'r.id', 'lr.route_id')
                    ->leftJoin('payments_periods as pp', 'pp.id', 'l.payments_period_id')
                    ->where('l.deleted_at', null)
                    ->where('lr.deleted_at', null)
                    ->where('l.debt', '!=', 0)
                    ->where('l.status', 'entregado')
                    ->where('r.status', 1)
                    ->where('r.deleted_at', null)
                    ->whereRaw($query_filter)
                    ->select('p.first_name', 'p.last_name1', 'last_name2', 'p.ci', 'l.code', 'l.dateDelivered', 'p.cell_phone', 'p.phone', 'p.street', 'p.home', 'p.zone',
                        'l.day', 'l.amountTotal', 'l.amountLoan', 'l.amountPorcentage', 'l.date', 'l.id as loan_id', 'l.payments_period_id', 'r.name as ruta', 'pp.color as bg', 'pp.name as payments_period_name', 'pp.days_quantity as payments_period_day'
                    )
                    ->orderBy('l.dateDelivered', 'ASC')
                    ->get();
        $date = date('Y-m-d');

        if($request->print){
            return view('report.cashier.dailyListCobro.print', compact('data', 'message', 'date'));
        }else{
            return view('report.cashier.dailyListCobro.list', compact('data', 'date', 'message'));
        }
    }

    public function registers_index(){

        $user = User::where('role_id', '!=', 1)->orderBy('name', 'ASC')->get();
        return view('report.cashier.registers.report', compact('user'));
    }

    public function registers_list(Request $request){
        
            $show_details = $request->show_details;

            $start = $request->start;
            $finish = $request->finish;
            $user_id = $request->user_id;
            $type = $request->type;
            $cashier_movement_category = $request->cashier_movement_category_id;

            if($show_details==1)
            {
                $movements = CashierMovement::with(['cashier'=>function($query)use($user_id){
                                                    $query->where('user_id', $user_id);
                                                }, 'cashierMovementCategory'])
                                                ->whereDate('created_at', '>=', $start)
                                                ->whereDate('created_at', '<=', $finish)
                                                ->where('deleted_at', null)
                                                // ->where('user_id', $request->user_id)
                                                ->where('type', 'egreso')
                                                // ->where('type', $request->type)
                                                ->when($cashier_movement_category, function($query) use($cashier_movement_category){
                                                    $query->whereIn('cashier_movement_category_id', $cashier_movement_category);
                                                })
                                                ->whereHas('cashier', function($query)use($user_id){
                                                    $query->where('user_id', $user_id);
                                                })
                                                ->get();
            }
            else
            {
                $movements = CashierMovement::with(['cashier'=>function($query)use($user_id){
                        $query->where('user_id', $user_id);
                    }, 'cashierMovementCategory'=>function($q){
                        $q->select('id', 'name');
                    }])
                    ->whereDate('created_at', '>=', $start)
                    ->whereDate('created_at', '<=', $finish)
                    ->where('deleted_at', null)
                    // ->where('user_id', $request->user_id)
                    ->where('type', 'egreso')
                    // ->where('type', $request->type)
                    ->when($cashier_movement_category, function($query) use($cashier_movement_category){
                        $query->whereIn('cashier_movement_category_id', $cashier_movement_category);
                    })
                    ->whereHas('cashier', function($query)use($user_id){
                        $query->where('user_id', $user_id);
                    })
                    ->select('cashier_movement_category_id', DB::raw('DATE(created_at) as created_at'), DB::raw("SUM(amount) as amount"))
                    ->groupBy('cashier_movement_category_id')
                    ->get();
            }

            // dump($movements);

            // return 1;
            if($request->print){
                $user = User::find($user_id);
                return view('report.cashier.registers.print', compact('movements', 'start', 'finish', 'type', 'user', 'show_details'));
            }else{
                return view('report.cashier.registers.list', compact('movements', 'show_details'));
            }
        
    }
}
