<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id', 'user_id', 'cashier_movement_category_id', 'balance', 'amount', 'description', 'type', 'deleted_at', 'status', 'transferCashier_id'
    ];

    public function cashier(){
        return $this->belongsTo(Cashier::class, 'cashier_id');
    }

    // public function cashier_from(){
    //     return $this->belongsTo(Cashier::class, 'cashier_id_from');
    // }

    // public function cashier_to(){
    //     return $this->belongsTo(Cashier::class, 'cashier_id_to');
    // }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function cashierMovementCategory()
    {
        return $this->belongsTo(CashierMovementCategory::class, 'cashier_movement_category_id')->withTrashed();
    }


}
