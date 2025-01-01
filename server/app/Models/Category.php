<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'name', 'slug', 'description',
        'display_order', 'is_active'
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_categories', 'category_id', 'movie_id');
    }
}
