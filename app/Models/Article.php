<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable =[
        'modelGarment_id',
        'brandGarment_id',
        'categoryGarment_id',
        'name',
        'description',
        'image',
        'deleted_at'
    ];

    public function model()
    {
        return $this->belongsTo(ModelGarment::class, 'modelGarment_id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryGarment::class, 'categoryGarment_id');
    }
    public function developer()
    {
        return $this->hasMany(ArticlesDeveloper::class, 'article_id');
    }

    public function marca()
    {
        return $this->belongsTo(BrandGarment::class, 'brandGarment_id');
    }
}
