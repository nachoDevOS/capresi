<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spreadsheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'paid',
        'totalPayment',

        'description',

        'status',

        'register_userId',
        'register_agentType',

        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'
    ];

    public function spreadsheetContract()
    {
        return $this->hasMany(SpreadsheetContract::class);
    }

}
