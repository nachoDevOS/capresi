<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPurchaseMonthAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'salaryPurchaseMonth_id',
        'salaryPurchase_id',
        'transaction_id',
        'cashier_id',
        'type',
        'amount',
        'dollarTotal',
        'dollarPrice',
        'agent_id',
        'agentType',
        'status',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation'
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function salaryPurchase()
    {
        return $this->belongsTo(SalaryPurchase::class, 'salaryPurchase_id');
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
