<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Obrolan Anonim Dua Arah - TEPO</title>
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

    @php
        $isPatientViewer = $user->id === $consultation->patient_id;
        $myAnonymousAlias = $isPatientViewer
            ? $consultation->patient->anonymous_patient_code
            : ($consultation->counselor ? $consultation->counselor->anonymous_counselor_code : 'Pendamping Anonim');

        $partnerAnonymousAlias = $isPatientViewer
            ? ($consultation->counselor ? $consultation->counselor->anonymous_counselor_code : 'Pendamping Sebaya Anonim')
            : ($consultation->patient ? $consultation->patient->anonymous_patient_code : 'Pasien Anonim');

        $backUrl = $user->role === 'counselor' ? route('counselor.dashboard') : route('patient.consultations');
    @endphp

    <!-- Header Ruang Obrolan Anonim -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ $backUrl }}" class="px-3 py-1.5 rounded-md border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    Kembali
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm font-bold font-mono text-slate-900">{{ $partnerAnonymousAlias }}</h1>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded {{ $consultation->status === 'active' ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-700' }}">
                            {{ $consultation->status === 'active' ? 'Sesi Aktif' : 'Selesai' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Identitas Anda di ruang ini: <span class="font-mono font-semibold text-slate-700">{{ $myAnonymousAlias }}</span> (Nama asli dirahasiakan)
                    </p>
                </div>
            </div>

            @if ($consultation->status === 'active')
                <form method="POST" action="{{ route('consultations.status.update', $consultation->id) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-3 py-1.5 rounded-md border border-slate-300 hover:border-rose-300 text-xs font-semibold text-slate-700 hover:text-rose-700 bg-white cursor-pointer">
                        Akhiri Sesi
                    </button>
                </form>
            @endif
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-6 flex-1 w-full flex flex-col gap-4">

        <!-- Pemberitahuan Anonimitas Dua Arah -->
        <div class="p-3.5 rounded-lg bg-slate-100 border border-slate-200 text-xs text-slate-700 leading-relaxed">
            <strong class="text-slate-900">Kerahasiaan Terjaga (Double-Blind Anonymity):</strong>
            Baik pasien maupun mahasiswa psikologi pendamping tidak dapat melihat nama, email, atau identitas pribadi satu sama lain. Silakan berdiskusi secara terbuka dan jujur.
        </div>

        <!-- Area Pesan -->
        <div class="bg-white rounded-lg border border-slate-200 p-5 flex-1 overflow-y-auto space-y-4 min-h-[420px] max-h-[60vh]" id="chat-container">
            @forelse ($messages as $msg)
                @php
                    $isMe = $msg->sender_id === $user->id;
                    $senderAlias = $msg->sender_id === $consultation->patient_id
                        ? $consultation->patient->anonymous_patient_code
                        : ($consultation->counselor ? $consultation->counselor->anonymous_counselor_code : 'Pendamping Anonim');
                @endphp
                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[11px] font-bold font-mono text-slate-600">
                            {{ $isMe ? 'Anda (' . $senderAlias . ')' : $senderAlias }}
                        </span>
                        <span class="text-[11px] text-slate-400 tabular-nums">
                            {{ $msg->created_at->format('d M H:i') }}
                        </span>
                    </div>
                    <div class="max-w-xl px-4 py-3 rounded-lg text-sm leading-relaxed {{ $isMe ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-900 border border-slate-200' }}">
                        {{ $msg->message }}
                    </div>
                </div>
            @empty
                <p class="text-center text-xs text-slate-500 py-10">Belum ada pesan dalam sesi ini.</p>
            @endforelse
        </div>

        <!-- Form Kirim Pesan -->
        @if ($consultation->status === 'active')
            <form method="POST" action="{{ route('consultations.messages.store', $consultation->id) }}" class="bg-white rounded-lg border border-slate-200 p-4 flex flex-col sm:flex-row gap-3">
                @csrf
                <label for="message" class="sr-only">Tulis Pesan</label>
                <textarea
                    name="message"
                    id="message"
                    rows="2"
                    required
                    placeholder="Tulis tanggapan atau cerita Anda secara anonim..."
                    class="flex-1 px-3.5 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700"
                ></textarea>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white font-semibold text-sm self-end sm:self-stretch cursor-pointer"
                >
                    Kirim Pesan
                </button>
            </form>
        @else
            <div class="p-4 rounded-lg bg-slate-100 border border-slate-200 text-center text-xs text-slate-600">
                Sesi konsultasi anonim ini telah selesai.
            </div>
        @endif

    </main>

    <script>
        const chatBox = document.getElementById('chat-container');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>
</body>
</html>
