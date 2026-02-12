<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonuDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'people_id',
        'bonu_id',
        'cashier_id',
        'cashierMovement_id',
        'paid',
        'payment',
        'dayWorked',
        'paid_userId',
        'paid_agentType',
        'paidDate',
        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'
    ];

    public function bonu()
    {
        return $this->belongsTo(Bonu::class, 'bonu_id');
    }
    public function bonuDetailContract()
    {
        return $this->hasMany(BonuDetailContract::class, 'bonuDetail_id');
    }
    public function people()
    {
        return $this->belongsTo(People::class, 'people_id');
    }
}
