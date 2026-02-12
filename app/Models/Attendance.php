<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'spreadsheet',
        'ci',
        'date',
        'hour',
        
        'register_userId',
        'register_agentType',

        // 'deleted_userId',
        // 'deleted_agentType',
        // 'deletedObservation',
        // 'deleted_at'
    ];


    public function peopleAsistencia()
    {
        return $this->belongsTo(People::class, 'ci');
    }


}
