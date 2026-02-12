<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportLoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ######################       Prestamos Actuales vigentes       ######################
    public function currentLoans()
    {        
        return view('reports.loans.currentLoans.browse');
    }

    public function currentLoansList(Request $request)
    {
        $date = Carbon::now()->format('Y-m-d');
        $type = $request->type;

        $loans = Loan::with(['people', 'loanDay', 'current_loan_route.route'])
            ->where('status', 'entregado')
            ->where('deleted_at', null)
            ->where('debt', '>', 0)
            ->whereHas('loanDay', function($q) use ($date){
                $q->where('date', '>=', $date)->where('deleted_at', null);
            })
            ->get();
        
        if($type == 'todo'){
            $loans = $loans->groupBy(function($item) {
                return 'Todo';
            });
        } else {
            $type = 'grouped';
            $loans = $loans->groupBy(function($item) {
                return Carbon::parse($item->dateDelivered)->format('d/m/Y');
            })->sortBy(function ($group, $key) {
                return Carbon::createFromFormat('d/m/Y', $key)->timestamp;
            });
        }

        if($request->print){
            return view('reports.loans.currentLoans.print', compact('loans', 'type'));
        }else{
            return view('reports.loans.currentLoans.list', compact('loans', 'type'));
        }
    }

    // ######################       Prestamos x Gestion       ######################
    public function loanGestions()
    {
        // $this->custom_authorize('browse_printloanGestion');
        return view('reports.loans.loanGestions.browse');
    }

    public function loanGestionsList(Request $request)
    {
        $start = $request->start;
        $finish = $request->finish;
        $date = date('Y-m-d');
        $groupBy = $request->group_by;

        $selectDate = "DATE_FORMAT(l.dateDelivered, '%Y-%m') as yearMonthDate, MONTH(l.dateDelivered) as monthDate, YEAR(l.dateDelivered) as yearDate";
        $subQueryCondition = "AND MONTH(ls.dateDelivered) = monthDate AND YEAR(ls.dateDelivered) = yearDate";

        if($groupBy == 'year'){
            $selectDate = "YEAR(l.dateDelivered) as yearDate, 0 as monthDate";
            $subQueryCondition = "AND YEAR(ls.dateDelivered) = yearDate";
        }
        
        $query = DB::table('loans as l')
            ->whereDate('l.dateDelivered', '>=', $request->start)
            ->whereDate('l.dateDelivered', '<=', $request->finish)
            ->where('l.deleted_at', null)
            ->where('l.status', 'entregado')

            ->select(DB::raw($selectDate),
                DB::raw("SUM(l.amountLoan) as amountLoan"), 

                DB::raw("SUM(l.amountPorcentage) as amountPorcentage"),
                DB::raw("SUM(l.amountLoan) + SUM(l.amountPorcentage) as capitalPorcentage"),
                DB::raw("(SELECT SUM(ld.amount - ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE
                    ls.dateDelivered >= '$start'
                    AND ls.dateDelivered <= '$finish'
                    $subQueryCondition

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
                        $subQueryCondition
                        AND ld.status = 1 
                        AND ld.deleted_at IS NULL 
                        AND ld.debt != 0
                        AND ls.status = 'entregado'
                        AND ls.deleted_at IS NULL
                        AND (ls.mora = 0 OR (SELECT MAX(date) FROM loan_days WHERE loan_id = ls.id AND deleted_at IS NULL) >= '$date')
                    ), 0) as deuda"),

                DB::raw("(SELECT SUM(ld.debt) 
                    FROM loan_days ld inner join loans ls on ld.loan_id = ls.id 
                    WHERE
                    ls.dateDelivered >= '$start'
                    AND ls.dateDelivered <= '$finish'
                    $subQueryCondition
                    AND ld.status = 1 
                    AND ld.deleted_at IS NULL 
                    AND ld.debt != 0
                    AND ls.status = 'entregado'
                    AND ls.deleted_at IS NULL
                    AND (ls.mora = 1 AND (SELECT MAX(date) FROM loan_days WHERE loan_id = ls.id AND deleted_at IS NULL) < '$date')
                    ) as mora") );

        if($groupBy == 'year'){
            $query->groupBy('yearDate')->orderBy('yearDate', 'ASC');
        } else {
            $query->groupBy('monthDate', 'yearDate')->orderBy('yearMonthDate', 'ASC');
        }

        $datas = $query->get();

        // dump(json_encode($datas, JSON_PRETTY_PRINT));


        if($request->print){
            return view('reports.loans.loanGestions.print', compact('start', 'finish','datas'));
        }else{
            return view('reports.loans.loanGestions.list', compact('datas'));
        }
    }


    // ######################       Prestamos x Rango de Gestion       ######################
    public function loanRangeGestions()
    {
        // $this->custom_authorize('browse_printloanRangeGestion');
        return view('reports.loans.loanRangeGestions.browse');
    }

    public function loanRangeGestionsList(Request $request)
    {
        $start = $request->start;
        $finish = $request->finish;
        $date = date('Y-m-d');
        // dump($request);


        $datas = DB::table('loans as l')
            ->where('l.dateDelivered', '>=', $start)
            ->where('l.dateDelivered', '<=', $finish)
            ->where('l.deleted_at', null)
            ->where('l.status', 'entregado')


            ->select(DB::raw('MONTH(l.dateDelivered) as monthDate'), DB::raw('YEAR(l.dateDelivered) as yearDate'), 
                DB::raw("SUM(l.amountLoan) as capital"),
                DB::raw("SUM(l.amountPorcentage) as interest"),
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
                        AND ls.deleted_at IS NULL
                        AND (ls.mora = 0 OR (SELECT MAX(date) FROM loan_days WHERE loan_id = ls.id AND deleted_at IS NULL) >= '$date')
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
                    AND ls.deleted_at IS NULL
                    AND (ls.mora = 1 AND (SELECT MAX(date) FROM loan_days WHERE loan_id = ls.id AND deleted_at IS NULL) < '$date')
                    ) as mora")
            )
            ->groupBy('yearDate', 'monthDate')
            ->get();

        if($request->print){
            return view('report.loanRangeGestions.print', compact('start', 'finish','datas'));
        }else{
            return view('report.loanRangeGestions.list', compact('start', 'finish', 'datas'));
        }
    }



}
