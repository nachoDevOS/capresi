<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
    
        'debt',
        'amount',
        'number',
        'date',

        'late',
        'lateN',
        'payment_day',

        'status',

        'deleted_at',
        'deleted_userId',
        'deleted_agentType',
        'deletedKey'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function loanDayAgents()
    {
        return $this->hasMany(LoanDayAgent::class, 'loanDay_id');
    }
}
