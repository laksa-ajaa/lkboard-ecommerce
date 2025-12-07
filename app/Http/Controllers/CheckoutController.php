<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(): View
    {
        // Redirect to login if not authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('redirect', route('checkout.index'));
        }

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        // If cart is empty, redirect to cart page
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        $cartItems = $cart->items()->with(['product', 'variant'])->get();

        // Get selected cart item IDs from request query parameter (passed from cart page)
        $selectedItemIds = [];
        if (request()->has('items')) {
            $itemsParam = request()->get('items');
            if (is_string($itemsParam)) {
                $decoded = json_decode(urldecode($itemsParam), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $selectedItemIds = $decoded;
                }
            } elseif (is_array($itemsParam)) {
                $selectedItemIds = $itemsParam;
            }
        }

        // Filter cart items if selected items are provided
        if (!empty($selectedItemIds)) {
            $cartItems = $cartItems->filter(function ($item) use ($selectedItemIds) {
                return in_array($item->id, $selectedItemIds);
            });
        }

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

        // If no items after filtering, redirect back to cart
        if (empty($formattedItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Tidak ada item yang dipilih untuk checkout.');
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($formattedItems as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        // Default shipping cost (can be calculated based on address later)
        $shippingCost = 15000; // Default shipping cost
        $total = $subtotal + $shippingCost;

        // Load saved shipping address
        $savedAddress = $user->shippingAddresses()
            ->where('is_default', true)
            ->first();

        return view('pages.checkout.index', [
            'cartItems' => $formattedItems,
            'subtotal' => $subtotal,
            'shippingCost' => $shippingCost,
            'total' => $total,
            'user' => $user,
            'savedAddress' => $savedAddress,
        ]);
    }

    /**
     * Update item quantity in checkout.
     */
    public function updateQuantity(Request $request, int $id): JsonResponse
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

        // Recalculate totals
        $cart = $cartItem->cart;
        $cartItems = $cart->items()->with(['product', 'variant'])->get();
        
        // Get selected item IDs from session or query
        $selectedItemIds = [];
        if (request()->has('items')) {
            $itemsParam = request()->get('items');
            if (is_string($itemsParam)) {
                $decoded = json_decode(urldecode($itemsParam), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $selectedItemIds = $decoded;
                }
            }
        }

        // Filter if selected items exist
        if (!empty($selectedItemIds)) {
            $cartItems = $cartItems->filter(function ($item) use ($selectedItemIds) {
                return in_array($item->id, $selectedItemIds);
            });
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->price * $item->quantity);
        }

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'itemTotal' => $cartItem->price * $cartItem->quantity,
            'quantity' => $cartItem->quantity,
        ]);
    }

    /**
     * Save shipping address.
     */
    public function saveAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
        ]);

        $user = Auth::user();

        // Update or create default shipping address
        $address = ShippingAddress::updateOrCreate(
            [
                'user_id' => $user->id,
                'is_default' => true,
            ],
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'postal_code' => $validated['postal_code'],
                'is_default' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil disimpan',
            'address' => $address,
        ]);
    }

    /**
     * Process checkout and create order.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        // Validate cart exists and has items
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        // Get selected cart item IDs from request (if any)
        $selectedItemIds = [];
        if (request()->has('items')) {
            $itemsParam = request()->get('items');
            if (is_string($itemsParam)) {
                $decoded = json_decode(urldecode($itemsParam), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $selectedItemIds = $decoded;
                }
            } elseif (is_array($itemsParam)) {
                $selectedItemIds = $itemsParam;
            }
        }

        // Get cart items (filtered if selected items exist)
        $cartItems = $cart->items()->with(['product', 'variant'])->get();
        if (!empty($selectedItemIds)) {
            $cartItems = $cartItems->filter(function ($item) use ($selectedItemIds) {
                return in_array($item->id, $selectedItemIds);
            });
        }

        // Validate form input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'shipping_method' => ['required', 'array', 'min:1'],
            'shipping_method.*' => ['required', 'string', 'in:standard,express,jnt,sicepat'],
            'payment_method' => ['required', 'string', 'in:transfer,cod,ewallet'],
        ], [
            'shipping_method.required' => 'Silakan pilih metode pengiriman untuk setiap item.',
            'shipping_method.array' => 'Format metode pengiriman tidak valid.',
            'shipping_method.min' => 'Minimal satu item harus memiliki metode pengiriman.',
            'shipping_method.*.required' => 'Metode pengiriman harus dipilih.',
            'shipping_method.*.in' => 'Metode pengiriman yang dipilih tidak valid.',
        ]);

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->price * $item->quantity);
        }

        // Calculate shipping cost from array of shipping methods
        $shippingCosts = [
            'standard' => 15000,
            'express' => 25000,
            'jnt' => 16000,
            'sicepat' => 18000,
        ];
        
        $shippingCost = 0;
        foreach ($validated['shipping_method'] as $itemId => $method) {
            $shippingCost += $shippingCosts[$method] ?? 15000;
        }
        
        $total = $subtotal + $shippingCost;

        // TODO: Create order in database
        // For now, just redirect to success page with order data
        $orderNumber = 'ORD-' . strtoupper(uniqid());

        return redirect()->route('checkout.success')
            ->with('order_number', $orderNumber)
            ->with('order_total', $total)
            ->with('success', 'Pesanan Anda berhasil dibuat!');
    }
}

