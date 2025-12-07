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

        $searchQuery = request('q');

        $products = Product::with(['category'])
            ->where('status', 'active')
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('short_description', 'like', '%' . $searchQuery . '%')
                        ->orWhere('description', 'like', '%' . $searchQuery . '%')
                        ->orWhereHas('category', function ($categoryQuery) use ($searchQuery) {
                            $categoryQuery->where('name', 'like', '%' . $searchQuery . '%');
                        });
                });
            })
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

        return view('pages.products.index', compact('categories', 'products', 'minPrice', 'maxPrice', 'searchQuery'));
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
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        $products = Product::with(['category'])
            ->where('status', 'active')
            ->whereHas('category', function ($q) use ($slug) {
                $q->where('slug', $slug);
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

        // Set category filter in request for view
        request()->merge(['category' => $slug]);

        return view('pages.products.index', compact('categories', 'products', 'minPrice', 'maxPrice', 'category'));
    }

    /**
     * Display the specified product.
     */
    public function show(string $product): View
    {
        $productModel = Product::with(['category', 'variants'])
            ->where('slug', $product)
            ->where('status', 'active')
            ->firstOrFail();

        // Calculate discount if exists
        $productModel->original_price = $productModel->compare_at_price;
        $productModel->discount = $productModel->compare_at_price && $productModel->compare_at_price > $productModel->price
            ? (int) round((($productModel->compare_at_price - $productModel->price) / $productModel->compare_at_price) * 100)
            : null;
        $productModel->image_url = $productModel->thumbnail;

        // Transform variants
        $productModel->variants->transform(function ($variant) use ($productModel) {
            // Use variant price if exists, otherwise use product price
            $variantPrice = $variant->price ?? $productModel->price;
            $variantComparePrice = $variant->compare_at_price ?? $productModel->compare_at_price;
            
            $variant->original_price = $variantComparePrice;
            $variant->discount = $variantComparePrice && $variantComparePrice > $variantPrice
                ? (int) round((($variantComparePrice - $variantPrice) / $variantComparePrice) * 100)
                : null;
            return $variant;
        });

        // Get similar products (same category, exclude current product)
        $similarProducts = Product::with(['category'])
            ->where('status', 'active')
            ->where('category_id', $productModel->category_id)
            ->where('id', '!=', $productModel->id)
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($product) {
                $product->original_price = $product->compare_at_price;
                $product->discount = $product->compare_at_price && $product->compare_at_price > $product->price
                    ? (int) round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100)
                    : null;
                $product->image_url = $product->thumbnail;
                return $product;
            });

        return view('pages.products.show', compact('productModel', 'similarProducts'));
    }
}

