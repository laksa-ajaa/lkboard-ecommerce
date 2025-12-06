<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Get search suggestions.
     */
    public function suggestions(): JsonResponse
    {
        $query = request('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'products' => [],
                'categories' => [],
            ]);
        }

        // Get product suggestions (limit 5)
        $products = Product::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('short_description', 'like', '%' . $query . '%');
            })
            ->with('category')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'category' => $product->category?->name,
                    'thumbnail' => $product->thumbnail,
                ];
            });

        // Get category suggestions (limit 3)
        $categories = Category::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->take(3)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ];
            });

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}

