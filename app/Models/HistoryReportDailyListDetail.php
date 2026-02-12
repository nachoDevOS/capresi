<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryReportDailyListDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'historyReport_id',
        'loan_id',
        'dailyPayment',
        'typeLoan',
        'lateDays',
        'latePayment',
        'color',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }
}
