@extends('layouts.account')

@section('title', 'Profil Saya | ' . config('app.name'))

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="border-b border-gray-200 pb-4">
            <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola informasi profil dan lihat ringkasan akun Anda</p>
        </div>

        {{-- Informasi Profil --}}
        <div class="grid gap-6 md:grid-cols-2">
            {{-- Informasi Dasar --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Informasi Dasar</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-gray-900">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-gray-900">{{ $user->phone ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bergabung Sejak</label>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-gray-900">{{ $user->created_at->format('d F Y') }}</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('account.settings') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Profil
                </a>
            </div>

            {{-- Statistik Pesanan --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Statistik Pesanan</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg border border-indigo-200">
                        <div class="text-2xl font-bold text-indigo-700">{{ $orderStats['total'] }}</div>
                        <div class="text-sm text-indigo-600 mt-1">Total Pesanan</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg border border-amber-200">
                        <div class="text-2xl font-bold text-amber-700">{{ $paymentStats['pending'] }}</div>
                        <div class="text-sm text-amber-600 mt-1">Menunggu Bayar</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                        <div class="text-2xl font-bold text-blue-700">{{ $orderStats['processing'] }}</div>
                        <div class="text-sm text-blue-600 mt-1">Diproses</div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg border border-emerald-200">
                        <div class="text-2xl font-bold text-emerald-700">{{ $orderStats['delivered'] }}</div>
                        <div class="text-sm text-emerald-600 mt-1">Selesai</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pesanan Terbaru --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Pesanan Terbaru</h2>
                <a href="{{ route('account.transactions.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                    Lihat Semua →
                </a>
            </div>

            @if ($recentOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">No. Pesanan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Pembayaran</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($recentOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        #{{ $order->order_number }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $order->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
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
                                            $color = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700';
                                            $label = $statusLabels[$order->status] ?? ucfirst($order->status);
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $paymentColors = [
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'paid' => 'bg-emerald-100 text-emerald-700',
                                                'failed' => 'bg-red-100 text-red-700',
                                                'expired' => 'bg-gray-100 text-gray-700',
                                                'cancelled' => 'bg-red-100 text-red-700',
                                            ];
                                            $paymentLabels = [
                                                'pending' => 'Menunggu',
                                                'paid' => 'Lunas',
                                                'failed' => 'Gagal',
                                                'expired' => 'Kedaluwarsa',
                                                'cancelled' => 'Dibatalkan',
                                            ];
                                            $paymentColor =
                                                $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-700';
                                            $paymentLabel =
                                                $paymentLabels[$order->payment_status] ??
                                                ucfirst($order->payment_status);
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentColor }}">
                                            {{ $paymentLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('account.transactions.show', $order->id) }}"
                                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-600">Belum ada pesanan</p>
                    <a href="{{ route('products.index') }}"
                        class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        Mulai Belanja →
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
