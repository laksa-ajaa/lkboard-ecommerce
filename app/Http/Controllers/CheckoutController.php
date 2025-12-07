<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingAddress;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Generate order number
        $orderNumber = 'ORD-' . strtoupper(uniqid());

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'shipping_name' => $validated['name'],
                'shipping_email' => $validated['email'],
                'shipping_phone' => $validated['phone'],
                'shipping_address' => $validated['address'],
                'shipping_city' => $validated['city'],
                'shipping_province' => $validated['province'],
                'shipping_postal_code' => $validated['postal_code'],
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                $shippingMethod = $validated['shipping_method'][$cartItem->id] ?? 'standard';
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'shipping_method' => $shippingMethod,
                ]);
            }

            // Generate Midtrans snap token if payment method is not COD
            if ($validated['payment_method'] !== 'cod') {
                $midtransService = new MidtransService();
                $snapToken = $midtransService->getSnapToken($order);
                
                $order->update([
                    'midtrans_snap_token' => $snapToken,
                    'midtrans_order_id' => $orderNumber,
                ]);
            }

            DB::commit();

            // For COD, redirect to success page
            if ($validated['payment_method'] === 'cod') {
                // Clear cart items that were ordered
                foreach ($cartItems as $cartItem) {
                    $cartItem->delete();
                }

                return redirect()->route('checkout.success')
                    ->with('order_number', $orderNumber)
                    ->with('order_total', $total)
                    ->with('success', 'Pesanan Anda berhasil dibuat!');
            }

            // For other payment methods, redirect to payment page
            return redirect()->route('checkout.payment', ['order' => $order->id])
                ->with('order_number', $orderNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Show payment page with Midtrans snap.
     */
    public function payment(Order $order): View
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success')
                ->with('order_number', $order->order_number)
                ->with('order_total', $order->total)
                ->with('success', 'Pesanan Anda sudah dibayar!');
        }

        // Generate snap token if not exists and payment is not COD
        if ($order->payment_method !== 'cod' && !$order->midtrans_snap_token) {
            try {
                $midtransService = new MidtransService();
                $snapToken = $midtransService->getSnapToken($order);
                $order->update(['midtrans_snap_token' => $snapToken]);
            } catch (\Exception $e) {
                return redirect()->route('checkout.index')
                    ->with('error', 'Gagal membuat token pembayaran: ' . $e->getMessage());
            }
        }

        return view('pages.checkout.payment', [
            'order' => $order->load(['items.product', 'items.variant']),
        ]);
    }

    /**
     * Handle Midtrans notification/callback.
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $notification = new \Midtrans\Notification();
            
            $orderNumber = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            
            $order = Order::where('order_number', $orderNumber)->first();
            
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update order based on transaction status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $order->update(['payment_status' => 'challenge']);
                } else if ($fraudStatus == 'accept') {
                    $order->update([
                        'payment_status' => 'paid',
                        'midtrans_transaction_id' => $notification->transaction_id,
                        'midtrans_response' => $notification->getResponse(),
                    ]);
                    
                    // Clear cart items
                    $cart = $order->user->cart;
                    if ($cart) {
                        $cart->items()->delete();
                    }
                }
            } else if ($transactionStatus == 'settlement') {
                $order->update([
                    'payment_status' => 'paid',
                    'midtrans_transaction_id' => $notification->transaction_id,
                    'midtrans_response' => $notification->getResponse(),
                ]);
                
                // Clear cart items
                $cart = $order->user->cart;
                if ($cart) {
                    $cart->items()->delete();
                }
            } else if ($transactionStatus == 'pending') {
                $order->update(['payment_status' => 'pending']);
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                $order->update([
                    'payment_status' => 'failed',
                    'midtrans_response' => $notification->getResponse(),
                ]);
            }

            return response()->json(['message' => 'Notification processed']);
            
        } catch (\Exception $e) {
            \Log::error('Midtrans notification error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle payment finish redirect.
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        
        if (!$orderId) {
            return redirect()->route('checkout.index')
                ->with('error', 'Order ID tidak ditemukan.');
        }

        $order = Order::where('order_number', $orderId)->first();
        
        if (!$order) {
            return redirect()->route('checkout.index')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        // Check payment status
        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success')
                ->with('order_number', $order->order_number)
                ->with('order_total', $order->total)
                ->with('success', 'Pembayaran berhasil!');
        }

        return redirect()->route('checkout.payment', ['order' => $order->id])
            ->with('info', 'Menunggu konfirmasi pembayaran...');
    }

    /**
     * Handle payment unfinish redirect.
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');
        
        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();
            
            if ($order) {
                return redirect()->route('checkout.payment', ['order' => $order->id])
                    ->with('error', 'Pembayaran belum selesai. Silakan coba lagi.');
            }
        }

        return redirect()->route('checkout.index')
            ->with('error', 'Pembayaran belum selesai. Silakan coba lagi.');
    }

    /**
     * Handle payment error redirect.
     */
    public function error(Request $request)
    {
        $orderId = $request->get('order_id');
        
        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();
            
            if ($order) {
                $order->update(['payment_status' => 'failed']);
                
                return redirect()->route('checkout.payment', ['order' => $order->id])
                    ->with('error', 'Pembayaran gagal. Silakan coba lagi atau gunakan metode pembayaran lain.');
            }
        }

        return redirect()->route('checkout.index')
            ->with('error', 'Pembayaran gagal. Silakan coba lagi.');
    }
}

