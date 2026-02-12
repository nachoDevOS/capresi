<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PawnRegisterMonth extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'pawnRegister_id',
        'start',
        'finish',
        'interest',
        'debt',
        'paid',
        'deleted_at',
        'dollarTotal',
        'dollarPrice'
    ];

}
