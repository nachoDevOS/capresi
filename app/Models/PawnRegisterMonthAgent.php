<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawnRegisterMonthAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'pawnRegisterMonth_id',
        'pawnRegister_id',
        'transaction_id',
        'cashier_id',
        'type',
        'amount',
        'agent_id',
        'agentType',
        'status',
        'dollarTotal',
        'dollarPrice',

        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation'
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function pawnRegister()
    {
        return $this->belongsTo(PawnRegister::class, 'pawnRegister_id');
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }



 
}
