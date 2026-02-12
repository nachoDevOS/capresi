<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticlesDeveloper extends Model
{
    use HasFactory;
    protected $fillable = [
        'article_id',
        'title',
        'tool',
        'type',
        'detail',
        'concatenar',
        'required',
        'deleted_at'
    ];
}
