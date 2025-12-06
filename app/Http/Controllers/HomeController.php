<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index(): View
    {
        $popularCategories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->take(6)
            ->get();

        $featuredProducts = Product::with(['category'])
            ->where('status', 'active')
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($product) {
                $product->original_price = $product->compare_at_price;
                $product->discount = $product->compare_at_price && $product->compare_at_price > $product->price
                    ? (int) round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100)
                    : null;
                $product->image_url = $product->thumbnail;
                return $product;
            });

        return view('pages.landing', compact('popularCategories', 'featuredProducts'));
    }
}

