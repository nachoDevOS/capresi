<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryReportDailyList extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'agent_id',
        'type',
        'agentType',
        'dateTime',

        'deleted_at',
        'deletedUser_id',
        'deletedRole'
    ];

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    public function details()
    {
        return $this->hasMany(HistoryReportDailyListDetail::class, 'historyReport_id');
    }

    public function historyDetail()
    {
        return $this->hasMany(HistoryReportDailyListDetail::class, 'historyReport_id', 'id');
    }
    


}
