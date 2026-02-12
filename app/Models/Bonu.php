<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonu extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'paid',
        'totalPayment',
        'description',
        'status',
        'register_userId',
        'register_agentType',
        'deleted_userId',
        'deleted_agentType',
        'deleted_observation',
        'deleted_at'
    ];

    public function bonuDetail()
    {
        return $this->hasMany(BonuDetail::class, 'bonu_id');
    }
}
