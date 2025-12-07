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
                    <span class="text-sm font-semibold text-slate-900 capitalize">
                        @if($order->payment_method === 'transfer')
                            Transfer Bank
                        @elseif($order->payment_method === 'cod')
                            Cash on Delivery (COD)
                        @else
                            E-Wallet
                        @endif
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

        @if($order->payment_method === 'cod')
            {{-- COD Payment Info --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-emerald-900">Pembayaran COD</h2>
                </div>
                <p class="text-sm text-emerald-800">
                    Pesanan Anda akan dibayar saat barang diterima. Silakan tunggu konfirmasi dari kami untuk pengiriman.
                </p>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('checkout.success') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                    Kembali ke Halaman Utama
                </a>
            </div>
        @else
            {{-- Midtrans Payment --}}
            <div id="snap-container" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-center">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 class="mb-2 text-lg font-semibold text-slate-900">Lanjutkan Pembayaran</h2>
                    <p class="text-sm text-slate-600">Selesaikan pembayaran Anda dengan aman melalui Midtrans</p>
                </div>
                <div id="midtrans-snap-container" class="mt-6"></div>
            </div>
        @endif
    </div>

    @if($order->payment_method !== 'cod' && $order->midtrans_snap_token)
        @push('scripts')
        <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" 
            data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script>
            window.snap.pay('{{ $order->midtrans_snap_token }}', {
                onSuccess: function(result) {
                    window.location.href = '{{ route('checkout.finish') }}?order_id={{ $order->order_number }}';
                },
                onPending: function(result) {
                    window.location.href = '{{ route('checkout.unfinish') }}?order_id={{ $order->order_number }}';
                },
                onError: function(result) {
                    window.location.href = '{{ route('checkout.error') }}?order_id={{ $order->order_number }}';
                },
                onClose: function() {
                    // User closed the payment popup
                    console.log('Payment popup closed');
                }
            });
        </script>
        @endpush
    @endif
@endsection

