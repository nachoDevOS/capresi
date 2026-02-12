<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LatePenalty extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];use HasFactory;

    protected $fillable = [
        'start',
        'finish',
        'amount',
        'status',

        'register_userId',
        'register_agentType',

        'deleted_userId',
        'deleted_agentType',
        'deletedObservation'
    ];
}
