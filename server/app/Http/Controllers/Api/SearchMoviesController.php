<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchMoviesController extends Controller
{
    public function searchMovies(Request $request)
    {
        try {
            $searchTerm = $request->query('q', '');
            $page = $request->query('page', 1);
            $limitItems = 24;
            $offset = ($page - 1) * $limitItems;

            // Get total count
            $totalMovies = DB::table('Movies')
                ->where('is_visible', 1)
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('origin_name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('slug', 'LIKE', "%{$searchTerm}%");
                })
                ->count();

            $totalPages = ceil($totalMovies / $limitItems);

            // Get movies with categories and countries
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
                    DB::raw('(SELECT STRING_AGG(c.name, \',\')
                            FROM Categories c
                            INNER JOIN movie_categories mc ON c.category_id = mc.category_id
                            WHERE mc.movie_id = m.movie_id) as Categories'),
                    DB::raw('(SELECT STRING_AGG(co.name, \',\')
                            FROM Countries co
                            INNER JOIN movie_countries mco ON co.country_id = mco.country_id
                            WHERE mco.movie_id = m.movie_id) as Countries')
                ])
                ->where('m.is_visible', 1)
                ->where(function($query) use ($searchTerm) {
                    $query->where('m.name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('m.origin_name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('m.slug', 'LIKE', "%{$searchTerm}%");
                })
                ->orderByDesc('m.created_at')
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
                    'totalPages' => $totalPages
                ]
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server khi lấy danh sách tìm kiếm phim',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    public function searchMoviesType(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 24);
            $offset = ($page - 1) * $limit;

            $query = DB::table('Movies as m')
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
                    DB::raw('(SELECT STRING_AGG(c.name, \',\')
                            FROM movie_categories mc
                            JOIN Categories c ON mc.category_id = c.category_id
                            WHERE mc.movie_id = m.movie_id) as Categories'),
                    DB::raw('(SELECT STRING_AGG(co.name, \',\')
                            FROM movie_countries mco
                            JOIN Countries co ON mco.country_id = co.country_id
                            WHERE mco.movie_id = m.movie_id) as Countries'),
                    DB::raw('COUNT(*) OVER() as TotalCount')
                ])
                ->where('m.is_visible', 1);

            // Add type filter
            if ($request->has('types')) {
                $types = explode(',', $request->query('types'));
                $query->whereIn('m.type', $types);
            }

            // Add categories filter
            if ($request->has('categories')) {
                $categories = explode(',', $request->query('categories'));
                $query->whereExists(function ($query) use ($categories) {
                    $query->select(DB::raw(1))
                          ->from('movie_categories as mc')
                          ->join('Categories as c', 'mc.category_id', '=', 'c.category_id')
                          ->whereRaw('mc.movie_id = m.movie_id')
                          ->whereIn('c.slug', $categories);
                });
            }

            // Add countries filter
            if ($request->has('countries')) {
                $countries = explode(',', $request->query('countries'));
                $query->whereExists(function ($query) use ($countries) {
                    $query->select(DB::raw(1))
                          ->from('movie_countries as mc')
                          ->join('Countries as c', 'mc.country_id', '=', 'c.country_id')
                          ->whereRaw('mc.movie_id = m.movie_id')
                          ->whereIn('c.slug', $countries);
                });
            }

            // Add years filter
            if ($request->has('years')) {
                $years = explode(',', $request->query('years'));
                $query->whereIn('m.year', $years);
            }

            $movies = $query->orderByDesc('m.created_at')
                           ->offset($offset)
                           ->limit($limit)
                           ->get();

            $totalItems = $movies->first()->TotalCount ?? 0;

            return response()->json([
                'success' => true,
                'data' => $movies,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'totalItems' => $totalItems,
                    'totalPages' => ceil($totalItems / $limit)
                ]
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }
}
