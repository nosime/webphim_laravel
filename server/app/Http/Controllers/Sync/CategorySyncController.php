<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CategorySyncController extends Controller
{
    public function getCategories()
    {
        try {
            $categories = Category::where('is_active', 1)
                ->select('category_id', 'name', 'slug', 'description')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch(\Exception $e) {
            Log::error('Error getting categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategoryById($id)
    {
        try {
            $category = Category::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        } catch(\Exception $e) {
            Log::error('Error getting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
    }

    public function syncCategories()
{
    try {
        $response = Http::withOptions([
            'verify' => false,
        ])->get('https://ophim1.com/the-loai');
        $categories = $response->json();

        foreach($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => "Thể loại {$category['name']}",
                    'display_order' => 0,
                    'is_active' => true
                ]
            );
        }

        $result = Category::orderBy('display_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories synced successfully',
            'data' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error syncing categories',
            'error' => $e->getMessage()
        ]);
    }
}
}
