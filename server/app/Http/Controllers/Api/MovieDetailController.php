<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieDetailController extends Controller
{
    public function getMovieBySlug(Request $request, $slug)
    {
        try {
            // Get movie details
            $movie = DB::table('Movies as m')
                ->select([
                    'm.movie_id as MovieID',
                    'm.name as Name',
                    'm.origin_name as OriginName',
                    'm.slug as Slug',
                    'm.description as Description',
                    'm.content as Content',
                    'm.type as Type',
                    'm.status as Status',
                    'm.thumb_url as ThumbUrl',
                    'm.poster_url as PosterUrl',
                    'm.banner_url as BannerUrl',
                    'm.trailer_url as TrailerUrl',
                    'm.duration as Duration',
                    'm.episode_current as Episode_Current',
                    'm.episode_total as Episode_Total',
                    'm.quality as Quality',
                    'm.language as Language',
                    'm.year as Year',
                    'm.actors as Actors',
                    'm.directors as Directors',
                    'm.is_copyright as IsCopyright',
                    'm.is_subtitled as IsSubtitled',
                    'm.is_premium as IsPremium',
                    'm.is_visible as IsVisible',
                    'm.views as Views',
                    'm.views_day as ViewsDay',
                    'm.views_week as ViewsWeek',
                    'm.views_month as ViewsMonth',
                    'm.rating_value as Rating_Value',
                    'm.rating_count as Rating_Count',
                    'm.created_at as CreatedAt',
                    'm.updated_at as UpdatedAt',
                    'm.published_at as PublishedAt',
                    'm.tmdb_id as TmdbId',
                    'm.imdb_id as ImdbId',
                    'm.tmdb_rating as TmdbRating',
                    DB::raw('(SELECT STRING_AGG(c.name, \', \')
                            FROM Categories c
                            INNER JOIN movie_categories mc ON c.category_id = mc.category_id
                            WHERE mc.movie_id = m.movie_id) as Categories'),
                    DB::raw('(SELECT STRING_AGG(co.name, \', \')
                            FROM Countries co
                            INNER JOIN movie_countries mco ON co.country_id = mco.country_id
                            WHERE mco.movie_id = m.movie_id) as Countries')
                ])
                ->where('m.slug', $slug)
                ->where('m.is_visible', 1)
                ->first();

            if (!$movie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movie not found'
                ], 404);
            }

            // Get episodes grouped by server
            $serverEpisodes = DB::table('Servers as s')
                ->select([
                    's.server_id as ServerID',
                    's.name as ServerName',
                    's.type as ServerType',
                    DB::raw('(SELECT CAST((
                        SELECT
                            e.episode_id as EpisodeID,
                            e.name as Name,
                            e.slug as Slug,
                            e.file_name as FileName,
                            e.episode_number as EpisodeNumber,
                            e.duration as Duration,
                            e.video_url as VideoUrl,
                            e.embed_url as EmbedUrl
                        FROM Episodes e
                        WHERE e.movie_id = ' . $movie->MovieID . '
                        AND e.server_id = s.server_id
                        ORDER BY e.episode_number
                        FOR JSON PATH
                    ) AS NVARCHAR(MAX))) as Episodes')
                ])
                ->whereExists(function ($query) use ($movie) {
                    $query->select(DB::raw(1))
                          ->from('Episodes as e')
                          ->whereRaw('e.server_id = s.server_id')
                          ->where('e.movie_id', $movie->MovieID);
                })
                ->orderByDesc('s.priority')
                ->get();

            // Format server episodes
            $formattedServers = $serverEpisodes->map(function ($server) {
                return [
                    'serverID' => $server->ServerID,
                    'serverName' => $server->ServerName,
                    'serverType' => $server->ServerType,
                    'episodes' => json_decode($server->Episodes)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => array_merge(
                    (array)$movie,
                    ['servers' => $formattedServers]
                )
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }

    public function checkStatus(Request $request, $movieId)
    {
        try {
            $userId = $request->user()->id;

            $status = DB::query()
                ->selectRaw('
                    (SELECT COUNT(*) FROM WatchLater WHERE UserID = ? AND MovieID = ?) as inWatchLater,
                    (SELECT COUNT(*) FROM MovieRatings WHERE UserID = ? AND MovieID = ? AND RatingType = \'like\') as isLiked
                ', [$userId, $movieId, $userId, $movieId])
                ->first();

            return response()->json([
                'inWatchLater' => $status->inWatchLater > 0,
                'isLiked' => $status->isLiked > 0
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }
}
