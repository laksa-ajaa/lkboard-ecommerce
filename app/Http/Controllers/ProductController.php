<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $products = Product::with(['category'])
            ->where('status', 'active')
            ->when(request('category'), function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->where('slug', request('category'));
                });
            })
            ->when(request('min_price'), function ($query) {
                $query->where('price', '>=', request('min_price'));
            })
            ->when(request('max_price'), function ($query) {
                $query->where('price', '<=', request('max_price'));
            })
            ->when(request('in_stock') === '1', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->when(request('sort'), function ($query) {
                $sort = request('sort');
                if ($sort === 'price_asc') {
                    $query->orderBy('price', 'asc');
                } elseif ($sort === 'price_desc') {
                    $query->orderBy('price', 'desc');
                } elseif ($sort === 'name_asc') {
                    $query->orderBy('name', 'asc');
                } elseif ($sort === 'name_desc') {
                    $query->orderBy('name', 'desc');
                } else {
                    $query->latest();
                }
            }, function ($query) {
                $query->latest();
            })
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function ($product) {
            $product->original_price = $product->compare_at_price;
            $product->discount = $product->compare_at_price && $product->compare_at_price > $product->price
                ? (int) round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100)
                : null;
            $product->image_url = $product->thumbnail;
            return $product;
        });

        $minPrice = Product::where('status', 'active')->min('price') ?? 0;
        $maxPrice = Product::where('status', 'active')->max('price') ?? 10000000;

        return view('pages.products.index', compact('categories', 'products', 'minPrice', 'maxPrice'));
    }

    /**
     * Display search results.
     */
    public function search(): View
    {
        return view('pages.products.search');
    }

    /**
     * Display products by category.
     */
    public function category(string $slug): View
    {
        return view('pages.products.category', compact('slug'));
    }

    /**
     * Display the specified product.
     */
    public function show(string $product): View
    {
        return view('pages.products.show', compact('product'));
    }
}

