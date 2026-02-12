<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'transaction_id',
        'cashier_id',
        'amount',
        'dollarTotal',
        'dollarPrice',
        'agent_id',
        'agentType',
        'status',

        'deleted_at',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation'
    ];
    
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function register()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
