<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Akun Saya | ' . config('app.name', 'LKBoard'))</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    {{-- Navbar utama --}}
    @include('components.navbar')

    <div class="container mx-auto px-4 py-6 grid gap-6 lg:grid-cols-[260px,1fr]">
        {{-- Sidebar akun --}}
        <aside class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 h-fit">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">
                Area Akun
            </h2>
            <nav class="space-y-1 text-sm">
                <a href="{{ route('account.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 @if(request()->routeIs('account.index')) bg-gray-100 font-semibold @endif">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('account.transactions.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 @if(request()->routeIs('account.transactions.*')) bg-gray-100 font-semibold @endif">
                    <span>Transaksi</span>
                </a>
                <a href="{{ route('account.address.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 @if(request()->routeIs('account.address.*')) bg-gray-100 font-semibold @endif">
                    <span>Alamat</span>
                </a>
                <a href="{{ route('account.settings') }}" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 @if(request()->routeIs('account.settings')) bg-gray-100 font-semibold @endif">
                    <span>Pengaturan</span>
                </a>
            </nav>
        </aside>

        {{-- Konten akun --}}
        <section class="space-y-4">
            {{-- Breadcrumb opsional --}}
            @hasSection('breadcrumb')
                @yield('breadcrumb')
            @else
                @includeWhen(View::exists('components.breadcrumb'), 'components.breadcrumb')
            @endif

            {{-- Alert --}}
            @includeWhen(session('status') || session('error') || $errors->any(), 'components.alert')

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                @yield('content')
            </div>
        </section>
    </div>

    @include('components.footer')
    @include('components.mobile-menu')

    @stack('scripts')
</body>
</html>
