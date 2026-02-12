<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employe extends Model
{
    use HasFactory, SoftDeletes;
    // protected $dates = ['deleted_at'];

    // // public function payments(){
    // //     return $this->hasMany(EmployePayment::class);
    // // }

    // public function employeJob(){
    //     return $this->belongsTo(EmployeJob::class, 'employe_job_id');
    // }
}
