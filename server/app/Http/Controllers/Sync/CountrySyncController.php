<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountrySyncController extends Controller
{
    public function getCountries()
    {
        try {
            $countries = Country::where('is_active', 1)
                ->select('country_id', 'name', 'slug', 'code')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $countries
            ]);
        } catch(\Exception $e) {
            Log::error('Error getting countries: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCountryById($id)
    {
        try {
            $country = Country::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $country
            ]);
        } catch(\Exception $e) {
            Log::error('Error getting country: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Country not found'
            ], 404);
        }
    }

    public function syncCountries()
    {
        try {
            $response = Http::withOptions([
                'verify' => false, // Bỏ qua xác thực SSL
            ])->get('https://ophim1.com/quoc-gia');
            $countries = $response->json();

            foreach($countries as $country) {
                Country::updateOrCreate(
                    ['slug' => $country['slug']],
                    [
                        'name' => $country['name'],
                        'code' => strtoupper(substr($country['slug'], 0, 10)),
                        'is_active' => true
                    ]
                );
            }

            $result = Country::orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Countries synced successfully',
                'data' => $result
            ]);

        } catch(\Exception $e) {
            Log::error('Error syncing countries: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
