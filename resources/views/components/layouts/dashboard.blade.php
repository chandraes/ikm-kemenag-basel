<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('nama_instansi', 'Dasbor IKM') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
{{-- PERUBAHAN: Kelas untuk mengunci layout ke satu layar --}}
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 h-screen overflow-hidden">
    <main class="h-full">
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
