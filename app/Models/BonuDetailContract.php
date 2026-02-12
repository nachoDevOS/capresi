<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonuDetailContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'bonuDetail_id',
        'deleted_at'
    ];
}
