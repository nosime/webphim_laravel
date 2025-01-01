<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $primaryKey = 'movie_id';

    protected $fillable = [
        'name', 'origin_name', 'slug', 'description', 'content',
        'type', 'status', 'thumb_url', 'poster_url', 'banner_url',
        'trailer_url', 'duration', 'episode_current', 'episode_total',
        'quality', 'language', 'year', 'actors', 'directors',
        'is_copyright', 'is_subtitled', 'is_premium', 'is_visible',
        'views', 'views_day', 'views_week', 'views_month',
        'rating_value', 'rating_count',
        'tmdb_id', 'imdb_id', 'tmdb_rating', 'tmdb_votecount' ];

         protected $casts = [
        'published_at' => 'datetime',
        'is_copyright' => 'boolean',
        'is_subtitled' => 'boolean',
        'is_premium' => 'boolean',
        'is_visible' => 'boolean',
        'rating_value' => 'decimal:1',
        'tmdb_rating' => 'decimal:1'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'movie_categories', 'movie_id', 'category_id');
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'movie_countries', 'movie_id', 'country_id');
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class, 'movie_id');
    }

    public function ratings()
    {
        return $this->hasMany(MovieRating::class, 'movie_id');
    }

    public function viewHistory()
    {
        return $this->hasMany(ViewHistory::class, 'movie_id');
    }
}
