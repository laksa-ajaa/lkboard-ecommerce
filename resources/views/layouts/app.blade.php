<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'LKBoard'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-[#f9f5ee] text-gray-900" 
    x-data="{ 
        showLoginModal: false,
        isAuthenticated: {{ auth()->check() ? 'true' : 'false' }}
    }">
    {{-- Navbar --}}
    @include('components.navbar')
    
    {{-- Global Login Modal --}}
    @include('components.login-modal')

    {{-- Flash message / alert global --}}
    <div class="container mx-auto px-4 mt-4">
        @includeWhen(session('status') || session('error') || $errors->any(), 'components.alert')
    </div>

    {{-- Main content --}}
    <main class="container mx-auto px-4 py-6 pt-20">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Mobile menu --}}
    @include('components.mobile-menu')

    @stack('scripts')
</body>

</html>
