<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Akun Saya | ' . config('app.name', 'LKBoard'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    {{-- Navbar utama --}}
    @include('components.navbar')

    @php
        $user = auth()->user();
        $orders = $user ? $user->orders()->get() : collect();
        $orderStats = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'shipped' => $orders->where('status', 'shipped')->count(),
            'delivered' => $orders->where('status', 'delivered')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
        ];
        $paymentPending = $orders->where('payment_status', 'pending')->count();
    @endphp

    <div class="container mx-auto flex gap-6 pt-20 pb-6">
        {{-- Sidebar Menu Fixed --}}
        <aside class="hidden lg:block w-[280px] shrink-0">
            <div class="sticky top-20 space-y-4 max-h-[calc(100vh-5rem)] overflow-y-auto pb-4">
                {{-- Informasi User --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-col items-center text-center mb-6">
                        <div
                            class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-2xl mb-3">
                            @if ($user && $user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                                    class="h-20 w-20 rounded-full object-cover">
                            @else
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-900 text-lg">{{ $user->name ?? 'User' }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $user->email ?? '' }}</p>
                    </div>

                    @if ($user && $user->phone)
                        <div class="text-sm text-gray-600 text-center pb-4 border-b border-gray-100">
                            <span class="font-medium">📱</span> {{ $user->phone }}
                        </div>
                    @endif

                    {{-- Menu Navigasi --}}
                    <nav class="space-y-2 mt-4">
                        <a href="{{ route('account.profile') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg transition @if (request()->routeIs('account.profile')) bg-indigo-600 text-white shadow-md @else text-gray-700 hover:bg-gray-100 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="font-medium">Profile</span>
                        </a>

                        <a href="{{ route('account.transactions.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg transition @if (request()->routeIs('account.transactions.*') || request()->routeIs('account.index')) bg-indigo-600 text-white shadow-md @else text-gray-700 hover:bg-gray-100 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="font-medium">Pesanan</span>
                        </a>

                        <a href="{{ route('account.address.index') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg transition @if (request()->routeIs('account.address.*')) bg-indigo-600 text-white shadow-md @else text-gray-700 hover:bg-gray-100 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="font-medium">Alamat</span>
                        </a>

                        <a href="{{ route('account.settings') }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg transition @if (request()->routeIs('account.settings')) bg-indigo-600 text-white shadow-md @else text-gray-700 hover:bg-gray-100 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="font-medium">Pengaturan</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="font-medium">Keluar</span>
                            </button>
                        </form>
                    </nav>
                </div>


            </div>
        </aside>

        {{-- Konten Utama --}}
        <main class="flex-1 min-w-0">
            <div class="space-y-4">
                {{-- Breadcrumb opsional --}}
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    @includeWhen(View::exists('components.breadcrumb'), 'components.breadcrumb')
                @endif

                {{-- Alert --}}
                @includeWhen(session('status') || session('error') || $errors->any(), 'components.alert')

                {{-- Konten dari setiap halaman --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @include('components.footer')
    @include('components.mobile-menu')

    @stack('scripts')
</body>

</html>
