<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'status',
        'deleted_at'
    ];

    public function details()
    {
        return $this->hasMany(RouteCollector::class, 'route_id');
    }


}
