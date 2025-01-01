<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthController extends Controller implements HasMiddleware
{
    /**
     * Create a new AuthController instance.
     */
    public static function middleware(): array
    {
        return [
            // Add proper except for public endpoints
            new Middleware('auth:api', except: ['login', 'register']),
        ];
    }

    /**
     * Get a JWT via given credentials.
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int)(config('jwt.ttl', 60) * 60) // Ensure integer value
        ];
    }

    public function login()
    {
        try {
            $credentials = request()->validate([
                'username' => 'required|string',
                'password' => 'required|string'
            ]);

            // Attempt đăng nhập với username
            if (!$token = Auth::attempt([
                'username' => $credentials['username'],
                'password' => $credentials['password']
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản hoặc mật khẩu không đúng'
                ], 401);
            }

            // Lấy thông tin user đã được xác thực
            $user = Auth::user();

            // Kiểm tra tài khoản active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa'
                ], 401);
            }

            // Lấy role và permissions
            $userRole = Role::join('user_roles', 'roles.role_id', '=', 'user_roles.role_id')
                           ->where('user_roles.user_id', $user->user_id)
                           ->select('roles.*')
                           ->first();
                           $permissions = $userRole ? $userRole->permissions()->pluck('code')->toArray() : [];
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'UserID' => $user->user_id,
                        'Username' => $user->username,
                        'Email' => $user->email,
                        'DisplayName' => $user->display_name,
                        'Avatar' => $user->avatar,
                        'IsVerified' => (bool)$user->is_verified,
                        'IsActive' => (bool)$user->is_active,
                        'IsPremium' => (bool)$user->is_premium,
                        'RoleName' => $userRole ? $userRole->name : null
                    ],
                    'permissions' => $permissions,
                    'token' => $token
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server, vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            // Remove any auth checks - this is a public endpoint
            $validated = $request->validate([
                'username' => 'required|string|unique:users',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
                'display_name' => 'nullable|string'
            ]);

            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'display_name' => $validated['display_name'] ?? $validated['username'],
                'is_active' => true,
                'is_verified' => false
            ]);

            // Gán role member mặc định (role_id = 5)
            $user->roles()->attach(5);

            // Automatically login after registration and return token
            $token = Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công',
                'data' => [
                    'token' => $this->respondWithToken($token)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server, vui lòng thử lại sau',
                'error' => $e->getMessage() // Add for debugging
            ], 500);
        }
    }

    /**
     * Get authenticated user info
     */
    public function me()
    {
        try {
            $user = Auth::user();
            // Lấy role và permissions
            $userRole = Role::join('user_roles', 'roles.role_id', '=', 'user_roles.role_id')
                           ->where('user_roles.user_id', $user->user_id)
                           ->select('roles.*')
                           ->first();
                           $permissions = $userRole ? $userRole->permissions()->pluck('code')->toArray() : [];

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'UserID' => $user->user_id,
                        'Username' => $user->username,
                        'Email' => $user->email,
                        'DisplayName' => $user->display_name,
                        'Avatar' => $user->avatar,
                        'IsVerified' => (bool)$user->is_verified,
                        'IsActive' => (bool)$user->is_active,
                        'IsPremium' => (bool)$user->is_premium,
                        'RoleName' => $userRole ? $userRole->name : null
                    ],
                    'permissions' => $permissions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server, vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token)
     */
    public function logout()
    {
        try {
            Auth::logout();
            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server, vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        try {
            $token = Auth::refresh();
            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $this->respondWithToken($token)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi refresh token'
            ], 500);
        }
    }
}
