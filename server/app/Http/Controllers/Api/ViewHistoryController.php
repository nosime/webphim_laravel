<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ViewHistory;
use App\Models\User;
use App\Models\Movie;
use App\Models\Episode;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\HasRoleCheck;

class ViewHistoryController extends Controller
{
    use HasRoleCheck;
    /**
     * Lưu lịch sử xem
     */
    public function saveViewHistory(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'movieId' => 'required|exists:movies,movie_id',
            'episodeId' => 'required|exists:episodes,episode_id',
            'serverId' => 'required|exists:servers,server_id'
        ]);

        try {
            // Lấy user ID từ token
            $userId = Auth::id();

            // Kiểm tra bản ghi tồn tại
            $existingRecord = ViewHistory::where('user_id', $userId)
                ->where('movie_id', $validatedData['movieId'])
                ->where('episode_id', $validatedData['episodeId'])
                ->first();

            if ($existingRecord) {
                // Cập nhật bản ghi hiện tại
                $existingRecord->update([
                    'server_id' => $validatedData['serverId'],
                    'view_date' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã cập nhật lịch sử xem'
                ]);
            } else {
                // Tạo bản ghi mới
                $viewHistory = ViewHistory::create([
                    'user_id' => $userId,
                    'movie_id' => $validatedData['movieId'],
                    'episode_id' => $validatedData['episodeId'],
                    'server_id' => $validatedData['serverId'],
                    'view_date' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm lịch sử xem mới',
                    'data' => [
                        'historyId' => $viewHistory->history_id
                    ]
                ]);
            }
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage() ?: 'Lỗi server'
            ], 500);
        }
    }

    /**
     * Lấy lịch sử xem của người dùng
     */
    public function getUserHistory(Request $request,$userId = null)
    {
        try {
           // Kiểm tra xác thực
           if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $currentUser = Auth::user();
        $targetUserId = $userId ? intval($userId) : $currentUser->user_id;

        // Kiểm tra quyền truy cập
        try {
            $userId = $this->checkUserAccess($userId);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }

            // Lấy lịch sử xem với các quan hệ
            $viewHistories = ViewHistory::with([
                'user:user_id,username',
                'movie:movie_id,name,slug,thumb_url,poster_url',
                'episode:episode_id,name,slug,file_name,episode_number',
                'server:server_id,name'
            ])
                ->where('user_id', $targetUserId)
                ->orderBy('view_date', 'desc')
                ->get()
                ->map(function ($history) {
                    return [
                        'historyId' => $history->history_id,
                        'user' => [
                            'userId' => $history->user->user_id,
                            'username' => $history->user->username
                        ],
                        'movie' => [
                            'movieId' => $history->movie->movie_id,
                            'name' => $history->movie->name,
                            'slug' => $history->movie->slug,
                            'thumbUrl' => $history->movie->thumb_url,
                            'posterUrl' => $history->movie->poster_url
                        ],
                        'episode' => [
                            'episodeId' => $history->episode->episode_id,
                            'name' => $history->episode->name,
                            'slug' => $history->episode->slug,
                            'fileName' => $history->episode->file_name,
                            'episodeNumber' => $history->episode->episode_number
                        ],
                        'server' => [
                            'serverId' => $history->server->server_id,
                            'name' => $history->server->name
                        ],
                        'viewDate' => $history->view_date
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $viewHistories
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage() ?: 'Lỗi server'
            ], 500);
        }
    }
}
