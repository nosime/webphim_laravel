<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Tạo tài khoản admin
        $adminUser = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'display_name' => 'Administrator',
                'is_active' => true,
                'is_verified' => true
            ]
        );

        // Gán role Admin cho user admin
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser->roles()->sync([$adminRole->role_id]);

        // Tạo tài khoản test
        $testUser = User::firstOrCreate(
            ['username' => 'test'],
            [
                'email' => 'test@example.com',
                'password' => Hash::make('test123'),
                'display_name' => 'Test User',
                'is_active' => true
            ]
        );

        // Gán role Member cho test user
        $memberRole = Role::where('name', 'Member')->first();
        $testUser->roles()->sync([$memberRole->role_id]);
    }
}
