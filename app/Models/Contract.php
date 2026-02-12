<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory;
    
    // protected $dates = ['deleted_at'];

    protected $fillable = ['people_id', 
                            'type', 'work',
                            'salary', 'advancement',
                            'totalSalary', 'dateStart',
                            'dateFinish', 'observation',
                            'status', 'deleted_at',

                            'paid',


                            'register_userId',
                            'register_agentType',

                            'rejected_userId',
                            'rejected_agentType',
                            'rejectedObservation',

                            'deleted_userId',
                            'deleted_agentType',
                            'deletedObservation',

                            'success_userId',
                            'success_agentType'
                        ];



    public function people(){
        return $this->belongsTo(People::class);
    }

    public function contractAdvancement()
    {
        return $this->hasMany(ContractAdvancement::class);
    }
    public function contractDay()
    {
        return $this->hasMany(ContractDay::class);
    }

    // public function contractDays()
    // {
    //     return $this->hasMany(ContractDay::class,'contract_id');
    // }

    public function spreadsheetContract()
    {
        return $this->hasMany(SpreadsheetContract::class,);
    }
    

}
