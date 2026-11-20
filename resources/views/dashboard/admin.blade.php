<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrator - TEPO</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">

    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo TEPO" width="36" height="36" class="w-9 h-9 rounded-full object-cover border border-slate-300">
                <div>
                    <span class="font-bold text-slate-900 tracking-tight block leading-none">TEPO - Panel Administrator</span>
                    <span class="text-[11px] text-slate-500">Manajemen Pustaka Koping Klinis</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-semibold text-slate-700 hover:text-rose-700 border border-slate-300 bg-white cursor-pointer">
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <span class="text-xs text-slate-500 font-medium block">Total Pasien Terdaftar</span>
                <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">{{ $totalPatients }}</span>
            </div>
            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <span class="text-xs text-slate-500 font-medium block">Total Pendamping Sebaya (DEMO)</span>
                <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">{{ $totalCounselors }}</span>
            </div>
            <div class="bg-white p-5 rounded-lg border border-slate-200">
                <span class="text-xs text-slate-500 font-medium block">Total Pustaka Rekomendasi Koping</span>
                <span class="text-2xl font-bold text-slate-900 tabular-nums mt-1 block">{{ $recommendations->count() }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <section class="lg:col-span-5 bg-white rounded-lg border border-slate-200 p-6">
                <h2 class="text-base font-bold text-slate-900 mb-4">Tambah Rekomendasi Koping Baru</h2>
                <form method="POST" action="{{ route('admin.recommendations.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="phase" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Fase Target</label>
                        <select name="phase" id="phase" required class="w-full px-3 py-2 rounded border border-slate-300 text-sm bg-white">
                            <option value="manic">Fase Tinggi / Manik (+2 s/d +3)</option>
                            <option value="depressive">Fase Rendah / Depresi (-2 s/d -3)</option>
                        </select>
                    </div>
                    <div>
                        <label for="type" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kategori</label>
                        <select name="type" id="type" required class="w-full px-3 py-2 rounded border border-slate-300 text-sm bg-white">
                            <option value="activity">Aktivitas Terarah</option>
                            <option value="music">Audio / Relaksasi</option>
                            <option value="video">Edukasi Visual</option>
                        </select>
                    </div>
                    <div>
                        <label for="title" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Judul Rekomendasi</label>
                        <input type="text" name="title" id="title" required class="w-full px-3 py-2 rounded border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Deskripsi Panduan</label>
                        <textarea name="description" id="description" rows="3" required class="w-full px-3 py-2 rounded border border-slate-300 text-sm"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm cursor-pointer">
                        Simpan Rekomendasi
                    </button>
                </form>
            </section>

            <section class="lg:col-span-7 bg-white rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-base font-bold text-slate-900">Daftar Pustaka Koping Aktif</h2>
                </div>
                <div class="divide-y divide-slate-200">
                    @foreach ($recommendations as $rec)
                        <div class="p-4 flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded {{ $rec->phase === 'manic' ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-900' }}">
                                    {{ $rec->phase === 'manic' ? 'Fase Tinggi / Manik' : 'Fase Rendah / Depresi' }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 mt-1">{{ $rec->title }}</h3>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $rec->description }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.recommendations.destroy', $rec->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-700 hover:text-rose-900 underline cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

    </main>
</body>
</html>
