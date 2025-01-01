<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $primaryKey = 'server_id';

    protected $fillable = [
        'name',
        'type',
        'priority',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];

    public $timestamps = true;

    public function episodes()
    {
        return $this->hasMany(Episode::class, 'server_id');
    }

    // Relationship with ViewHistory
    public function viewHistory()
    {
        return $this->hasMany(ViewHistory::class, 'server_id');
    }
}
