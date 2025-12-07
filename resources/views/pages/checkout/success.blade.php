@extends('layouts.app')

@section('title', 'Pesanan Berhasil – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-0">
        {{-- Success Message --}}
        <div class="mx-auto max-w-2xl text-center">
            {{-- Success Icon --}}
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-10 w-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mb-4 text-3xl font-extrabold text-slate-900">Pesanan Berhasil!</h1>
            <p class="mb-8 text-lg text-slate-600">
                Terima kasih atas pembelian Anda. Pesanan Anda telah berhasil dibuat dan akan segera kami proses.
            </p>

            {{-- Order Info Card --}}
            <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-4">
                    <span class="text-sm font-medium text-slate-600">Nomor Pesanan</span>
                    <span
                        class="text-lg font-bold text-slate-900">#{{ session('order_number', 'ORD-' . strtoupper(uniqid())) }}</span>
                </div>

                <div class="space-y-3 text-left">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Status</span>
                        @php
                            $paymentStatus = session('payment_status', 'pending');
                            $paymentColors = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'paid' => 'bg-emerald-100 text-emerald-700',
                                'failed' => 'bg-red-100 text-red-700',
                                'expired' => 'bg-gray-100 text-gray-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'challenge' => 'bg-yellow-100 text-yellow-700',
                            ];
                            $paymentLabels = [
                                'pending' => 'Menunggu Pembayaran',
                                'paid' => 'Lunas',
                                'failed' => 'Gagal',
                                'expired' => 'Kedaluwarsa',
                                'cancelled' => 'Dibatalkan',
                                'challenge' => 'Menunggu Verifikasi',
                            ];
                            $statusColor = $paymentColors[$paymentStatus] ?? 'bg-gray-100 text-gray-700';
                            $statusLabel = $paymentLabels[$paymentStatus] ?? ucfirst($paymentStatus);
                        @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Total Pembayaran</span>
                        <span class="text-lg font-bold text-indigo-600">
                            Rp {{ number_format(session('order_total', 0), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('account.transactions.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-indigo-500 bg-white px-6 py-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                    Lihat Detail Pesanan
                </a>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                    Lanjutkan Belanja
                </a>
            </div>

            {{-- Support Info --}}
            <div class="mt-8 rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-600">
                    Butuh bantuan? Hubungi kami di
                    <a href="mailto:support@lkboard.com" class="font-semibold text-indigo-600 hover:text-indigo-700">
                        support@lkboard.com
                    </a>
                    atau melalui WhatsApp
                </p>
            </div>
        </div>
    </div>
@endsection
