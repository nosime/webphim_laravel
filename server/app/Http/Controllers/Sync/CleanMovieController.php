<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CleanMovieController extends Controller
{
    public function cleanAllMovieData()
    {
        try {
            DB::beginTransaction();

            $tables = [
                'episodes',
                'servers',
                'movie_categories',
                'movie_countries',
                'view_history',
                'watch_later',
                'movie_ratings',
                'movies'
            ];

            $results = [
                'success' => [],
                'failed' => []
            ];

            foreach ($tables as $table) {
                try {
                    $beforeCount = DB::table($table)->count();
                    DB::table($table)->delete();
                    $afterCount = DB::table($table)->count();

                    $results['success'][] = [
                        'table' => $table,
                        'deletedRecords' => $beforeCount - $afterCount,
                        'remainingRecords' => $afterCount
                    ];
                } catch (Exception $e) {
                    Log::error("Error cleaning {$table}: " . $e->getMessage());
                    $results['failed'][] = [
                        'table' => $table,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::statement('DBCC CHECKIDENT (movies, RESEED, 0)');
            DB::statement('DBCC CHECKIDENT (episodes, RESEED, 0)');
            DB::statement('DBCC CHECKIDENT (servers, RESEED, 0)');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Database cleanup completed',
                'results' => $results
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in cleanAllMovieData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
