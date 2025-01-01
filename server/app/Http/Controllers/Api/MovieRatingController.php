<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovieRating;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\User;
use App\Traits\HasRoleCheck;

class MovieRatingController extends Controller implements HasMiddleware
{
    use HasRoleCheck;
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api')
        ];
    }

    public function toggleLike(Request $request)
    {
        try {
            $validated = $request->validate([
                'movieId' => 'required|exists:movies,movie_id'
            ]);

            $user = Auth::user();

            $existingRating = MovieRating::where([
                'user_id' => $user->user_id,
                'movie_id' => $validated['movieId']
            ])->first();

            $isCurrentlyLiked = $existingRating && $existingRating->rating_type === 'like';

            if ($isCurrentlyLiked) {
                $existingRating->delete();
                return response()->json([
                    'success' => true,
                    'liked' => false,
                    'message' => 'Đã bỏ thích phim'
                ]);
            }

            // Add or Update rating
            MovieRating::updateOrCreate(
                [
                    'user_id' => $user->user_id,
                    'movie_id' => $validated['movieId']
                ],
                [
                    'rating_type' => 'like',
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'liked' => true,
                'message' => 'Đã thích phim'
            ]);

        } catch (\Exception $e) {
            Log::error('Movie rating error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thực hiện thao tác'
            ], 500);
        }
    }

    public function getFavorites(Request $request, $userId = null)
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

            // Query danh sách yêu thích
            $favorites = DB::table('movies as m')
                ->select([
                    'm.movie_id as MovieID',
                    'm.name as Name',
                    'm.origin_name as OriginName',
                    'm.slug as Slug',
                    'm.thumb_url as ThumbUrl',
                    'm.poster_url as PosterUrl',
                    'm.quality as Quality',
                    'm.language as Language',
                    'm.year as Year',
                    'm.views as Views',
                    'mr.created_at as FavoriteDate',
                    DB::raw('(SELECT STRING_AGG(c.name, \', \') FROM movie_categories mc
                            JOIN categories c ON mc.category_id = c.category_id
                            WHERE mc.movie_id = m.movie_id) as Categories'),
                    DB::raw('(SELECT STRING_AGG(co.name, \', \') FROM movie_countries mco
                            JOIN countries co ON mco.country_id = co.country_id
                            WHERE mco.movie_id = m.movie_id) as Countries')
                ])
                ->join('movie_ratings as mr', function($join) use ($targetUserId) {
                    $join->on('m.movie_id', '=', 'mr.movie_id')
                         ->where('mr.user_id', '=', $targetUserId)
                         ->where('mr.rating_type', '=', 'like');
                })
                ->where('m.is_visible', '=', 1)
                ->orderBy('mr.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $favorites
            ]);

        } catch (\Exception $e) {
            Log::error('Get favorites error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách phim yêu thích: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkLikeStatus($movieId)
    {
        try {
            $user = Auth::user();

            $isLiked = MovieRating::where('user_id', $user->user_id)
                ->where('movie_id', $movieId)
                ->where('rating_type', 'like')
                ->exists();

            return response()->json([
                'success' => true,
                'liked' => $isLiked
            ]);

        } catch (\Exception $e) {
            Log::error('Check like status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra trạng thái yêu thích'
            ], 500);
        }
    }
}
