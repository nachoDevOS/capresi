<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'shift_id',

        'spreadsheet',
        'periodMonth',
        'periodYear',

        'date',
        'paymentDay',
        'dayWeekNumber',
        'dayWeekName',

        'job',
        'minuteLate',
        'cantHour',
        'cantHourAmount',

        'status',
        'typeLicense',
        'register_userId',
        'register_agentType',
        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'
    ];

    public function contractDayAttendance()
    {
        return $this->hasMany(ContractDayAttendance::class, 'contractDay_id');
    }
}
