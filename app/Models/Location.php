<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'latitude',
        'longitude',
        'timestamp',
        'battery',
        'accuracy',
        'raw_data'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'raw_data' => 'array'
    ];
}
