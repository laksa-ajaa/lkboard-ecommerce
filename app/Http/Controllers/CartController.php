<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Get or create cart for the authenticated user.
     */
    private function getOrCreateCart(): Cart
    {
        $user = Auth::user();
        
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );
    }

    /**
     * Display the cart page.
     */
    public function index(): View
    {
        $cart = $this->getOrCreateCart();
        $cartItems = $cart->items()->with(['product', 'variant'])->get();
        
        // Format cart items for view
        $formattedItems = $cartItems->map(function ($item) {
            $product = $item->product;
            $variant = $item->variant;
            
            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name,
                'variant' => $variant?->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'stock' => $variant?->stock ?? $product->stock,
                'image' => $product->thumbnail,
                'product_url' => route('products.show', $product->slug),
            ];
        })->toArray();
        
        // Calculate totals
        $subtotal = 0;
        foreach ($formattedItems as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }
        
        // Shipping will be calculated at checkout
        $total = $subtotal;

        return view('pages.cart.index', [
            'cartItems' => $formattedItems,
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // If product has variants, variant_id is required
        if ($product->variants()->count() > 0 && !$validated['variant_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih varian terlebih dahulu.',
            ], 422);
        }

        // Get variant if exists
        $variant = null;
        if ($validated['variant_id']) {
            $variant = ProductVariant::findOrFail($validated['variant_id']);
            
            // Check stock
            if ($variant->stock < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $variant->stock,
                ], 422);
            }
            
            $price = $variant->price ?? $product->price;
            $stock = $variant->stock;
        } else {
            // Check product stock
            if ($product->stock < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $product->stock,
                ], 422);
            }
            
            $price = $product->price;
            $stock = $product->stock;
        }

        // Get or create cart
        $cart = $this->getOrCreateCart();

        // Check if item already exists in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $validated['variant_id'])
            ->first();
        
        if ($existingItem) {
            // Update quantity if item exists
            $newQuantity = $existingItem->quantity + $validated['quantity'];
            
            if ($newQuantity > $stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $stock,
                ], 422);
            }
            
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $validated['quantity'],
                'price' => $price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang!',
        ]);
    }

    /**
     * Update item quantity in cart.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['product', 'variant'])
            ->firstOrFail();

        // Get current stock
        $product = $cartItem->product;
        $variant = $cartItem->variant;
        $stock = $variant?->stock ?? $product->stock;

        // Check stock
        if ($validated['quantity'] > $stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $stock,
            ], 422);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        // Calculate totals
        $cart = $cartItem->cart;
        $cartItems = $cart->items;
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->price * $item->quantity);
        }
        // Shipping will be calculated at checkout
        $total = $subtotal;

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total' => $total,
            'itemTotal' => $cartItem->price * $cartItem->quantity,
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(int $id): JsonResponse
    {
        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->firstOrFail();

        $cart = $cartItem->cart;
        $cartItem->delete();

        // Calculate totals
        $cartItems = $cart->items;
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->price * $item->quantity);
        }
        // Shipping will be calculated at checkout
        $total = $subtotal;

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total' => $total,
            'itemCount' => $cartItems->count(),
        ]);
    }

    /**
     * Clear all items from cart.
     */
    public function clear(): JsonResponse
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan.',
        ]);
    }
}
