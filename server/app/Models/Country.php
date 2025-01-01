<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    protected $fillable = [
        'name', 'slug', 'code', 'is_active'
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_countries', 'country_id', 'movie_id');
    }
}
