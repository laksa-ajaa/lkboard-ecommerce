@extends('layouts.app')

@section('title', 'Pembayaran Gagal – ' . config('app.name'))

@section('content')
    <div class="container mx-auto max-w-4xl px-4 sm:px-6 lg:px-0">
        {{-- Failed Message --}}
        <div class="mx-auto max-w-2xl text-center">
            {{-- Failed Icon --}}
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-rose-100">
                <svg class="h-10 w-10 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <h1 class="mb-4 text-3xl font-extrabold text-slate-900">
                @if (session('expired'))
                    Pembayaran Kadaluarsa
                @else
                    Pembayaran Gagal
                @endif
            </h1>
            <p class="mb-8 text-lg text-slate-600">
                @if (session('expired'))
                    Kami tidak menerima pembayaran tepat waktu. Silakan buat pesanan baru untuk melanjutkan.
                @else
                    Terjadi kesalahan saat memproses pembayaran Anda. Silakan coba lagi atau gunakan metode pembayaran lain.
                @endif
            </p>

            {{-- Order Info Card --}}
            @if (session('order_number'))
                <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between border-b border-slate-200 pb-4">
                        <span class="text-sm font-medium text-slate-600">Nomor Pesanan</span>
                        <span class="text-lg font-bold text-slate-900">#{{ session('order_number') }}</span>
                    </div>

                    <div class="space-y-3 text-left">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Status</span>
                            @php
                                $paymentStatus = session('payment_status', session('expired') ? 'expired' : 'failed');
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
                                $statusColor = $paymentColors[$paymentStatus] ?? 'bg-red-100 text-red-700';
                                $statusLabel = $paymentLabels[$paymentStatus] ?? ucfirst($paymentStatus);
                            @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        @if (session('order_total'))
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">Total Pembayaran</span>
                                <span class="text-lg font-bold text-slate-900">
                                    Rp {{ number_format(session('order_total'), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Reason Message --}}
            <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <div class="flex items-start gap-3">
                    <svg class="h-6 w-6 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="text-left">
                        <h3 class="font-semibold text-amber-900">
                            @if (session('expired'))
                                Mengapa Pembayaran Kadaluarsa?
                            @else
                                Mengapa Pembayaran Gagal?
                            @endif
                        </h3>
                        <p class="mt-2 text-sm text-amber-800">
                            @if (session('expired'))
                                Transaksi pembayaran memiliki batas waktu. Jika pembayaran tidak dilakukan dalam waktu yang
                                ditentukan, transaksi akan otomatis kadaluarsa. Silakan buat pesanan baru dan selesaikan
                                pembayaran sebelum batas waktu habis.
                            @else
                                Pembayaran Anda gagal diproses. Hal ini bisa terjadi karena beberapa alasan, seperti saldo
                                tidak mencukupi, kartu kredit ditolak, atau masalah teknis. Silakan coba lagi dengan metode
                                pembayaran lain atau hubungi bank/kartu Anda untuk informasi lebih lanjut.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Next Steps --}}
            <div class="mb-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Langkah Selanjutnya</h2>
                <div class="space-y-4 text-left">
                    @if (session('expired'))
                        <div class="flex gap-4">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                1
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">Buat Pesanan Baru</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    Pesanan lama sudah kadaluarsa. Silakan buat pesanan baru dari keranjang belanja Anda.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                2
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">Selesaikan Pembayaran</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    Setelah membuat pesanan baru, segera selesaikan pembayaran sebelum batas waktu habis.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="flex gap-4">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                1
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">Coba Metode Pembayaran Lain</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    Gunakan metode pembayaran yang berbeda, seperti Virtual Account, E-Wallet, atau Kartu
                                    Kredit lainnya.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                2
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">Periksa Saldo atau Limit Kartu</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    Pastikan saldo atau limit kartu Anda mencukupi untuk melakukan pembayaran.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600">
                                3
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">Hubungi Customer Service</h3>
                                <p class="mt-1 text-sm text-slate-600">
                                    Jika masalah masih terjadi, silakan hubungi customer service kami untuk bantuan lebih
                                    lanjut.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                @if (session('expired'))
                    <a href="{{ route('cart.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-indigo-500 bg-white px-6 py-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                        Kembali ke Keranjang
                    </a>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                        Lanjutkan Belanja
                    </a>
                @else
                    <a href="{{ route('cart.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-indigo-500 bg-white px-6 py-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                        Kembali ke Keranjang
                    </a>
                    <a href="{{ route('account.transactions.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-indigo-500 bg-white px-6 py-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                        Lihat Pesanan Saya
                    </a>
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-400 transition-colors shadow-sm shadow-indigo-500/40">
                        Lanjutkan Belanja
                    </a>
                @endif
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
