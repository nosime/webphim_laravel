<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\User;

trait HasRoleCheck
{
    protected function checkUserAccess($targetUserId = null)
    {
        $currentUser = auth()->user();
        $userId = $targetUserId ? intval($targetUserId) : $currentUser->user_id;

        // Nếu người dùng đang truy cập dữ liệu người khác
        if ($targetUserId && $userId !== $currentUser->user_id) {
            // Check admin role bằng cách query trực tiếp
            $isAdmin = DB::table('user_roles as ur')
                ->join('roles as r', 'ur.role_id', '=', 'r.role_id')
                ->where('ur.user_id', $currentUser->user_id)
                ->where(function($query) {
                    $query->where('r.name', '=', 'Admin')
                          ->orWhere('r.role_id', '=', 1);
                })
                ->exists();

            if (!$isAdmin) {
                abort(403, 'Không có quyền truy cập dữ liệu của người khác');
            }

            // Kiểm tra user tồn tại
            $targetExists = User::where('user_id', $userId)->exists();
            if (!$targetExists) {
                abort(404, 'Không tìm thấy người dùng');
            }
        }

        return $userId;
    }
}
