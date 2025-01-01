<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Category;
use App\Models\Country;
use App\Models\Server;
use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MovieSyncController extends Controller
{
    private function fetchMovieDetails($slug)
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Bỏ qua xác thực SSL
            ])->get("https://ophim1.com/phim/{$slug}");
            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error fetching movie: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateUrl($url)
    {
        if (empty($url)) return null;

        $url = trim($url);

        // Check if URL is a base64 image
        if (strpos($url, 'data:image') === 0) {
            $pathInfo = pathinfo($this->extractFilenameFromUrl($url));
            $baseUrl = "https://img.ophim.live/uploads/movies/";
            return $baseUrl . $pathInfo['filename'] . '-poster.jpg';
        }

        return $url;
    }

    private function extractFilenameFromUrl($url)
    {
        // Extract filename from URL or use a hash if not found
        $parts = parse_url($url);
        if (isset($parts['path'])) {
            return basename($parts['path']);
        }
        return md5($url); // Fallback to hash if no filename found
    }

    private function insertMovie($movieData)
    {
        try {
            // Parse episode numbers
            $episodeCurrent = null;
            if (isset($movieData['episode_current'])) {
                preg_match('/\d+/', $movieData['episode_current'], $matches);
                $episodeCurrent = isset($matches[0]) ? (int)$matches[0] : null;
            }

            $episodeTotal = null;
            if (isset($movieData['episode_total'])) {
                preg_match('/\d+/', $movieData['episode_total'], $matches);
                $episodeTotal = isset($matches[0]) ? (int)$matches[0] : null;
            }

            // Process data
            $actors = is_array($movieData['actor']) ? implode(', ', $movieData['actor']) : '';
            $directors = is_array($movieData['director']) ? implode(', ', $movieData['director']) : '';

            // Type mapping
            $movieType = $movieData['type'] === 'series' ? 'series' : 'single';

            // Clean URLs before inserting
            $thumbUrl = $this->validateUrl($movieData['thumb_url']);
            $posterUrl = $this->validateUrl($movieData['poster_url']);

            // If poster URL is empty or invalid, use thumb URL as fallback
            if (empty($posterUrl) || strpos($posterUrl, 'data:image') === 0) {
                $posterUrl = $thumbUrl;
            }

            $movie = Movie::updateOrCreate(
                ['slug' => $movieData['slug']],
                [
                    'name' => $movieData['name'],
                    'origin_name' => $movieData['origin_name'],
                    'description' => $movieData['content'],
                    'type' => $movieType,
                    'status' => $movieData['status'] ?? 'ongoing',
                    'thumb_url' => $thumbUrl,
                    'poster_url' => $posterUrl,
                    'trailer_url' => $this->validateUrl($movieData['trailer_url']),
                    'episode_current' => $episodeCurrent,
                    'episode_total' => $episodeTotal,
                    'quality' => $movieData['quality'],
                    'language' => $movieData['lang'],
                    'year' => $movieData['year'],
                    'actors' => $actors,
                    'directors' => $directors,
                    'is_copyright' => $movieData['is_copyright'] ?? false,
                    'is_subtitled' => $movieData['sub_docquyen'] ?? false,
                    'views' => $movieData['view'] ?? 0,
                    'tmdb_id' => $movieData['tmdb']['id'] ?? null,
                    'imdb_id' => $movieData['imdb']['id'] ?? null,
                    'tmdb_rating' => $movieData['tmdb']['vote_average'] ?? 0,
                    'tmdb_vote_count' => $movieData['tmdb']['vote_count'] ?? 0
                ]
            );

            return $movie;

        } catch (\Exception $e) {
            Log::error("Error inserting movie: " . $e->getMessage());
            throw $e;
        }
    }

    private function insertMovieRelations($movie, $movieData)
    {
        try {
            // Sync categories
            if (!empty($movieData['category'])) {
                $categoryIds = [];
                foreach ($movieData['category'] as $category) {
                    $categoryModel = Category::where('slug', $category['slug'])->first();
                    if ($categoryModel) {
                        $categoryIds[] = $categoryModel->category_id;
                    }
                }
                $movie->categories()->sync($categoryIds);
            }

            // Sync countries
            if (!empty($movieData['country'])) {
                $countryIds = [];
                foreach ($movieData['country'] as $country) {
                    $countryModel = Country::where('slug', $country['slug'])->first();
                    if ($countryModel) {
                        $countryIds[] = $countryModel->country_id;
                    }
                }
                $movie->countries()->sync($countryIds);
            }

        } catch (\Exception $e) {
            Log::error("Error inserting relations: " . $e->getMessage());
            throw $e;
        }
    }

    private function insertServerAndEpisodes($movie, $episodes)
    {
        try {
            foreach ($episodes as $episodeGroup) {
                $server = Server::firstOrCreate(
                    ['name' => $episodeGroup['server_name']],
                    ['type' => 'embed', 'priority' => 1]
                );

                // Delete existing episodes
                Episode::where('movie_id', $movie->movie_id)
                      ->where('server_id', $server->server_id)
                      ->delete();

                // Insert new episodes
                foreach ($episodeGroup['server_data'] as $episode) {
                    Episode::create([
                        'movie_id' => $movie->movie_id,
                        'server_id' => $server->server_id,
                        'name' => $episode['name'],
                        'slug' => $episode['slug'],
                        'file_name' => $episode['filename'],
                        'episode_number' => (int)$episode['name'],
                        'video_url' => $episode['link_m3u8'],
                        'embed_url' => $episode['link_embed'],
                        'is_processed' => true
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error inserting episodes: " . $e->getMessage());
            throw $e;
        }
    }

    public function syncMovieBySlug(Request $request, $slug)
    {
        try {
            if (empty($slug)) {
                throw new \Exception("Movie slug is required");
            }

            $movieData = $this->fetchMovieDetails($slug);

            if (empty($movieData['movie'])) {
                throw new \Exception("Movie not found");
            }

            $movie = $this->insertMovie($movieData['movie']);
            $this->insertMovieRelations($movie, $movieData['movie']);

            if (!empty($movieData['episodes'])) {
                $this->insertServerAndEpisodes($movie, $movieData['episodes']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Movie synced successfully',
                'data' => [
                    'movieId' => $movie->movie_id,
                    'slug' => $slug,
                    'episodes' => count($movieData['episodes'] ?? [])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function fetchMovieList($page = 1)
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->get("https://ophim1.com/danh-sach/phim-moi-cap-nhat", [
                'page' => $page
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error("Error fetching movie list page {$page}: " . $e->getMessage());
            throw $e;
        }
    }

    private function consoleLog($message, $type = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $output = "[{$timestamp}] {$type}: {$message}\n";
        echo $output;
        ob_flush();
        flush();

        // Also log to Laravel log
        Log::$type($message);
    }

    public function syncAllMovies(Request $request)
    {
        // Set unlimited execution time
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        // Enable output buffering
        if (ob_get_level() == 0) ob_start();

        $startPage = $request->input('startPage', 1);
        $endPage = $request->input('endPage', $startPage);
        $chunkSize = 10;

        $results = [
            'success' => [],
            'failed' => [],
            'processedPages' => 0
        ];

        try {
            $this->consoleLog("Starting sync process from page {$startPage} to {$endPage}");

            for ($currentChunkStart = $startPage; $currentChunkStart <= $endPage; $currentChunkStart += $chunkSize) {
                $chunkEnd = min($currentChunkStart + $chunkSize - 1, $endPage);
                $this->consoleLog("Processing chunk {$currentChunkStart} to {$chunkEnd}");

                for ($page = $currentChunkStart; $page <= $chunkEnd; $page++) {
                    try {
                        $this->consoleLog("Fetching page {$page}/{$endPage}");
                        $pageData = $this->fetchMovieList($page);

                        foreach ($pageData['items'] as $item) {
                            try {
                                $this->consoleLog("Syncing: {$item['name']} ({$item['slug']})");

                                // Add small delay between requests to prevent timeouts
                                usleep(500000);

                                $movieData = $this->fetchMovieDetails($item['slug']);

                                if (empty($movieData['movie'])) {
                                    $this->consoleLog("Skipping {$item['slug']}: No movie data found", 'warning');
                                    continue;
                                }

                                DB::beginTransaction();

                                $movie = $this->insertMovie($movieData['movie']);
                                $this->insertMovieRelations($movie, $movieData['movie']);

                                if (!empty($movieData['episodes'])) {
                                    $this->insertServerAndEpisodes($movie, $movieData['episodes']);
                                }

                                DB::commit();

                                $results['success'][] = [
                                    'slug' => $item['slug'],
                                    'name' => $item['name'],
                                    'id' => $movie->movie_id
                                ];

                                $this->consoleLog("Success: {$item['name']} - Total: " . count($results['success']));

                            } catch (\Exception $e) {
                                DB::rollBack();
                                $this->consoleLog("Error syncing {$item['slug']}: " . $e->getMessage(), 'error');
                                $results['failed'][] = [
                                    'slug' => $item['slug'],
                                    'name' => $item['name'],
                                    'error' => $e->getMessage()
                                ];
                                sleep(2);
                            }
                        }

                        $results['processedPages']++;
                        $this->consoleLog("Page complete: {$page} - Total pages: {$results['processedPages']}");

                    } catch (\Exception $e) {
                        $this->consoleLog("Error on page {$page}: " . $e->getMessage(), 'error');
                        sleep(5);
                        continue;
                    }
                }

                $this->consoleLog("Chunk complete. Waiting 10 seconds...");
                sleep(10);
            }

            $this->consoleLog("Sync complete! Success: " . count($results['success']) . ", Failed: " . count($results['failed']));

            // ...existing response code...

        } catch (\Exception $e) {
            $this->consoleLog("Fatal error: " . $e->getMessage(), 'error');
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
