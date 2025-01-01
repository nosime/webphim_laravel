<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;

class Permission extends Model
{
    use HasTimestamps;
    protected $primaryKey = 'permission_id';

    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
