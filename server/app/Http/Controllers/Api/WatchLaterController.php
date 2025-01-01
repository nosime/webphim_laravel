<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchLater;
use App\Models\Movie;
use App\Models\User;
use App\Traits\HasRoleCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WatchLaterController extends Controller implements HasMiddleware
{
    use HasRoleCheck;

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api')  // Chuyển từ sanctum sang jwt auth
        ];
    }

    public function toggleWatchLater(Request $request)
    {
        try {
            $validated = $request->validate([
                'movieId' => 'required|exists:movies,movie_id'
            ]);

            $userId = Auth::id();
            $movieId = $validated['movieId'];

            $existingItem = WatchLater::where([
                'user_id' => $userId,
                'movie_id' => $movieId
            ])->first();

            if ($existingItem) {
                $existingItem->delete();
                return response()->json([
                    'success' => true,
                    'added' => false
                ]);
            }

            WatchLater::create([
                'user_id' => $userId,
                'movie_id' => $movieId,
                'added_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'added' => true
            ]);

        } catch (\Exception $e) {
            Log::error('WatchLater Error:', [
                'error' => $e->getMessage(),
                'user' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getWatchLater(Request $request, $userId = null)
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
            // Lấy danh sách phim xem sau với thông tin chi tiết
            $movies = Movie::with(['categories:name', 'countries:name'])
                ->select('movies.*', 'watch_later.notes', 'watch_later.added_date', 'watch_later.priority')
                ->join('watch_later', 'movies.movie_id', '=', 'watch_later.movie_id')
                ->where([
                    'watch_later.user_id' => $targetUserId,
                    'movies.is_visible' => true
                ])
                ->orderByDesc('watch_later.priority')
                ->orderByDesc('watch_later.added_date')
                ->get()
                ->map(function($movie) {
                    return [
                        'MovieID' => $movie->movie_id,
                        'Name' => $movie->name,
                        'OriginName' => $movie->origin_name,
                        'Slug' => $movie->slug,
                        'Type' => $movie->type,
                        'Status' => $movie->status,
                        'ThumbUrl' => $movie->thumb_url,
                        'PosterUrl' => $movie->poster_url,
                        'Episode_Current' => $movie->episode_current,
                        'Episode_Total' => $movie->episode_total,
                        'Quality' => $movie->quality,
                        'Language' => $movie->language,
                        'Year' => $movie->year,
                        'Views' => $movie->views,
                        'Categories' => $movie->categories->pluck('name')->join(', '),
                        'Countries' => $movie->countries->pluck('name')->join(', '),
                        'Notes' => $movie->notes,
                        'AddedDate' => $movie->added_date,
                        'Priority' => $movie->priority
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $movies
            ]);

        } catch (\Exception $error) {
            Log::error('GetWatchLater Error:', ['error' => $error]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy danh sách xem sau'
            ], 500);
        }
    }
}
