@extends('layouts.app')

@section('title', 'Pembayaran – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-0">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-xs text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
            <span>/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-indigo-600">Keranjang</a>
            <span>/</span>
            <a href="{{ route('checkout.index') }}" class="hover:text-indigo-600">Checkout</a>
            <span>/</span>
            <span class="text-slate-900 font-medium">Pembayaran</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold text-slate-900">Pembayaran</h1>
            <p class="mt-2 text-sm text-slate-600">Selesaikan pembayaran untuk menyelesaikan pesanan Anda</p>
        </div>

        {{-- Order Summary --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-4">
                <span class="text-sm font-medium text-slate-600">Nomor Pesanan</span>
                <span class="text-lg font-bold text-slate-900">#{{ $order->order_number }}</span>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Metode Pembayaran</span>
                    <span class="text-sm font-semibold text-slate-900">
                        @php
                            $paymentMethods = [
                                'mandiri_va' => 'Mandiri Virtual Account',
                                'gopay' => 'GoPay',
                                'shopeepay' => 'ShopeePay',
                                'credit_card' => 'Kartu Kredit / Debit',
                            ];
                        @endphp
                        {{ $paymentMethods[$order->payment_method] ?? ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Total Pembayaran</span>
                    <span class="text-xl font-bold text-indigo-600">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Payment Status Indicator --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-slate-900">Menunggu Pembayaran</h3>
                    <p class="mt-1 text-xs text-slate-600">
                        Silakan selesaikan pembayaran Anda. Jangan tutup halaman ini sampai pembayaran selesai.
                    </p>
                </div>
            </div>
        </div>

        {{-- Payment Button --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-center">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="mb-2 text-lg font-semibold text-slate-900">Lanjutkan Pembayaran</h2>
                <p class="mb-6 text-sm text-slate-600">
                    Klik tombol di bawah untuk melanjutkan ke halaman pembayaran yang aman
                </p>

                <button id="pay-button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-8 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Bayar Sekarang
                </button>
            </div>
        </div>

        {{-- Order Details --}}
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Detail Pesanan</h3>
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg bg-slate-100">
                            @if ($item->product->thumbnail)
                                <img src="{{ $item->product->thumbnail }}" alt="{{ $item->product->name }}"
                                    class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-slate-900">{{ $item->product->name }}</h4>
                            @if ($item->variant)
                                <p class="text-xs text-slate-600">{{ $item->variant->name }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-600">{{ $item->quantity }} × Rp
                                {{ number_format($item->price, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900">
                                Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 space-y-2 border-t border-slate-200 pt-4">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Subtotal</span>
                    <span class="font-medium text-slate-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Ongkir</span>
                    <span class="font-medium text-slate-900">Rp
                        {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base">
                    <span class="font-semibold text-slate-900">Total</span>
                    <span class="font-bold text-indigo-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if ($order->midtrans_snap_token)
        @push('scripts')
            <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
            <script>
                const payButton = document.getElementById('pay-button');
                const snapToken = '{{ $order->midtrans_snap_token }}';

                payButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    window.snap.pay(snapToken, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            window.location.href =
                                '{{ route('checkout.finish') }}?order_id={{ $order->order_number }}';
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            window.location.href =
                                '{{ route('checkout.finish') }}?order_id={{ $order->order_number }}';
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            window.location.href =
                                '{{ route('checkout.error') }}?order_id={{ $order->order_number }}';
                        },
                        onClose: function() {
                            console.log('Payment popup closed');
                            // Check if transaction might be expired or failed
                            // Redirect to finish handler which will check the actual status
                            window.location.href =
                                '{{ route('checkout.finish') }}?order_id={{ $order->order_number }}';
                        }
                    });
                });

                // Auto-trigger payment on page load (optional)
                // Uncomment the line below if you want to auto-open payment popup
                // setTimeout(() => payButton.click(), 500);
            </script>
        @endpush
    @else
        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-6">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 flex-shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-semibold text-rose-900">Token Pembayaran Tidak Tersedia</h3>
                    <p class="mt-1 text-sm text-rose-700">
                        Terjadi kesalahan saat membuat token pembayaran. Silakan hubungi customer service atau coba lagi
                        nanti.
                    </p>
                </div>
            </div>
        </div>
    @endif
@endsection
