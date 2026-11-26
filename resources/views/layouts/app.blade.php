<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Jadwal &amp; Kalender Pelatihan — PPSDMK Kemnaker Corpu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">

    @livewireStyles
</head>
<body>
    {{ $slot ?? '' }}
    @yield('content')

    @livewireScripts
    <script src="{{ asset('js/clock.js') }}?v={{ filemtime(public_path('js/clock.js')) }}"></script>
</body>
</html>
