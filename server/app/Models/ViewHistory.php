<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewHistory extends Model
{
    // Tên bảng (nếu không tuân theo quy tắc đặt tên mặc định)
    protected $table = 'view_history';

    // Khóa chính
    protected $primaryKey = 'history_id';

    // Vô hiệu hóa timestamps nếu bảng không có created_at, updated_at
    public $timestamps = false;

    // Các trường được phép mass assignment
    protected $fillable = [
        'user_id',
        'movie_id',
        'episode_id',
        'server_id',
        'view_date',
        'view_duration',
        'last_position',
        'completed',
        'device_type',
        'device_id',
        'ip_address'
    ];

    // Các thuộc tính được cast
    protected $casts = [
        'view_date' => 'datetime',
        'completed' => 'boolean'
    ];

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Quan hệ với Movie
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }

    // Quan hệ với Episode
    public function episode()
    {
        return $this->belongsTo(Episode::class, 'episode_id', 'episode_id');
    }

    // Quan hệ với Server
    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }
}
