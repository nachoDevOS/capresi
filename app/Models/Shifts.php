<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shifts extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',

        'register_userId',
        'register_agentType',
        'deleted_userId',
        'deleted_agentType',
        'deletedObservation',
        'deleted_at'

        
    ];


}
