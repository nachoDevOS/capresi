<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPurchase extends Model
{
    use HasFactory;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'date',
        'date_limit',
        'cashier_id',
        'person_id',
        'amount',
        'interest_rate',
        'dollarTotal',
        'dollarPrice',
        'observations',
        'status',

        'delivered_userId',
        'delivered_userType',
        'dateDelivered',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation'
    ];

    public function cashier()
    {
        return $this->belongsTo(Cashier::class, 'cashier_id');
    }


    public function person()
    {
        return $this->belongsTo(People::class, 'person_id');
    }

    public function salaryPurchaseMonths()
    {
        return $this->hasMany(SalaryPurchaseMonth::class, 'salaryPurchase_id');
    }

    public function register()
    {
        return $this->belongsTo(User::class, 'registerUser_id');
    }
}
