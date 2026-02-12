<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'device_id', 'name'];

    public function locations()
    {
        return $this->hasMany(Location::class)->latest();
    }
}
