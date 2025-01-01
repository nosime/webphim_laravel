<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddMovieController extends Controller
{
    public function addMovie(Request $request)
    {
        try {
            $validated = $request->validate([
                'Name' => 'required|string',
                'OriginName' => 'required|string',
                'Description' => 'required|string',
                'Type' => 'required|string',
                'Status' => 'required|string',
                'ThumbUrl' => 'required|string',
                'PosterUrl' => 'required|string',
                'TrailerUrl' => 'nullable|string',
                'Year' => 'required|integer',
                'Language' => 'required|string',
                'Actors' => 'nullable|string',
                'Directors' => 'nullable|string',
                'Categories' => 'nullable|array',
                'Countries' => 'nullable|array',
                'Quality' => 'required|string'
            ]);

            DB::beginTransaction();

            try {
                // Create slug
                $slug = $this->createSlug($validated['Name']);

                // Check if slug already exists
                $existingMovie = DB::table('movies')
                    ->where('slug', $slug)
                    ->first();

                if ($existingMovie) {
                    throw new \Exception('Phim đã tồn tại');
                }

                // Insert movie
                $movieId = DB::table('movies')->insertGetId([
                    'name' => $validated['Name'],
                    'origin_name' => $validated['OriginName'],
                    'slug' => $slug,
                    'description' => $validated['Description'],
                    'type' => $validated['Type'],
                    'status' => $validated['Status'],
                    'thumb_url' => $validated['ThumbUrl'],
                    'poster_url' => $validated['PosterUrl'],
                    'trailer_url' => $validated['TrailerUrl'] ?? null,
                    'year' => $validated['Year'],
                    'language' => $validated['Language'],
                    'quality' => $validated['Quality'],
                    'actors' => $validated['Actors'] ?? '',
                    'directors' => $validated['Directors'] ?? '',
                    'is_visible' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Add categories
                if ($validated['Categories']) {
                    foreach ($validated['Categories'] as $categorySlug) {
                        $categoryId = DB::table('categories')
                            ->where('slug', $categorySlug)
                            ->value('category_id');

                        if ($categoryId) {
                            DB::table('movie_categories')->insert([
                                'movie_id' => $movieId,
                                'category_id' => $categoryId
                            ]);
                        }
                    }
                }

                // Add country
                if ($validated['Countries']) {
                    $countryId = DB::table('countries')
                        ->where('slug', $validated['Countries'][0])
                        ->value('country_id');

                    if ($countryId) {
                        DB::table('movie_countries')->insert([
                            'movie_id' => $movieId,
                            'country_id' => $countryId
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Thêm phim thành công',
                    'data' => [
                        'MovieID' => (string)$movieId,
                        'Slug' => $slug
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage() ?? 'Có lỗi xảy ra khi thêm phim'
            ], 500);
        }
    }

    private function createSlug($text)
    {
        return Str::slug($text, '-');
    }
}
