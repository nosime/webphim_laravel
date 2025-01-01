<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Episode;
use App\Models\ViewHistory;
use App\Models\WatchLater;
use App\Models\MovieRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteMovieController extends Controller
{
    public function deleteMovie($id)
    {
        try {
            DB::beginTransaction();

            // 1. Xóa lịch sử xem
            ViewHistory::whereIn('episode_id', function($query) use ($id) {
                $query->select('episode_id')
                      ->from('episodes')
                      ->where('movie_id', $id);
            })->delete();

            // 2. Xóa các tập phim
            Episode::where('movie_id', $id)->delete();

            // 3. Xóa danh sách xem sau
            WatchLater::where('movie_id', $id)->delete();

            // 4. Xóa danh sách yêu thích
            MovieRating::where('movie_id', $id)->delete();

            // 5. Xóa liên kết với Categories
            DB::table('movie_categories')->where('movie_id', $id)->delete();

            // 6. Xóa liên kết với Countries
            DB::table('movie_countries')->where('movie_id', $id)->delete();

            // 7. Cuối cùng xóa phim
            Movie::destroy($id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa phim thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa phim: ' . $e->getMessage()
            ], 500);
        }
    }
}
