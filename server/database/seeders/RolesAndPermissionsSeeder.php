<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Tạo Permissions trước
        $permissions = [
            ['name' => 'Xem Phim', 'code' => 'VIEW_MOVIE', 'description' => 'Quyền xem phim'],
            ['name' => 'Thêm Phim', 'code' => 'ADD_MOVIE', 'description' => 'Quyền thêm phim mới'],
            ['name' => 'Sửa Phim', 'code' => 'EDIT_MOVIE', 'description' => 'Quyền sửa thông tin phim'],
            ['name' => 'Xóa Phim', 'code' => 'DELETE_MOVIE', 'description' => 'Quyền xóa phim'],
            ['name' => 'Quản Lý Users', 'code' => 'MANAGE_USERS', 'description' => 'Quyền quản lý người dùng'],
            ['name' => 'Full Quyền', 'code' => 'FULL_CONTROL', 'description' => 'Toàn quyền quản lý hệ thống']
        ];

        foreach($permissions as $permission) {
            Permission::firstOrCreate(
                ['code' => $permission['code']],
                $permission
            );
        }

        // Sau đó tạo Roles và gán permissions
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Quản trị viên - Full quyền',
                'permissions' => ['FULL_CONTROL']
            ],
            [
                'name' => 'Manager',
                'description' => 'Quản lý - Quyền thêm, sửa, xóa',
                'permissions' => ['ADD_MOVIE', 'EDIT_MOVIE', 'DELETE_MOVIE']
            ],
            [
                'name' => 'Editor',
                'description' => 'Biên tập viên - Quyền thêm và sửa',
                'permissions' => ['ADD_MOVIE', 'EDIT_MOVIE']
            ],
            [
                'name' => 'Moderator',
                'description' => 'Điều hành viên - Quyền xem và thêm',
                'permissions' => ['VIEW_MOVIE', 'ADD_MOVIE']
            ],
            [
                'name' => 'Member',
                'description' => 'Thành viên - Chỉ có quyền xem',
                'permissions' => ['VIEW_MOVIE']
            ]
        ];

        foreach($roles as $role) {
            $newRole = Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'name' => $role['name'],
                    'description' => $role['description']
                ]
            );
            $permissions = Permission::whereIn('code', $role['permissions'])->get();
            $newRole->permissions()->sync($permissions);
        }
    }
}
