<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hourStart',
        'hourFinish',
        'status',

        'minuteLate',
        'minuteEarly',
        
        'rangeStartInput',
        'rangeStartOutput',

        'rangeFinishInput',
        'rangeFinishOutput',

        'day',
        'description',
        'register_userId',
        'register_agentType',

        'deleted_userId',
        'deleted_agentType',
        'deletedObservation',
        'deleted_at'

    ];

}
