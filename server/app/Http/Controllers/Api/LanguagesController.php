<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LanguagesController extends Controller
{
    public function getUniqueLanguages()
    {
        try {
            $languages = DB::table('Movies')
                ->select([
                    'Language as name',
                    DB::raw('LOWER(Language) as slug')
                ])
                ->whereNotNull('Language')
                ->where('Language', '!=', '')
                ->distinct()
                ->orderBy('Language')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $languages
            ]);

        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'error' => $error->getMessage()
            ], 500);
        }
    }
}
