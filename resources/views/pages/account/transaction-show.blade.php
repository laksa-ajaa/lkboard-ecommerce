@extends('layouts.account')

@section('title', 'Detail Pesanan #' . $order->order_number . ' | ' . config('app.name'))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pesanan</h1>
            <p class="text-sm text-gray-600 mt-1">#{{ $order->order_number }}</p>
        </div>
        <a href="{{ route('account.transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            ← Kembali ke Daftar Pesanan
        </a>
    </div>

    {{-- Order Status --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Status Pesanan</h2>
                <div class="flex flex-wrap items-center gap-3">
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            'shipped' => 'bg-indigo-100 text-indigo-700',
                            'delivered' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabels = [
                            'pending' => 'Menunggu',
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'delivered' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ];
                        $paymentColors = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'paid' => 'bg-emerald-100 text-emerald-700',
                            'failed' => 'bg-red-100 text-red-700',
                            'expired' => 'bg-gray-100 text-gray-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $paymentLabels = [
                            'pending' => 'Menunggu Pembayaran',
                            'paid' => 'Lunas',
                            'failed' => 'Gagal',
                            'expired' => 'Kedaluwarsa',
                            'cancelled' => 'Dibatalkan',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Tanggal Pesanan</p>
                <p class="text-lg font-semibold text-gray-900">{{ $order->created_at->format('d F Y, H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Order Items --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Item Pesanan</h2>
        <div class="space-y-4">
            @foreach($order->items as $item)
            <div class="flex gap-4 pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                    @if($item->product && $item->product->thumbnail)
                        <img src="{{ $item->product->thumbnail }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-gray-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">
                        @if($item->product)
                            <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-indigo-600">
                                {{ $item->product->name }}
                            </a>
                        @else
                            Produk tidak tersedia
                        @endif
                    </h3>
                    @if($item->variant)
                        <p class="text-sm text-gray-600 mt-1">Varian: {{ $item->variant->name }}</p>
                    @endif
                    <p class="text-sm text-gray-600 mt-1">Ekspedisi: {{ ucfirst($item->shipping_method) }}</p>
                    <p class="text-sm text-gray-600">Jumlah: {{ $item->quantity }}x</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-indigo-600">
                        Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-500">@ Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Shipping Address --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Alamat Pengiriman</h2>
        <div class="space-y-1 text-sm text-gray-600">
            <p class="font-semibold text-gray-900">{{ $order->shipping_name }}</p>
            <p>{{ $order->shipping_email }}</p>
            <p>{{ $order->shipping_phone }}</p>
            <p class="mt-2">{{ $order->shipping_address }}</p>
            <p>{{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}</p>
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium text-gray-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Ongkos Kirim</span>
                <span class="font-medium text-gray-900">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                <span class="text-lg font-semibold text-gray-900">Total</span>
                <span class="text-xl font-bold text-indigo-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
            <div class="pt-3 border-t border-gray-200">
                <p class="text-sm text-gray-600">Metode Pembayaran: <span class="font-medium">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span></p>
            </div>
        </div>
    </div>

    @if($order->payment_status === 'pending' && $order->status !== 'cancelled')
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
        <div class="flex items-start gap-3">
            <svg class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="font-semibold text-amber-900 mb-1">Menunggu Pembayaran</h3>
                <p class="text-sm text-amber-700">Silakan selesaikan pembayaran Anda untuk melanjutkan proses pesanan.</p>
                @if($order->midtrans_snap_token)
                <a href="{{ route('checkout.payment', $order) }}" class="mt-3 inline-block px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition text-sm font-medium">
                    Lanjutkan Pembayaran
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

