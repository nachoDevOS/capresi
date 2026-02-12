<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemType extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'item_category_id',
        'name',
        'unit',
        'price',
        'max_price',
        'description',
        'images',
        'status',
    ];

    public function category(){
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }
}
