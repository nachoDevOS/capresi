<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpreadsheetContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'spreadsheet_id',
        'cashier_id',
        'cashierMovement_id',
        'paid',
        'dateStart',
        'dateFinish',
        'dayWorked',
        'dayWorkedFebrary',

        'salary',
        'payment',
        'liquidPaid',
        'minuteLate',

        'minuteLateAmount',
        'cantHourAmount',
        'advancement',
        'paid_userId',
        'paid_agentType',
        'paidDate',
        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'


    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
    public function spreadsheet()
    {
        return $this->belongsTo(Spreadsheet::class, 'spreadsheet_id');
    }
}
