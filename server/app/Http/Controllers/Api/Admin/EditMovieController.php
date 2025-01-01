<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditMovieController extends Controller
{
    public function updateMovie($id, Request $request)
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
                'Quality' => 'required|string',
                'Actors' => 'nullable|string',
                'Directors' => 'nullable|string',
                'Categories' => 'nullable|array',
                'Countries' => 'nullable|array'
            ]);

            DB::beginTransaction();

            try {
                // Update movie details
                DB::table('movies')
                    ->where('movie_id', $id)
                    ->update([
                        'name' => $validated['Name'],
                        'origin_name' => $validated['OriginName'],
                        'description' => $validated['Description'],
                        'type' => $validated['Type'],
                        'status' => $validated['Status'],
                        'thumb_url' => $validated['ThumbUrl'],
                        'poster_url' => $validated['PosterUrl'],
                        'trailer_url' => $validated['TrailerUrl'],
                        'year' => $validated['Year'],
                        'language' => $validated['Language'],
                        'quality' => $validated['Quality'],
                        'actors' => $validated['Actors'],
                        'directors' => $validated['Directors'],
                        'updated_at' => now()
                    ]);

                // Update categories
                DB::table('movie_categories')
                    ->where('movie_id', $id)
                    ->delete();

                if ($validated['Categories']) {
                    $categories = [];
                    foreach ($validated['Categories'] as $categorySlug) {
                        $categoryId = DB::table('categories')
                            ->where('slug', $categorySlug)
                            ->value('category_id');

                        if ($categoryId) {
                            $categories[] = [
                                'movie_id' => $id,
                                'category_id' => $categoryId
                            ];
                        }
                    }
                    DB::table('movie_categories')->insert($categories);
                }

                // Update country
                DB::table('movie_countries')
                    ->where('movie_id', $id)
                    ->delete();

                if ($validated['Countries']) {
                    $countryId = DB::table('countries')
                        ->where('slug', $validated['Countries'][0])
                        ->value('country_id');

                    if ($countryId) {
                        DB::table('movie_countries')->insert([
                            'movie_id' => $id,
                            'country_id' => $countryId
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật phim thành công'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage() ?? 'Có lỗi xảy ra khi cập nhật phim'
            ], 500);
        }
    }
}
