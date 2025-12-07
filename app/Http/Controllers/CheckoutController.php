<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(): View|RedirectResponse
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
        /** @var \App\Models\User $user */
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
     * Display checkout page for buy now (direct from product, no cart).
     */
    public function buyNow(Request $request): View|RedirectResponse
    {
        // Redirect to login if not authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('redirect', $request->fullUrl());
        }

        // Validate required parameters
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $variant = $request->variant_id ? ProductVariant::findOrFail($request->variant_id) : null;

        // Validate variant is required if product has variants
        if ($product->variants()->count() > 0 && !$variant) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'Silakan pilih varian terlebih dahulu.');
        }

        // Check stock
        $stock = $variant?->stock ?? $product->stock;
        if ($stock < $request->quantity) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $stock);
        }

        // Get price
        $price = $variant?->price ?? $product->price;

        // Format item for view
        $formattedItem = [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $product->name,
            'variant' => $variant?->name,
            'price' => $price,
            'quantity' => $request->quantity,
            'stock' => $stock,
            'image' => $product->thumbnail,
            'product_url' => route('products.show', $product->slug),
        ];

        // Calculate totals
        $subtotal = $price * $request->quantity;
        $shippingCost = 15000; // Default shipping cost
        $total = $subtotal + $shippingCost;

        // Load saved shipping address
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $savedAddress = $user->shippingAddresses()
            ->where('is_default', true)
            ->first();

        return view('pages.checkout.buy-now', [
            'item' => $formattedItem,
            'subtotal' => $subtotal,
            'shippingCost' => $shippingCost,
            'total' => $total,
            'user' => $user,
            'savedAddress' => $savedAddress,
        ]);
    }

    /**
     * Process buy now checkout and create order (without cart).
     */
    public function storeBuyNow(Request $request)
    {
        $user = Auth::user();

        // Validate form input
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'shipping_method' => ['required', 'string', 'in:standard,express,jnt,sicepat'],
            'payment_method' => ['required', 'string', 'in:mandiri_va,gopay,shopeepay,credit_card'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variant = $validated['variant_id'] ? ProductVariant::findOrFail($validated['variant_id']) : null;

        // Validate variant is required if product has variants
        if ($product->variants()->count() > 0 && !$variant) {
            return back()->withInput()->with('error', 'Silakan pilih varian terlebih dahulu.');
        }

        // Check stock
        $stock = $variant?->stock ?? $product->stock;
        if ($stock < $validated['quantity']) {
            return back()->withInput()->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $stock);
        }

        // Get price
        $price = $variant?->price ?? $product->price;

        // Calculate totals
        $subtotal = $price * $validated['quantity'];

        // Calculate shipping cost
        $shippingCosts = [
            'standard' => 15000,
            'express' => 25000,
            'jnt' => 16000,
            'sicepat' => 18000,
        ];

        $shippingCost = $shippingCosts[$validated['shipping_method']] ?? 15000;
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

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $validated['quantity'],
                'price' => $price,
                'shipping_method' => $validated['shipping_method'],
            ]);

            // Generate Midtrans snap token
            $midtransService = new MidtransService();
            $snapToken = $midtransService->getSnapToken($order);

            $order->update([
                'midtrans_snap_token' => $snapToken,
                'midtrans_order_id' => $orderNumber,
            ]);

            DB::commit();

            // Redirect to payment page
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
        // Bisa dari query string (GET) atau form data (POST)
        $selectedItemIds = [];
        $itemsParam = $request->input('items') ?? request()->get('items');

        if ($itemsParam) {
            if (is_string($itemsParam)) {
                // Coba decode jika berupa JSON string
                $decoded = json_decode(urldecode($itemsParam), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $selectedItemIds = array_map('intval', $decoded);
                } else {
                    // Jika bukan JSON, coba sebagai array biasa
                    $selectedItemIds = [intval($itemsParam)];
                }
            } elseif (is_array($itemsParam)) {
                $selectedItemIds = array_map('intval', $itemsParam);
            }
        }

        // Get cart items (filtered if selected items exist)
        $cartItems = $cart->items()->with(['product', 'variant'])->get();
        if (!empty($selectedItemIds)) {
            $cartItems = $cartItems->filter(function ($item) use ($selectedItemIds) {
                return in_array((int)$item->id, $selectedItemIds, true);
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
            'payment_method' => ['required', 'string', 'in:mandiri_va,gopay,shopeepay,credit_card'],
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

            // Generate Midtrans snap token for all payment methods
            $midtransService = new MidtransService();
            $snapToken = $midtransService->getSnapToken($order);

            $order->update([
                'midtrans_snap_token' => $snapToken,
                'midtrans_order_id' => $orderNumber,
            ]);

            DB::commit();

            // Redirect to payment page
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
    public function payment(Order $order): View|RedirectResponse
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

        // Check if payment is expired or failed
        if (in_array($order->payment_status, ['expired', 'failed', 'cancelled'])) {
            return redirect()->route('checkout.failed')
                ->with('expired', $order->payment_status === 'expired')
                ->with('order_number', $order->order_number)
                ->with('order_total', $order->total);
        }

        // Generate snap token if not exists
        if (!$order->midtrans_snap_token) {
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
            // Initialize Midtrans Config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            $notification = new \Midtrans\Notification();

            $orderNumber = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $fraudStatus = $notification->fraud_status ?? null;

            Log::info('Midtrans Notification', [
                'order_id' => $orderNumber,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'fraud_status' => $fraudStatus
            ]);

            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                Log::error('Order not found: ' . $orderNumber);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update order based on transaction status
            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $order->update([
                            'payment_status' => 'challenge',
                            'midtrans_transaction_id' => $notification->transaction_id,
                        ]);
                    } else if ($fraudStatus == 'accept') {
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'midtrans_transaction_id' => $notification->transaction_id,
                        ]);

                        $this->clearCartItems($order);
                        $this->reduceStock($order);
                    }
                }
            } else if ($transactionStatus == 'settlement') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'midtrans_transaction_id' => $notification->transaction_id,
                    'paid_at' => now(),
                ]);

                $this->clearCartItems($order);
                $this->reduceStock($order);
            } else if ($transactionStatus == 'pending') {
                $order->update([
                    'payment_status' => 'pending',
                    'midtrans_transaction_id' => $notification->transaction_id,
                ]);
            } else if ($transactionStatus == 'deny') {
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);
            } else if ($transactionStatus == 'expire') {
                $order->update([
                    'payment_status' => 'expired',
                    'status' => 'cancelled',
                ]);
            } else if ($transactionStatus == 'cancel') {
                $order->update([
                    'payment_status' => 'cancelled',
                    'status' => 'cancelled',
                ]);
            }

            return response()->json(['message' => 'Notification processed successfully']);
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Clear cart items after successful payment.
     */
    private function clearCartItems(Order $order): void
    {
        try {
            $cart = $order->user->cart;
            if ($cart) {
                // Match and delete cart items based on product_id and variant_id from order items
                foreach ($order->items as $orderItem) {
                    $query = $cart->items()
                        ->where('product_id', $orderItem->product_id)
                        ->where('variant_id', $orderItem->variant_id);

                    $cartItem = $query->first();
                    if ($cartItem) {
                        // Reduce quantity or delete if quantity matches
                        if ($cartItem->quantity <= $orderItem->quantity) {
                            $cartItem->delete();
                        } else {
                            $cartItem->decrement('quantity', $orderItem->quantity);
                        }
                    }
                }

                Log::info('Cart items cleared for order: ' . $order->order_number);
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear cart items: ' . $e->getMessage());
        }
    }

    /**
     * Reduce stock after successful payment.
     */
    private function reduceStock(Order $order): void
    {
        try {
            DB::beginTransaction();

            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    // Reduce variant stock
                    $variant = $item->variant;
                    if ($variant) {
                        $variant->decrement('stock', $item->quantity);
                    }
                } else {
                    // Reduce product stock
                    $product = $item->product;
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                    }
                }
            }

            DB::commit();
            Log::info('Stock reduced for order: ' . $order->order_number);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reduce stock: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status from Midtrans API.
     */
    public function checkStatus(Order $order): JsonResponse
    {
        // Verify user owns this order
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this order.',
            ], 403);
        }

        try {
            // Initialize Midtrans Config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            // Check status from Midtrans
            /** @var object{transaction_status: string, transaction_id: string, payment_type: string, fraud_status?: string} $status */
            $status = \Midtrans\Transaction::status($order->order_number);
            $transactionStatus = (string) ($status->transaction_status ?? '');
            $paymentType = (string) ($status->payment_type ?? '');
            $fraudStatus = isset($status->fraud_status) ? (string) $status->fraud_status : null;
            $transactionId = isset($status->transaction_id) ? (string) $status->transaction_id : null;

            $updated = false;
            $redirectUrl = null;

            // Update order based on transaction status
            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card' && $fraudStatus == 'challenge') {
                    if ($order->payment_status !== 'challenge') {
                        $order->update([
                            'payment_status' => 'challenge',
                            'midtrans_transaction_id' => $transactionId,
                        ]);
                        $updated = true;
                    }
                } else if ($paymentType == 'credit_card' && $fraudStatus == 'accept') {
                    if ($order->payment_status !== 'paid') {
                        DB::beginTransaction();
                        try {
                            $order->update([
                                'payment_status' => 'paid',
                                'status' => 'processing',
                                'midtrans_transaction_id' => $transactionId,
                            ]);

                            $this->clearCartItems($order);
                            $this->reduceStock($order);
                            DB::commit();

                            $updated = true;
                            $redirectUrl = route('checkout.success');
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Failed to update order after payment check: ' . $e->getMessage());
                            return response()->json([
                                'success' => false,
                                'message' => 'Gagal memperbarui status pesanan.',
                            ], 500);
                        }
                    } else {
                        $redirectUrl = route('checkout.success');
                    }
                }
            } else if ($transactionStatus == 'settlement') {
                if ($order->payment_status !== 'paid') {
                    DB::beginTransaction();
                    try {
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'midtrans_transaction_id' => $transactionId,
                        ]);

                        $this->clearCartItems($order);
                        $this->reduceStock($order);
                        DB::commit();

                        $updated = true;
                        $redirectUrl = route('checkout.success');
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to update order after payment check: ' . $e->getMessage());
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal memperbarui status pesanan.',
                        ], 500);
                    }
                } else {
                    $redirectUrl = route('checkout.success');
                }
            } else if ($transactionStatus == 'pending') {
                if ($order->payment_status !== 'pending') {
                    $order->update([
                        'payment_status' => 'pending',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                    $updated = true;
                }
            } else if ($transactionStatus == 'expire') {
                if ($order->payment_status !== 'expired') {
                    $order->update([
                        'payment_status' => 'expired',
                        'status' => 'cancelled',
                    ]);
                    $updated = true;
                }
                $redirectUrl = route('checkout.failed');
            } else if (in_array($transactionStatus, ['deny', 'cancel'])) {
                if ($order->payment_status !== 'failed' && $order->payment_status !== 'cancelled') {
                    $order->update([
                        'payment_status' => $transactionStatus == 'deny' ? 'failed' : 'cancelled',
                        'status' => 'cancelled',
                    ]);
                    $updated = true;
                }
                $redirectUrl = route('checkout.failed');
            }

            return response()->json([
                'success' => true,
                'message' => $updated ? 'Status pembayaran berhasil diperbarui.' : 'Status pembayaran sudah up to date.',
                'payment_status' => $order->fresh()->payment_status,
                'redirect' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check payment status: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek status pembayaran. Silakan coba lagi nanti.',
            ], 500);
        }
    }

    /**
     * Handle payment finish redirect.
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderId = $request->get('order_id');

        if (!$orderId) {
            return redirect()->route('home')
                ->with('error', 'Order ID tidak ditemukan.');
        }

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        // Verify payment status from Midtrans and update order if needed
        try {
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            /** @var object{transaction_status: string, transaction_id: string, payment_type: string, fraud_status?: string} $status */
            $status = \Midtrans\Transaction::status($orderId);
            $transactionStatus = (string) ($status->transaction_status ?? '');
            $paymentType = (string) ($status->payment_type ?? '');
            $fraudStatus = isset($status->fraud_status) ? (string) $status->fraud_status : null;
            $transactionId = isset($status->transaction_id) ? (string) $status->transaction_id : null;

            // Update order status based on transaction status
            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card' && $fraudStatus == 'challenge') {
                    $order->update([
                        'payment_status' => 'challenge',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                } else if ($paymentType == 'credit_card' && $fraudStatus == 'accept') {
                    // Payment successful via credit card
                    if ($order->payment_status !== 'paid') {
                        DB::beginTransaction();
                        try {
                            $order->update([
                                'payment_status' => 'paid',
                                'status' => 'processing',
                                'midtrans_transaction_id' => $transactionId,
                            ]);

                            $this->clearCartItems($order);
                            $this->reduceStock($order);
                            DB::commit();

                            Log::info('Payment successful via finish handler (capture)', [
                                'order_id' => $order->order_number,
                                'transaction_id' => $transactionId,
                            ]);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Failed to update order after payment: ' . $e->getMessage());
                        }
                    }
                    return redirect()->route('checkout.success')
                        ->with('success', 'Pembayaran berhasil! Terima kasih atas pesanan Anda.')
                        ->with('order_number', $order->order_number)
                        ->with('order_total', $order->total);
                }
            } else if ($transactionStatus == 'settlement') {
                // Payment successful via other methods (VA, e-wallet, etc)
                if ($order->payment_status !== 'paid') {
                    DB::beginTransaction();
                    try {
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'midtrans_transaction_id' => $transactionId,
                        ]);

                        $this->clearCartItems($order);
                        $this->reduceStock($order);
                        DB::commit();

                        Log::info('Payment successful via finish handler (settlement)', [
                            'order_id' => $order->order_number,
                            'transaction_id' => $transactionId,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Failed to update order after payment: ' . $e->getMessage());
                    }
                }
                return redirect()->route('checkout.success')
                    ->with('success', 'Pembayaran berhasil! Terima kasih atas pesanan Anda.')
                    ->with('order_number', $order->order_number)
                    ->with('order_total', $order->total);
            } else if ($transactionStatus == 'pending') {
                // Update pending status if not already set
                if ($order->payment_status !== 'pending') {
                    $order->update([
                        'payment_status' => 'pending',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                }
                return redirect()->route('checkout.payment', $order->id)
                    ->with('info', 'Menunggu konfirmasi pembayaran. Silakan selesaikan pembayaran Anda.');
            } else if ($transactionStatus == 'expire') {
                if ($order->payment_status !== 'expired') {
                    $order->update([
                        'payment_status' => 'expired',
                        'status' => 'cancelled',
                    ]);
                }
                return redirect()->route('checkout.failed')
                    ->with('expired', true)
                    ->with('order_number', $order->order_number)
                    ->with('order_total', $order->total);
            } else if (in_array($transactionStatus, ['deny', 'cancel'])) {
                if ($order->payment_status !== 'failed' && $order->payment_status !== 'cancelled') {
                    $order->update([
                        'payment_status' => $transactionStatus == 'deny' ? 'failed' : 'cancelled',
                        'status' => 'cancelled',
                    ]);
                }
                return redirect()->route('checkout.failed')
                    ->with('order_number', $order->order_number)
                    ->with('order_total', $order->total);
            }
        } catch (\Exception $e) {
            Log::error('Failed to check payment status: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return redirect()->route('checkout.payment', $order->id)
            ->with('info', 'Pesanan Anda telah dibuat. Silakan selesaikan pembayaran.');
    }

    /**
     * Handle payment unfinish redirect.
     */
    public function unfinish(Request $request): RedirectResponse
    {
        $orderId = $request->get('order_id');

        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();

            if ($order) {
                // Check if payment is expired (user clicked "return to merchant" on expired popup)
                try {
                    \Midtrans\Config::$serverKey = config('midtrans.server_key');
                    \Midtrans\Config::$isProduction = config('midtrans.is_production');

                    /** @var object{transaction_status: string} $status */
                    $status = \Midtrans\Transaction::status($orderId);
                    $transactionStatus = (string) ($status->transaction_status ?? '');

                    if ($transactionStatus == 'expire') {
                        return redirect()->route('checkout.failed')
                            ->with('expired', true)
                            ->with('order_number', $order->order_number)
                            ->with('order_total', $order->total);
                    } else if (in_array($transactionStatus, ['deny', 'cancel', 'failed'])) {
                        return redirect()->route('checkout.failed')
                            ->with('order_number', $order->order_number)
                            ->with('order_total', $order->total);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to check payment status in unfinish handler: ' . $e->getMessage());
                }

                // If status is still pending, return to payment page
                return redirect()->route('checkout.payment', ['order' => $order->id])
                    ->with('warning', 'Pembayaran belum selesai. Silakan selesaikan pembayaran Anda.');
            }
        }

        return redirect()->route('cart.index')
            ->with('warning', 'Pembayaran belum selesai.');
    }

    /**
     * Handle payment error redirect.
     */
    public function error(Request $request): RedirectResponse
    {
        $orderId = $request->get('order_id');

        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();

            if ($order) {
                // Check if payment is expired
                try {
                    \Midtrans\Config::$serverKey = config('midtrans.server_key');
                    \Midtrans\Config::$isProduction = config('midtrans.is_production');

                    /** @var object{transaction_status: string} $status */
                    $status = \Midtrans\Transaction::status($orderId);
                    $transactionStatus = (string) ($status->transaction_status ?? '');

                    if ($transactionStatus == 'expire') {
                        return redirect()->route('checkout.failed')
                            ->with('expired', true)
                            ->with('order_number', $order->order_number)
                            ->with('order_total', $order->total);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to check payment status in error handler: ' . $e->getMessage());
                }

                return redirect()->route('checkout.failed')
                    ->with('order_number', $order->order_number)
                    ->with('order_total', $order->total);
            }
        }

        return redirect()->route('checkout.failed');
    }
}
