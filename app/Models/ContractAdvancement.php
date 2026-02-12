<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAdvancement extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'cashier_id',
        'cashierMovement_id',
        'advancement',

        'spreadsheet',
        'periodMonth',
        'periodYear',

        'dateAdvancement',
        'observation',

        'register_userId',
        'register_agentType',
        'deleted_userId',
        'deleted_agentType',
        'deletedObservation',
        'deleted_at'
    ];

    public function contract(){
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function registerUser()
    {
        return $this->belongsTo(User::class, 'register_userId');

    }



}
