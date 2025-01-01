<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListMoviesRDController extends Controller
{
    public function getMoviesListPaginatedRandom(Request $request)
    {
        try {
            $page = $request->route('page', 1);
            $limitItems = $request->route('limit', 24);
            $offset = ($page - 1) * $limitItems;

            // Get total count
            $totalMovies = Movie::where('is_visible', 1)->count();
            $totalPages = ceil($totalMovies / $limitItems);

            // Get random movies with pagination
            $movies = DB::table('Movies as m')
                ->select([
                    'm.movie_id as MovieID',
                    'm.name as Name',
                    'm.origin_name as OriginName',
                    'm.slug as Slug',
                    'm.type as Type',
                    'm.status as Status',
                    'm.thumb_url as ThumbUrl',
                    'm.poster_url as PosterUrl',
                    'm.banner_url as BannerUrl',
                    'm.trailer_url as TrailerUrl',
                    'm.episode_current as Episode_Current',
                    'm.episode_total as Episode_Total',
                    'm.quality as Quality',
                    'm.language as Lang',
                    'm.views as Views',
                    'm.year as Year',
                    DB::raw('(SELECT STRING_AGG(c.name, \', \')
                            FROM Categories c
                            INNER JOIN movie_categories mc ON c.category_id = mc.category_id
                            WHERE mc.movie_id = m.movie_id) as Categories'),
                    DB::raw('(SELECT STRING_AGG(co.name, \', \')
                            FROM Countries co
                            INNER JOIN movie_countries mco ON co.country_id = mco.country_id
                            WHERE mco.movie_id = m.movie_id) as Countries')
                ])
                ->where('m.is_visible', 1)
                ->orderByRaw('NEWID()') // For SQL Server
                ->offset($offset)
                ->limit($limitItems)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $movies,
                'pagination' => [
                    'page' => $page,
                    'limitItems' => $limitItems,
                    'totalItems' => $totalMovies,
                    'totalPages' => $totalPages,
                ]
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy danh sách phim random page',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function getRandomMovies(Request $request)
    {
        try {
            $batchSize = 10;
            $limit = $request->route('limit', $batchSize);

            $movies = DB::query()
                ->fromSub(function ($query) use ($limit) {
                    $query->select('movie_id')
                          ->from('Movies')
                          ->where('is_visible', 1)
                          ->orderByRaw('NEWID()')
                          ->limit($limit);
                }, 'MovieIds')
                ->join('Movies as m', 'm.movie_id', '=', 'MovieIds.movie_id')
                ->select([
                    'm.movie_id as MovieID',
                    'm.name as Name',
                    'm.origin_name as OriginName',
                    'm.slug as Slug',
                    'm.type as Type',
                    'm.status as Status',
                    'm.thumb_url as ThumbUrl',
                    'm.poster_url as PosterUrl',
                    'm.banner_url as BannerUrl',
                    'm.trailer_url as TrailerUrl',
                    'm.episode_current as Episode_Current',
                    'm.episode_total as Episode_Total',
                    'm.quality as Quality',
                    'm.language as Lang',
                    'm.views as Views',
                    'm.year as Year',
                    DB::raw('(SELECT STRING_AGG(c.name, \', \')
                            FROM Categories c
                            JOIN movie_categories mc ON c.category_id = mc.category_id
                            WHERE mc.movie_id = m.movie_id) as Categories'),
                    DB::raw('(SELECT STRING_AGG(co.name, \', \')
                            FROM Countries co
                            JOIN movie_countries mco ON co.country_id = mco.country_id
                            WHERE mco.movie_id = m.movie_id) as Countries')
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $movies
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy danh sách phim random',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
