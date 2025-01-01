<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieRating extends Model
{
    protected $primaryKey = 'rating_id';

    protected $fillable = [
        'user_id',
        'movie_id',
        'rating_type',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }
}
