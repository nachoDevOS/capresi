<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $fillable =[
        'code',
        'person_id',
        'typeSale',
        'amount',
        'discount',
        'amountTotal',
        'dollarPrice',
        'dollarTotal',
        'debt',

        'saleDate',
        'datePayment',
        'status',
        'description',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deletedUser_id',
        'deletedRole',
        'deletedObservation'
    ];

    public function person()
    {
        return $this->belongsTo(People::class, 'person_id');
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public function saleAgents()
    {
        return $this->hasMany(SaleAgent::class, 'sale_id');
    }

    public function register()
    {
        return $this->belongsTo(User::class, 'registerUser_id');
    }



}
