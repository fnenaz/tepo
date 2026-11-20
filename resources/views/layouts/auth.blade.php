<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Autentikasi') - TEPO (Teman Bipolar)</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-slate-50 text-slate-900 antialiased font-sans">

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Kolom Kiri: Identitas Klinis TEPO -->
        <aside class="w-full lg:w-5/12 bg-slate-900 text-slate-100 flex flex-col justify-between p-8 sm:p-12 border-b lg:border-b-0 lg:border-r border-slate-800">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 focus:outline-none focus:ring-2 focus:ring-teal-500 rounded-lg">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo TEPO" width="40" height="40" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white block">TEPO</span>
                        <span class="text-xs text-slate-400 font-medium block">Teman Bipolar</span>
                    </div>
                </a>
                <span class="text-xs font-medium text-slate-400 border border-slate-800 bg-slate-800/60 px-3 py-1 rounded-md">
                    Sistem Pemantauan Mandiri
                </span>
            </div>

            <div class="my-auto py-10">
                <div class="mb-8 flex justify-center lg:justify-start">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Ilustrasi Spektrum Suasana Hati TEPO"
                        width="200"
                        height="200"
                        fetchpriority="high"
                        class="w-44 h-44 sm:w-52 sm:h-52 rounded-2xl object-cover border border-slate-700"
                    >
                </div>

                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white leading-snug max-w-md">
                    Pencatatan Terstruktur untuk Kestabilan Suasana Hati
                </h2>
                <p class="mt-3 text-sm text-slate-300 max-w-md leading-relaxed">
                    Catat fluktuasi mood harian pada skala klinis (-3 hingga +3), tingkat energi (1 hingga 5), jam tidur, serta jadwal masing-masing resep obat secara akurat.
                </p>

                <dl class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-lg border-t border-slate-800 pt-6">
                    <div>
                        <dt class="text-xs font-semibold text-teal-400 uppercase tracking-wider">Multi-Mood</dt>
                        <dd class="text-xs text-slate-300 mt-1">Pencatatan pagi, siang, sore, dan malam.</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-teal-400 uppercase tracking-wider">Multi-Resep</dt>
                        <dd class="text-xs text-slate-300 mt-1">Kelola lebih dari satu jenis obat beserta resepnya.</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-teal-400 uppercase tracking-wider">Anonim Dua Arah</dt>
                        <dd class="text-xs text-slate-300 mt-1">Konsultasi sebaya dengan identitas rahasia.</dd>
                    </div>
                </dl>
            </div>

            <div class="text-xs text-slate-400 flex justify-between items-center border-t border-slate-800 pt-4">
                <span>&copy; {{ date('Y') }} TEPO</span>
                <span>Rekam Pemantauan Harian Bipolar</span>
            </div>
        </aside>

        <!-- Kolom Kanan: Area Formulir -->
        <main class="w-full lg:w-7/12 flex items-center justify-center p-6 sm:p-12 bg-white overflow-y-auto">
            <div class="w-full max-w-md">

                @if (session('success'))
                    <div class="mb-5 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-5 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm font-medium" role="alert">
                        {{ session('warning') }}
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-5 p-4 rounded-lg bg-slate-100 border border-slate-200 text-slate-800 text-sm font-medium" role="status">
                        {{ session('info') }}
                    </div>
                @endif

                @yield('content')

            </div>
        </main>

    </div>

    @yield('scripts')
</body>
</html>
