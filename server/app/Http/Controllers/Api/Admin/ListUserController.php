<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ListUserController extends Controller
{
    /**
     * Lấy danh sách người dùng có phân trang
     */
    public function getUsers(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 24);

            // Raw query để match chính xác định dạng Express
            $totalQuery = DB::table('users')->count();

            $usersQuery = DB::table('users as u')
                ->leftJoin('user_roles as ur', 'u.user_id', '=', 'ur.user_id')
                ->leftJoin('roles as r', 'ur.role_id', '=', 'r.role_id')
                ->select(
                    'u.user_id as UserID',
                    'u.username as Username',
                    'u.email as Email',
                    'u.display_name as DisplayName',
                    'u.is_active as IsActive',
                    'u.is_verified as IsVerified',
                    'u.created_at as CreatedAt',
                    DB::raw('MAX(r.name) as RoleName')
                )
                ->groupBy(
                    'u.user_id',
                    'u.username',
                    'u.email',
                    'u.display_name',
                    'u.is_active',
                    'u.is_verified',
                    'u.created_at'
                )
                ->orderByDesc('u.created_at')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get();

            // Tính toán phân trang
            $totalItems = $totalQuery;
            $totalPages = ceil($totalItems / $limit);

            // Trả về JSON với định dạng y hệt Express
            return response()->json([
                'success' => true,
                'data' => $usersQuery->toArray(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'totalItems' => $totalItems,
                    'totalPages' => $totalPages,
                ],
            ]);

        } catch (\Exception $error) {
            Log::error('Error getting user list: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi máy chủ, không thể lấy danh sách người dùng',
            ], 500);
        }
    }

    /**
     * Xóa người dùng
     */
    public function deleteUser($id)
    {
        try {
            // Kiểm tra nếu ID không hợp lệ
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID người dùng không hợp lệ',
                ], 400);
            }

            // Kiểm tra người dùng có tồn tại hay không
            $userCheckQuery = User::getUserWithRole($id);

            if (!$userCheckQuery) {
                return response()->json([
                    'success' => false,  
                    'message' => 'Người dùng không tồn tại',
                ], 404);
            }

            // Kiểm tra vai trò của người dùng
            if ($userCheckQuery->RoleName === 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa người dùng có vai trò Admin',
                ], 403); 
            }

            // Xóa người dùng
            User::destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Người dùng đã được xóa thành công',
            ]);

        } catch (\Exception $error) {
            Log::error('Error deleting user: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi máy chủ, không thể xóa người dùng',
            ], 500);
        }
    }
}
