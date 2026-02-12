<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawnRegisterAmountAditional extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id',
        'pawnRegister_id',
        'amountTotal',
        'dollarTotal',
        'dollarPrice',
        'description',

        'registerUser_id',
        'registerRole',

        'deleted_at',
        'deletedUser_id',
        'deletedRole',
        'deletedObservation'
    ];

    public function cashier(){
        return $this->belongsTo(Cashier::class, 'cashier_id');
    }

    public function pawnRegister(){
        return $this->belongsTo(PawnRegister::class, 'pawnRegister_id');
    }

    public function register(){
        return $this->belongsTo(User::class, 'registerUser_id');
    }
}
