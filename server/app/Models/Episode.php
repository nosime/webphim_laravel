<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $primaryKey = 'episode_id';

    protected $fillable = [
        'movie_id', 'server_id', 'name', 'slug',
        'file_name', 'episode_number', 'duration',
        'video_url', 'embed_url', 'thumbnail_url',
        'views', 'size', 'quality', 'is_processed'
    ];

    protected $casts = [
        'is_processed' => 'boolean'
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id');
    }
}
