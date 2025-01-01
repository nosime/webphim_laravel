<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchLater extends Model
{
    protected $table = 'watch_later'; // Add this line to specify correct table name
    protected $primaryKey = 'watch_later_id';

    // Tắt timestamps nếu bảng không có created_at, updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'movie_id',
        'notes',
        'priority',
        'added_date'
    ];

    protected $casts = [
        'added_date' => 'datetime',
        'priority' => 'integer'
    ];

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với Movie
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }
}
