<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawnRegister extends Model
{
    use HasFactory;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'person_id',
        'code',
        'codeManual',
        'date',
        'date_limit',
        'interest_rate',
        'observations',
        'status',
        'cantMonth',
        'endeavor',

        'cashier_id',
        'delivered_userId',
        'delivered_userType',
        'dateDelivered',
        'amountTotal',
        'dollarTotal',
        'dollarPrice',

        'inventory',

        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation'
    ];

    public function cashier()
    {
        return $this->belongsTo(Cashier::class, 'cashier_id');
    }

    public function person(){
        return $this->belongsTo(People::class, 'person_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details(){
        return $this->hasMany(PawnRegisterDetail::class, 'pawn_register_id');
    }
    public function month(){
        return $this->hasMany(PawnRegisterMonth::class, 'pawnRegister_id');
    }

    public function payments(){
        return $this->hasMany(PawnRegisterPayment::class, 'pawn_register_id');
    }

    public function amountAditional(){
        return $this->hasMany(PawnRegisterAmountAditional::class, 'pawnRegister_id');
    }

    //para ver que persona es la que entrega el prestamo al beneficiario
    public function agentDelivered()
    {
        return $this->belongsTo(User::class, 'delivered_userId');
    }
}
