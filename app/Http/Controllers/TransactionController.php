<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

use App\Models\Transaction;

class TransactionController extends Controller
{
    public function listTransaction($loan)
    {

        $data = DB::table('loans as l')
            ->join('loan_days as ld', 'ld.loan_id', 'l.id')
            ->join('loan_day_agents as lda', 'lda.loanDay_id', 'ld.id')
            ->join('transactions as t', 't.id', 'lda.transaction_id')
            ->join('users as u', 'u.id', 'lda.agent_id')
            ->join('people as p', 'p.id', 'l.people_id')
            ->leftJoin('users as ud', 'ud.id', 'lda.deleted_userId')
            ->where('l.id', $loan)
            ->where('l.deleted_at', null)
            ->select( 't.id as id','l.id as loan', DB::raw('SUM(lda.amount)as amount'), 'u.name', 'lda.agentType', 'p.id as people', 'p.cell_phone as people_phone', 'lda.transaction_id', 'ud.name as eliminado', 't.id as transaction', 't.DescriptionPrecision','t.urlRegister', 't.latitude', 't.longitude', 't.deleted_at', 't.created_at')
            ->groupBy('loan', 't.id')
            ->orderBy('id', 'DESC')
            ->get();

            // return $data;

        return view('transaction.browse', compact('data'));
    }

    public function payment_notification($id){
        $transaction = Transaction::with(['payments.agent', 'payments.loanDay.loan.people'])->where('id', $id)->first();
        
        return view('loans.print.notification', compact('transaction'));
    }
}
