<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoriesFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'title',
        'value',
        'deleted_at'
    ];
}
