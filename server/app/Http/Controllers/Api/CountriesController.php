<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function getCountries()
    {
        try {
            $countries = Country::where('is_active', 1)
                ->select([
                    'country_id as CountryID',
                    'name as Name',
                    'slug as Slug',
                    'code as Code'
                ])
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
                'error' => $e->getMessage() || 'Database error'
            ], 500);
        }
    }

    public function getCountryById($id)
    {
        try {
            $country = Country::select([
                    'country_id as CountryID',
                    'name as Name',
                    'slug as Slug',
                    'code as Code',
                    'is_active as IsActive'
                ])
                ->findOrFail($id);

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
}
