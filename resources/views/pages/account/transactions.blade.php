@extends('layouts.account')

@section('title', 'Pesanan Saya | ' . config('app.name'))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="border-b border-gray-200 pb-4">
        <h1 class="text-2xl font-bold text-gray-900">Pesanan Saya</h1>
        <p class="text-sm text-gray-600 mt-1">Lihat dan kelola semua pesanan Anda</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4" x-data="{ 
        status: '{{ $status }}',
        paymentStatus: '{{ $paymentStatus }}',
        search: '{{ $search }}',
        showFilters: false
    }">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Search --}}
            <form method="GET" action="{{ route('account.transactions.index') }}" class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor pesanan..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 pl-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="hidden" name="status" :value="status">
                <input type="hidden" name="payment_status" :value="paymentStatus">
            </form>

            {{-- Filter Toggle --}}
            <button @click="showFilters = !showFilters" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
        </div>

        {{-- Filter Options --}}
        <div x-show="showFilters" x-transition class="mt-4 pt-4 border-t border-gray-200">
            <form method="GET" action="{{ route('account.transactions.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="search" value="{{ $search }}">
                
                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pesanan</label>
                    <select name="status" x-model="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="processing">Diproses</option>
                        <option value="shipped">Dikirim</option>
                        <option value="delivered">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                {{-- Payment Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran</label>
                    <select name="payment_status" x-model="paymentStatus" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">Semua Status</option>
                        <option value="pending">Menunggu Pembayaran</option>
                        <option value="paid">Lunas</option>
                        <option value="failed">Gagal</option>
                        <option value="expired">Kedaluwarsa</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('account.transactions.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-600 mt-1">Total Pesanan</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</div>
            <div class="text-xs text-gray-600 mt-1">Menunggu</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</div>
            <div class="text-xs text-gray-600 mt-1">Diproses</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-indigo-600">{{ $stats['shipped'] }}</div>
            <div class="text-xs text-gray-600 mt-1">Dikirim</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['delivered'] }}</div>
            <div class="text-xs text-gray-600 mt-1">Selesai</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</div>
            <div class="text-xs text-gray-600 mt-1">Dibatalkan</div>
        </div>
    </div>

    {{-- Orders List --}}
    @if($orders->count() > 0)
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">#{{ $order->order_number }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('d F Y, H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3 mt-3">
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
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>
                
                <div class="flex flex-col items-end gap-2">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-xl font-bold text-indigo-600">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('account.transactions.show', $order->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="mt-4 text-sm text-gray-600">Tidak ada pesanan ditemukan</p>
        <a href="{{ route('products.index') }}" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            Mulai Belanja →
        </a>
    </div>
    @endif
</div>
@endsection

