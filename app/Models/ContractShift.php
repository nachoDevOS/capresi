<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'shift_id',
        'start',
        'finish',
        'status',
        'register_userId',
        'register_agentType'
    ];

}
