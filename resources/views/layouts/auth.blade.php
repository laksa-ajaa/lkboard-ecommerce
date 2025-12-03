<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentication' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-linear-to-br from-slate-950 via-slate-900 to-indigo-950">

    {{-- Toast notification --}}
    @includeWhen(session('status') || session('error') || $errors->any(), 'components.alert')

    <div class="min-h-screen flex items-center justify-center px-4">
        @yield('content')
    </div>

</body>

</html>
