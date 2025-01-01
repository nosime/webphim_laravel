<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function getCategories()
    {
        try {
            $categories = Category::where('is_active', 1)
                ->select([
                    'category_id as CategoryID',
                    'name as Name',
                    'slug as Slug',
                    'description as Description',
                    'display_order as DisplayOrder'
                ])
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
                'error' => $e->getMessage() || 'Database error'
            ], 500);
        }
    }

    public function getCategoryById($id)
    {
        try {
            $category = Category::select([
                    'category_id as CategoryID',
                    'name as Name',
                    'slug as Slug',
                    'description as Description',
                    'display_order as DisplayOrder',
                    'is_active as IsActive'
                ])
                ->where('category_id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $category
            ]);

        } catch(\Exception $e) {
            Log::error('Error getting category by ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
    }
}
