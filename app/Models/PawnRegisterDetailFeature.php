<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PawnRegisterDetailFeature extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'pawn_register_detail_id',
        'item_feature_id',
        'title',
        'value'
    ];

    public function feature(){
        return $this->belongsTo(ItemFeature::class, 'item_feature_id');
    }
}
