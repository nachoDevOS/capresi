<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        // 'spreadsheet',
        'dateStart',
        'dateFinish',
        'file',
        'description',
        'type',

        'register_userId',
        'register_agentType',

        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'
    ];


    public function registerUser()
    {
        return $this->belongsTo(User::class, 'register_userId');

    }
}
