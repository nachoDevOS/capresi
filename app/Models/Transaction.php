<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    
    protected $fillable = ['transaction', 'type', 'category', 'deleted_at', 'urlRegister',
        'latitude',
        'longitude',
        'DescriptionPrecision'
    ];



    public function payments() {
        return $this->hasMany(LoanDayAgent::class, 'transaction_id');
    }

    public function pawAgent() {
        return $this->hasMany(PawnRegisterMonthAgent::class, 'transaction_id');
    }
}
