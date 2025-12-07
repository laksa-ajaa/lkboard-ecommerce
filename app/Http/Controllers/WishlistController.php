<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page.
     */
    public function index(): View
    {
        // Get all wishlist items first to determine categories (only active products)
        $allWishlistItems = Auth::user()
            ->wishlists()
            ->with('product.category')
            ->whereHas('product', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        // Get unique categories from wishlist items (filter null products)
        $categoryIds = $allWishlistItems
            ->filter(fn($wishlist) => $wishlist->product && $wishlist->product->category_id)
            ->pluck('product.category_id')
            ->filter()
            ->unique()
            ->toArray();

        // Get all active categories
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($category) use ($allWishlistItems) {
                $category->products_count = $allWishlistItems->filter(function ($wishlist) use ($category) {
                    return $wishlist->product && $wishlist->product->category_id === $category->id;
                })->count();
                return $category;
            });

        // Get wishlist items with filters (only active products)
        $wishlistsQuery = Auth::user()
            ->wishlists()
            ->with(['product.category'])
            ->whereHas('product', function ($q) {
                $q->where('status', 'active');
            });

        // Apply category filter
        if (request('category')) {
            $wishlistsQuery->whereHas('product.category', function ($q) {
                $q->where('slug', request('category'));
            });
        }

        // Get products for price filtering
        $wishlistsQuery->whereHas('product', function ($query) {
            // Apply price filter
            if (request('min_price')) {
                $query->where('price', '>=', request('min_price'));
            }
            if (request('max_price')) {
                $query->where('price', '<=', request('max_price'));
            }
            // Apply stock filter
            if (request('in_stock') === '1') {
                $query->where('stock', '>', 0);
            }
        });

        // Get all wishlist items to apply sorting
        $wishlists = $wishlistsQuery->get();

        // Transform and sort products (filter out null products)
        $wishlistItems = $wishlists
            ->filter(fn($wishlist) => $wishlist->product !== null)
            ->map(function ($wishlist) {
                $product = $wishlist->product;
                
                return [
                    'id' => $wishlist->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'original_price' => $product->compare_at_price,
                    'image' => $product->thumbnail,
                    'category' => $product->category?->name,
                    'category_slug' => $product->category?->slug,
                    'stock' => $product->stock,
                    'created_at' => $wishlist->created_at,
                    'product_url' => route('products.show', $product->slug),
                ];
            });

        // Apply sorting
        $sort = request('sort');
        if ($sort === 'price_asc') {
            $wishlistItems = $wishlistItems->sortBy('price')->values();
        } elseif ($sort === 'price_desc') {
            $wishlistItems = $wishlistItems->sortByDesc('price')->values();
        } elseif ($sort === 'name_asc') {
            $wishlistItems = $wishlistItems->sortBy('name')->values();
        } elseif ($sort === 'name_desc') {
            $wishlistItems = $wishlistItems->sortByDesc('name')->values();
        } else {
            // Default: sort by latest (newest first)
            $wishlistItems = $wishlistItems->sortByDesc('created_at')->values();
        }

        // Get price range for filter (only active products)
        $allWishlistPrices = Auth::user()
            ->wishlists()
            ->with('product')
            ->whereHas('product', function ($q) {
                $q->where('status', 'active');
            })
            ->get()
            ->filter(fn($wishlist) => $wishlist->product !== null)
            ->pluck('product.price')
            ->filter();

        $minPrice = $allWishlistPrices->min() ?? 0;
        $maxPrice = $allWishlistPrices->max() ?? 10000000;

        // Paginate manually
        $currentPage = request()->get('page', 1);
        $perPage = 12;
        $items = $wishlistItems->all();
        $total = count($items);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = array_slice($items, $offset, $perPage);

        // Create paginator manually
        $wishlistItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('pages.wishlist.index', [
            'wishlistItems' => $wishlistItems,
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    /**
     * Toggle wishlist item (add or remove).
     */
    public function toggle(): JsonResponse
    {
        $request = request();
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = Auth::id();
        $productId = $request->input('product_id');

        // Check if wishlist already exists
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            // Remove from wishlist
            $wishlist->delete();
            
            return response()->json([
                'success' => true,
                'in_wishlist' => false,
                'message' => 'Produk berhasil dihapus dari wishlist.',
            ]);
        } else {
            // Add to wishlist
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);

            return response()->json([
                'success' => true,
                'in_wishlist' => true,
                'message' => 'Produk berhasil ditambahkan ke wishlist.',
            ]);
        }
    }

    /**
     * Remove item from wishlist.
     */
    public function destroy(int $id): JsonResponse
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari wishlist.',
        ]);
    }
}
