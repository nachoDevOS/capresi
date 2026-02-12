<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\People;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\LoanDay;
use App\Models\User;

class ReportManagerController extends Controller
{

    //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$               PARA LA RECOLECCION DIARIA POR RANGO DE FECHA                   $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
    public function dailyCollection()
    {
        // return 1;
        $user = User::where('role_id', '!=', 1)->orderBy('name', 'ASC')->get();
        return view('report.manager.dailyCollection.report', compact('user'));
    }

    // VIEW LIST
    public function dailyCollectionList(Request $request)
    {
        // return $request;
        // dump($request);
        $show_details = $request->show_details;
        $query_filter = 'lda.agent_id = '. $request->agent_id;

        $type = 1;
        if($request->type=='Efectivo')
        {
            $type = "t.type='Efectivo'";
        }
        if($request->type=='Qr')
        {
            $type = "t.type='Qr'";
        }

        if ($request->agent_id=='todo') {
            $query_filter = 1;
        }


        // $article = Article::whereRaw($query_filter)->get();
        if($show_details==1)
        {
            $data = DB::table('loan_day_agents as lda')
                        ->join('loan_days as ld', 'ld.id', 'lda.loanDay_id')
                        ->join('loans as l', 'l.id', 'ld.loan_id')
                        ->join('people as p', 'p.id', 'l.people_id')
                        ->join('users as u', 'u.id', 'lda.agent_id')
                        ->join('transactions as t', 't.id', 'lda.transaction_id')

                        // ->where('l.deleted_at', null)
                        // ->where('ld.deleted_at', null)
                        ->where('lda.deleted_at', null)
                        ->whereDate('t.created_at', '>=', date('Y-m-d', strtotime($request->start)))
                        ->whereDate('t.created_at', '<=', date('Y-m-d', strtotime($request->finish)))
                        // ->where('lda.agent_id', $request->agent_id)
                        ->whereRaw($query_filter)
                        ->whereRaw($type)
                        ->select('l.deleted_at','p.first_name', 'p.last_name1', 'last_name2', 'p.ci', 'ld.date as dateDay', 'u.name', 'l.id as loan', 'l.code', 'l.amountTotal', 'lda.id as loanDayAgent_id',
                                    DB::raw('SUM(lda.amount)as amount'), 't.transaction',
                                't.created_at as loanDayAgent_fecha', 't.type')
                        ->groupBy('loan', 'transaction')
                        ->orderBy('loanDayAgent_fecha', 'ASC')
                        ->get();
        }
        else
        {

            $data = DB::table('loan_day_agents as lda')
                    ->join('loan_days as ld', 'ld.id', 'lda.loanDay_id')
                    ->join('loans as l', 'l.id', 'ld.loan_id')
                    ->join('people as p', 'p.id', 'l.people_id')
                    ->join('users as u', 'u.id', 'lda.agent_id')
                    ->join('transactions as t', 't.id', 'lda.transaction_id')

                    // ->where('l.deleted_at', null)
                    // ->where('ld.deleted_at', null)
                    ->where('lda.deleted_at', null)
                    ->whereDate('t.created_at', '>=', date('Y-m-d', strtotime($request->start)))
                    ->whereDate('t.created_at', '<=', date('Y-m-d', strtotime($request->finish)))
                    // ->where('lda.agent_id', $request->agent_id)
                    ->whereRaw($query_filter)
                    ->whereRaw($type)
                    ->select('u.name', 'u.id as user_id',
                                DB::raw('SUM(lda.amount)as amount'),
                            DB::raw('DATE(t.created_at) as loanDayAgent_fecha'))

                    ->groupBy('user_id', 'loanDayAgent_fecha')

                    ->orderBy('name', 'ASC')
                    ->get();
        }


        // return $data->id;    
        $amountTotal = $data->SUM('amount');
        // dump($data);
        if($request->print){
            $start = $request->start;
            $finish = $request->finish;
            return view('report.manager.dailyCollection.print', compact('data', 'show_details', 'type', 'start', 'finish', 'amountTotal'));
        }else{
            return view('report.manager.dailyCollection.list', compact('data', 'show_details'));
        }
    }


    //$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$               PARA TODOS LOS PRESTAMOS TOTAL SUMADOS                   $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
    public function loanAll()
    {
        return view('report.manager.loanAll.report');
    }
    public function loanAllList(Request $request)
    {
        // dump($request);
        $query_filter = 1;;
        if ($request->status == 'enpago') {
            $query_filter = 'debt > 0 ';
        }
        if ($request->status == 'pagado') {
            $query_filter = 'debt = 0 ';
        }

        $data = Loan::with(['people', 'agentDelivered', 'loanDay'])
                    ->where('deleted_at', null)
                    ->where('status', 'entregado')
                    ->whereDate('dateDelivered', '>=', date('Y-m-d', strtotime($request->start)))
                    ->whereDate('dateDelivered', '<=', date('Y-m-d', strtotime($request->finish)))
                    ->whereRaw($query_filter)
                    ->whereRaw($request->type ? 'porcentage = 0' : 1)
                    ->orderBY('dateDelivered', 'ASC')->get();

        $ok = $request->status;
        if($request->print){
            $start = $request->start;
            $finish = $request->finish;
            return view('report.manager.loanAll.print', compact('data', 'start', 'finish', 'ok'));
        }else{
            return view('report.manager.loanAll.list', compact('data'));
        }
    }
    
}
