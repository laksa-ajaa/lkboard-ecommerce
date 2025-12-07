<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);
    }

    /**
     * Generate Snap token for order.
     */
    public function getSnapToken(Order $order): string
    {
        // Load relationships
        $order->load(['items.product', 'items.variant']);
        
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->shipping_name,
                'last_name' => '',
                'email' => $order->shipping_email,
                'phone' => $order->shipping_phone,
                'billing_address' => [
                    'first_name' => $order->shipping_name,
                    'last_name' => '',
                    'email' => $order->shipping_email,
                    'phone' => $order->shipping_phone,
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
                'shipping_address' => [
                    'first_name' => $order->shipping_name,
                    'last_name' => '',
                    'email' => $order->shipping_email,
                    'phone' => $order->shipping_phone,
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
            ],
            'item_details' => $order->items->map(function ($item) {
                return [
                    'id' => $item->product_id . ($item->variant_id ? '-' . $item->variant_id : ''),
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product->name . ($item->variant ? ' - ' . $item->variant->name : ''),
                ];
            })->toArray(),
        ];

        // Add shipping as item
        if ($order->shipping_cost > 0) {
            $params['item_details'][] = [
                'id' => 'SHIPPING',
                'price' => $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        $snapToken = Snap::getSnapToken($params);
        
        return $snapToken;
    }
}

