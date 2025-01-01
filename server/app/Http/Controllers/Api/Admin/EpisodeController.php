<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EpisodeController extends Controller
{
    public function getEpisodes($movieId)
    {
        if (!$movieId) {
            return response()->json([
                'success' => false,
                'message' => 'Movie ID is required',
            ], 400);
        }

        try {
            $episodes = DB::table('episodes as e')
                ->select('e.episode_id as EpisodeID', 'e.name as Name', 'e.slug as Slug', 'e.file_name as FileName', 'e.episode_number as EpisodeNumber', 'e.embed_url as EmbedUrl', 's.name as ServerName')
                ->leftJoin('servers as s', 'e.server_id', '=', 's.server_id')
                ->where('e.movie_id', $movieId)
                ->orderBy('e.episode_number', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $episodes,
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 500);
        }
    }

    public function addEpisode(Request $request)
    {
        $validated = $request->validate([
            'MovieID' => 'required|integer',
            'Name' => 'required|string',
            'Slug' => 'required|string',
            'EpisodeNumber' => 'required|integer',
            'EmbedUrl' => 'nullable|string',
            'ServerID' => 'required|integer',
        ]);

        try {
            $episodeId = DB::table('episodes')->insertGetId([
                'movie_id' => $validated['MovieID'],
                'server_id' => $validated['ServerID'],
                'name' => $validated['Name'],
                'slug' => $validated['Slug'],
                'file_name' => $request->input('FileName'),
                'episode_number' => $validated['EpisodeNumber'],
                'embed_url' => $validated['EmbedUrl'],
                'created_at' => now(),
            ]);

            $this->updateEpisodeCount($validated['MovieID']);

            return response()->json([
                'success' => true,
                'data' => ['EpisodeID' => $episodeId],
                'message' => 'Episode added successfully',
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 500);
        }
    }

    public function updateEpisode($id, Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string',
            'Slug' => 'required|string',
            'EpisodeNumber' => 'required|integer',
            'EmbedUrl' => 'nullable|string',
            'ServerID' => 'required|integer',
            'MovieID' => 'required|integer',
        ]);

        try {
            DB::table('episodes')
                ->where('episode_id', $id)
                ->update([
                    'movie_id' => $validated['MovieID'],
                    'server_id' => $validated['ServerID'],
                    'name' => $validated['Name'],
                    'slug' => $validated['Slug'],
                    'file_name' => $request->input('FileName'),
                    'episode_number' => $validated['EpisodeNumber'],
                    'embed_url' => $validated['EmbedUrl'],
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Episode updated successfully',
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 500);
        }
    }

    public function deleteEpisode($id)
    {
        try {
            $episodeResult = DB::table('episodes')
                ->where('episode_id', $id)
                ->first();

            if (!$episodeResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Episode not found',
                ], 404);
            }

            $movieId = $episodeResult->movie_id;

            DB::table('view_history')
                ->where('episode_id', $id)
                ->delete();

            DB::table('episodes')
                ->where('episode_id', $id)
                ->delete();

            $this->updateEpisodeCount($movieId);

            return response()->json([
                'success' => true,
                'message' => 'Episode deleted successfully',
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
            ], 500);
        }
    }

    private function updateEpisodeCount($movieId)
    {
        try {
            $totalEpisodes = DB::table('episodes')
                ->where('movie_id', $movieId)
                ->count();

            DB::table('movies')
                ->where('movie_id', $movieId)
                ->update(['episode_current' => $totalEpisodes]);
        } catch (\Exception $error) {
            throw $error;
        }
    }
}
