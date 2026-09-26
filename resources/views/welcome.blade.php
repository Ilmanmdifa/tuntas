<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tuntas — Kerja lebih rapi, bareng tim</title>
    <meta name="description" content="Tuntas — catat tugas pribadi, bagi kerjaan tim, dan lihat progress dalam satu tempat.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800">
    <header class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <x-application-logo class="block h-8 w-8" />
            <span class="font-bold text-xl tracking-tight">Tuntas</span>
        </div>
        <nav class="flex items-center gap-2">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-gray-900 text-white rounded text-sm transition-colors hover:bg-black focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-black">Dashboard</a>
                <a href="{{ url('/todos') }}" class="px-4 py-2 bg-white border rounded text-sm transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Tasks</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 bg-white border rounded text-sm transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Masuk</a>
                <a href="{{ route('register') }}" class="px-5 py-2 bg-gray-900 text-white rounded text-sm transition-colors hover:bg-black focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800 active:bg-black">Coba gratis</a>
            @endauth
        </nav>
    </header>

    <main class="max-w-7xl mx-auto px-6">
        {{-- Hero jualan --}}
        <div class="bg-white shadow-sm rounded-2xl p-8 md:p-14 mt-8">
            <div class="max-w-3xl">
                <p class="text-sm font-medium text-gray-500 mb-3">Simple · Cepat · Kolaboratif</p>
                <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-4">Kerjaan berantakan?<br>Bikin jadi beres.</h1>
                <p class="text-lg text-gray-600 mb-8">Catat tugas pribadi, bagi kerjaan tim, dan lihat progress dalam satu tempat. Tanpa ribet, langsung pakai.</p>

                @guest
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="px-7 py-3 bg-black text-white rounded-lg font-medium transition-colors hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800">Mulai gratis — Daftar</a>
                        <a href="{{ route('login') }}" class="px-7 py-3 bg-white border rounded-lg font-medium transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Sudah punya akun? Masuk</a>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Gratis, tanpa kartu kredit. Daftar 30 detik.</p>
                @else
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ url('/todos') }}" class="px-7 py-3 bg-black text-white rounded-lg font-medium transition-colors hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-800">Buka Tasks →</a>
                        <a href="{{ url('/teams') }}" class="px-7 py-3 bg-white border rounded-lg font-medium transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 active:bg-gray-100">Kelola Team</a>
                    </div>
                @endguest
            </div>

            {{-- 3 benefit jualan, bukan spek teknis --}}
            <div class="grid md:grid-cols-3 gap-4 mt-10 pt-8 border-t">
                <div>
                    <div class="w-10 h-10 rounded bg-gray-900 text-white flex items-center justify-center mb-3">✓</div>
                    <h3 class="font-semibold mb-1">Pribadi tetap pribadi</h3>
                    <p class="text-sm text-gray-600">Tugasmu hanya kamu yang lihat. Rapi, tidak campur dengan kerjaan orang.</p>
                </div>
                <div>
                    <div class="w-10 h-10 rounded bg-gray-900 text-white flex items-center justify-center mb-3">◯</div>
                    <h3 class="font-semibold mb-1">Kerja bareng tim lancar</h3>
                    <p class="text-sm text-gray-600">Buat team, undang teman, bagi tugas — semua update langsung terlihat.</p>
                </div>
                <div>
                    <div class="w-10 h-10 rounded bg-gray-900 text-white flex items-center justify-center mb-3">⌕</div>
                    <h3 class="font-semibold mb-1">Cari & atur cepat</h3>
                    <p class="text-sm text-gray-600">Cari judul dan deskripsi, filter overdue, atur prioritas dan deadline.</p>
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-gray-400 mt-6">© {{ date('Y') }} Tuntas · <a href="{{ url('/todos') }}" class="underline">Tasks</a> · <a href="{{ url('/teams') }}" class="underline">Teams</a></p>
    </main>
</body>
</html>
