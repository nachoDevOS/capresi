<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftsHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'shifts_id',
        'hour_id',

        'dayWeekNumber',
        'dayWeekName',
        
        'name',

        'hourStart',
        'hourFinish',

        'minuteLate',
        'minuteEarly',

        'rangeStartInput',
        'rangeStartOutput',

        'rangeFinishInput',
        'rangeFinishOutput',

        'day',

        'register_userId',
        'register_agentType',
        'deleted_userId',
        'deleted_agentType',
        'deletedObservation',
        'deleted_at'
    ];


}
