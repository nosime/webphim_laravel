<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';

    protected $fillable = [
        'username',
        'email',
        'password',
        'display_name',
        'avatar',
        'cover_photo',
        'bio',
        'gender',
        'birthday',
        'phone_number',
        'address',
        'is_verified',
        'is_active',
        'is_premium',
        'premium_expire_date'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'last_login_ip'
    ];

    protected $casts = [
        'birthday' => 'date',
        'premium_expire_date' => 'datetime',
        'last_login_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_premium' => 'boolean'
    ];

    public static function getUserWithRole($userId)
    {
        return DB::table('users as u')
            ->leftJoin('user_roles as ur', 'u.user_id', '=', 'ur.user_id')
            ->leftJoin('roles as r', 'ur.role_id', '=', 'r.role_id')
            ->where('u.user_id', $userId)
            ->select('u.user_id as UserID', 'r.name as RoleName')
            ->first();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function isAdmin(): bool
    {
        return $this->roles()
            ->where('name', 'Admin')
            ->orWhere('role_id', 1)
            ->exists();
    }

    public function hasRole($roleName): bool
    {
        return $this->roles()
            ->where('name', $roleName)
            ->exists();
    }

    public function getRoleAttribute()
    {
        return $this->attributes['role'];
    }
    // JWT required methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
