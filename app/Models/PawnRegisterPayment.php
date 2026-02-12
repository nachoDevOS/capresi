<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PawnRegisterPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'pawn_register_id',
        'user_id',
        'date',
        'amount',
        'observations'
    ];

    public function pawn(){
        return $this->belongsTo(PawnRegister::class, 'pawn_register_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
