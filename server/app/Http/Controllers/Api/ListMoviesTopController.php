<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListMoviesTopController extends Controller
{
    public function getTopViewMovies(Request $request)
    {
        try {
            $batchSize = 10;
            $limit = $request->route('limit', $batchSize);

            $movies = DB::query()
                ->fromSub(function ($query) {
                    $query->from('Movies as m')
                        ->select([
                            'm.*',
                            DB::raw('(SELECT STRING_AGG(c.name, \', \')
                                    FROM Categories c
                                    INNER JOIN movie_categories mc ON c.category_id = mc.category_id
                                    WHERE mc.movie_id = m.movie_id) as Categories'),
                            DB::raw('(SELECT STRING_AGG(co.name, \', \')
                                    FROM Countries co
                                    INNER JOIN movie_countries mco ON co.country_id = mco.country_id
                                    WHERE mco.movie_id = m.movie_id) as Countries'),
                            DB::raw('RANK() OVER (ORDER BY m.views DESC) as MovieRank')
                        ])
                        ->where('m.is_visible', 1);
                }, 'MovieRanks')
                ->select([
                    'MovieRank as Rank',
                    'movie_id as MovieID',
                    'name as Name',
                    'origin_name as OriginName',
                    'slug as Slug',
                    'type as Type',
                    'status as Status',
                    'thumb_url as ThumbUrl',
                    'poster_url as PosterUrl',
                    'banner_url as BannerUrl',
                    'trailer_url as TrailerUrl',
                    'episode_current as Episode_Current',
                    'episode_total as Episode_Total',
                    'quality as Quality',
                    'language as Lang',
                    'views as Views',
                    'year as Year',
                    'Categories',
                    'Countries'
                ])
                ->where('MovieRank', '<=', $limit)
                ->orderBy('MovieRank')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $movies,
                'total' => $movies->count()
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy danh sách top phim',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
