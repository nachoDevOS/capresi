<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'codeManual',
        'pawnRegisterDetail_id',
        'itemType_id',
        'image',
        'price',
        'quantity',
        'stock',
        'amountTotal',
        'dollarTotal',
        'dollarPrice',

        'description',
        'status',

        'typeRegister',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deletedUser_id',
        'deletedRole',
        'deletedObservation'
    ];

    public function features()
    {
        return $this->hasMany(InventoriesFeature::class,'inventory_id');
    }


    public function item()
    {
        return $this->belongsTo(ItemType::class, 'itemType_id');
    }

    public function register()
    {
        return $this->belongsTo(User::class, 'registerUser_id');
    }

    public function pawnRegisterDetail()
    {
        return $this->belongsTo(PawnRegisterDetail::class,'pawnRegisterDetail_id' );
    }



}
