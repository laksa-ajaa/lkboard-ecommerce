<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
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

        // Prepare item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => $item->product_id . ($item->variant_id ? '-' . $item->variant_id : ''),
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product->name . ($item->variant ? ' - ' . $item->variant->name : ''), 0, 50),
            ];
        }

        // Add shipping as separate item
        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        // Map payment method to Midtrans payment type
        $enabledPayments = $this->mapPaymentMethod($order->payment_method);

        // Log for debugging
        Log::info('Generating Snap token', [
            'order_id' => $order->order_number,
            'payment_method' => $order->payment_method,
            'enabled_payments' => $enabledPayments,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->shipping_name,
                'email' => $order->shipping_email,
                'phone' => $order->shipping_phone,
                'billing_address' => [
                    'first_name' => $order->shipping_name,
                    'email' => $order->shipping_email,
                    'phone' => $order->shipping_phone,
                    'address' => substr($order->shipping_address, 0, 200),
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
                'shipping_address' => [
                    'first_name' => $order->shipping_name,
                    'email' => $order->shipping_email,
                    'phone' => $order->shipping_phone,
                    'address' => substr($order->shipping_address, 0, 200),
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
            ],
            'item_details' => $itemDetails,
            'enabled_payments' => $enabledPayments,
            'callbacks' => [
                'finish' => route('checkout.finish'),
                'unfinish' => route('checkout.unfinish'),
                'error' => route('checkout.error'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            Log::info('Snap token generated successfully', [
                'order_id' => $order->order_number,
                'payment_method' => $order->payment_method,
            ]);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Error', [
                'order_id' => $order->order_number,
                'payment_method' => $order->payment_method,
                'enabled_payments' => $enabledPayments,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Gagal membuat token pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Map internal payment method to Midtrans payment type.
     * Reference: https://docs.midtrans.com/docs/snap-methods
     */
    private function mapPaymentMethod(string $paymentMethod): array
    {
        $paymentMap = [
            'bca_va' => ['bca_va'],
            'bni_va' => ['bni_va'],
            'bri_va' => ['bri_va'],
            'mandiri_va' => ['echannel'],
            'permata_va' => ['permata_va'],
            'cimb_va' => ['cimb_va'],
            'other_va' => ['other_va'],
            'gopay' => ['gopay'],
            'shopeepay' => ['shopeepay'],
            'qris' => ['qris'],
            'credit_card' => ['credit_card'],
        ];

        return $paymentMap[$paymentMethod] ?? ['bank_transfer'];
    }

    /**
     * Get payment instructions based on payment method.
     */
    public function getPaymentInstructions(string $paymentMethod, $vaNumber = null): array
    {
        $instructions = [
            'bca_va' => [
                'title' => 'BCA Virtual Account',
                'steps' => [
                    'Login ke BCA Mobile atau ATM BCA',
                    'Pilih menu Transfer',
                    'Pilih Virtual Account',
                    'Masukkan nomor Virtual Account: ' . ($vaNumber ?? '[VA Number]'),
                    'Ikuti instruksi untuk menyelesaikan pembayaran',
                ],
            ],
            'bni_va' => [
                'title' => 'BNI Virtual Account',
                'steps' => [
                    'Login ke BNI Mobile Banking atau ATM BNI',
                    'Pilih menu Transfer',
                    'Pilih Virtual Account Billing',
                    'Masukkan nomor Virtual Account: ' . ($vaNumber ?? '[VA Number]'),
                    'Ikuti instruksi untuk menyelesaikan pembayaran',
                ],
            ],
            'bri_va' => [
                'title' => 'BRI Virtual Account',
                'steps' => [
                    'Login ke BRI Mobile atau ATM BRI',
                    'Pilih menu Pembayaran',
                    'Pilih menu lainnya',
                    'Pilih BRIVA',
                    'Masukkan nomor Virtual Account: ' . ($vaNumber ?? '[VA Number]'),
                    'Ikuti instruksi untuk menyelesaikan pembayaran',
                ],
            ],
            'mandiri_va' => [
                'title' => 'Mandiri Virtual Account',
                'steps' => [
                    'Login ke Mandiri Online atau ATM Mandiri',
                    'Pilih menu Bayar/Beli',
                    'Pilih Lainnya',
                    'Pilih Multi Payment',
                    'Masukkan kode perusahaan: 70012',
                    'Masukkan nomor Virtual Account: ' . ($vaNumber ?? '[VA Number]'),
                    'Ikuti instruksi untuk menyelesaikan pembayaran',
                ],
            ],
            'gopay' => [
                'title' => 'GoPay',
                'steps' => [
                    'Buka aplikasi Gojek',
                    'Scan QR Code yang ditampilkan',
                    'Konfirmasi pembayaran',
                    'Transaksi selesai',
                ],
            ],
            'shopeepay' => [
                'title' => 'ShopeePay',
                'steps' => [
                    'Buka aplikasi Shopee',
                    'Scan QR Code atau klik link pembayaran',
                    'Konfirmasi pembayaran',
                    'Transaksi selesai',
                ],
            ],
            'qris' => [
                'title' => 'QRIS',
                'steps' => [
                    'Buka aplikasi e-wallet atau mobile banking',
                    'Scan QR Code yang ditampilkan',
                    'Konfirmasi pembayaran',
                    'Transaksi selesai',
                ],
            ],
            'credit_card' => [
                'title' => 'Kartu Kredit/Debit',
                'steps' => [
                    'Masukkan nomor kartu',
                    'Masukkan tanggal kadaluarsa',
                    'Masukkan CVV',
                    'Klik Bayar untuk menyelesaikan transaksi',
                ],
            ],
        ];

        return $instructions[$paymentMethod] ?? [
            'title' => 'Pembayaran',
            'steps' => ['Ikuti instruksi pembayaran yang diberikan'],
        ];
    }
}
