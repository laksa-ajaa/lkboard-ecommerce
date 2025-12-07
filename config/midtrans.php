<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | File ini menghubungkan Laravel dengan Midtrans.
    | Semua nilai dikontrol melalui file .env agar mudah pindah dari sandbox
    | ke production.
    |
    */

    'merchant_id'   => env('MIDTRANS_MERCHANT_ID', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),

    // True = Production, False = Sandbox
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Sanitized & 3DS untuk keamanan transaksi
    'is_sanitized'  => env('MIDTRANS_SANITIZE', true),
    'is_3ds'        => env('MIDTRANS_3DS', true),

];
