<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDayAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractDay_id',
        'attendaceStart_id',
        'attendaceFinish_id',
        'shiftHour_id',
        'hour_id',
        'amount',
        'minuteLate',

        'start',
        'finish',

        'lostHour',
        'typeHour',

        'license_id',
        'typeLicense',

        'status',
        'register_userId',
        'register_agentType',
        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'
    ];


    public function shiftHour()
    {
        return $this->belongsTo(ShiftsHour::class,'shiftHour_id');
    }
    public function hours()
    {
        return $this->belongsTo(Hour::class,'hour_id');
    }



    
}
