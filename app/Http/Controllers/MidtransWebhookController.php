<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    /**
     * Handle Midtrans webhook notification.
     * 
     * Route: POST /midtrans/webhook
     * 
     * This endpoint receives payment status updates from Midtrans
     * and updates the order status accordingly.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            // Initialize Midtrans Config
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');

            // Parse notification from Midtrans
            $notification = new Notification();

            $orderNumber = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $fraudStatus = $notification->fraud_status ?? null;
            $transactionId = $notification->transaction_id ?? null;

            Log::info('Midtrans Webhook Received', [
                'order_id' => $orderNumber,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'fraud_status' => $fraudStatus,
                'transaction_id' => $transactionId,
            ]);

            // Find order by order number
            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                Log::error('Order not found in webhook', [
                    'order_id' => $orderNumber,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found',
                ], 404);
            }

            // Update order based on transaction status
            $this->updateOrderStatus($order, $transactionStatus, $paymentType, $fraudStatus, $transactionId);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status based on Midtrans transaction status.
     */
    private function updateOrderStatus(
        Order $order,
        string $transactionStatus,
        string $paymentType,
        ?string $fraudStatus,
        ?string $transactionId
    ): void {
        DB::beginTransaction();

        try {
            switch ($transactionStatus) {
                case 'capture':
                    // Credit card payment
                    if ($paymentType === 'credit_card') {
                        if ($fraudStatus === 'challenge') {
                            // Payment is being challenged by fraud detection
                            $order->update([
                                'payment_status' => 'challenge',
                                'midtrans_transaction_id' => $transactionId,
                            ]);
                            Log::info('Order payment challenged', [
                                'order_id' => $order->order_number,
                            ]);
                        } elseif ($fraudStatus === 'accept') {
                            // Payment accepted
                            $this->markOrderAsPaid($order, $transactionId);
                        }
                    }
                    break;

                case 'settlement':
                    // Payment successfully settled (VA, e-wallet, etc.)
                    $this->markOrderAsPaid($order, $transactionId);
                    break;

                case 'pending':
                    // Payment is pending
                    $order->update([
                        'payment_status' => 'pending',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                    Log::info('Order payment pending', [
                        'order_id' => $order->order_number,
                    ]);
                    break;

                case 'deny':
                    // Payment denied
                    $order->update([
                        'payment_status' => 'failed',
                        'status' => 'cancelled',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                    Log::info('Order payment denied', [
                        'order_id' => $order->order_number,
                    ]);
                    break;

                case 'expire':
                    // Payment expired
                    $order->update([
                        'payment_status' => 'expired',
                        'status' => 'cancelled',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                    Log::info('Order payment expired', [
                        'order_id' => $order->order_number,
                    ]);
                    break;

                case 'cancel':
                    // Payment cancelled
                    $order->update([
                        'payment_status' => 'cancelled',
                        'status' => 'cancelled',
                        'midtrans_transaction_id' => $transactionId,
                    ]);
                    Log::info('Order payment cancelled', [
                        'order_id' => $order->order_number,
                    ]);
                    break;

                default:
                    Log::warning('Unknown transaction status', [
                        'order_id' => $order->order_number,
                        'status' => $transactionStatus,
                    ]);
                    break;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order status', [
                'order_id' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark order as paid and perform necessary actions.
     */
    private function markOrderAsPaid(Order $order, ?string $transactionId): void
    {
        // Only update if not already paid to avoid duplicate processing
        if ($order->payment_status === 'paid') {
            Log::info('Order already paid, skipping update', [
                'order_id' => $order->order_number,
            ]);
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'midtrans_transaction_id' => $transactionId,
        ]);

        // Clear cart items
        $this->clearCartItems($order);

        // Reduce stock
        $this->reduceStock($order);

        Log::info('Order marked as paid', [
            'order_id' => $order->order_number,
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Clear cart items after successful payment.
     */
    private function clearCartItems(Order $order): void
    {
        try {
            $cart = $order->user->cart;
            if (!$cart) {
                return;
            }

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

            Log::info('Cart items cleared for order', [
                'order_id' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear cart items', [
                'order_id' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reduce stock after successful payment.
     */
    private function reduceStock(Order $order): void
    {
        try {
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

            Log::info('Stock reduced for order', [
                'order_id' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reduce stock', [
                'order_id' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

