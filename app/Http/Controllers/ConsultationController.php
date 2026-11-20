<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    /**
     * Halaman daftar konsultasi anonim dua arah untuk Pasien (DEMO).
     */
    public function indexPatient(): View
    {
        $user = Auth::user();

        $this->ensureDemoCounselorsExist();

        $counselors = User::where('role', 'counselor')->get();

        $myConsultations = Consultation::with(['counselor', 'chatMessages'])
            ->where('patient_id', $user->id)
            ->latest()
            ->get();

        return view('patient.consultations', [
            'user' => $user,
            'counselors' => $counselors,
            'myConsultations' => $myConsultations,
        ]);
    }

    /**
     * Pasien memulai sesi konsultasi anonim dengan Mahasiswa Psikologi (bisa pilih kode anonim atau acak otomatis).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureDemoCounselorsExist();

        $validated = $request->validate([
            'counselor_id' => ['nullable', 'integer'],
            'notes' => ['required', 'string', 'max:1000'],
        ], [
            'notes.required' => 'Tuliskan cerita atau keluhan yang ingin Anda sampaikan secara anonim.',
        ]);

        // Jika tidak memilih pendamping spesifik atau memilih penugasan acak rahasia, pilihkan otomatis
        $counselorId = $validated['counselor_id'] ?? null;
        if (! $counselorId || ! User::where('id', $counselorId)->where('role', 'counselor')->exists()) {
            $counselorId = User::where('role', 'counselor')->inRandomOrder()->value('id');
        }

        $consultation = Consultation::create([
            'patient_id' => Auth::id(),
            'counselor_id' => $counselorId,
            'status' => 'active',
            'scheduled_at' => now(),
            'notes' => trim($validated['notes']),
        ]);

        ChatMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => Auth::id(),
            'message' => trim($validated['notes']),
            'is_read' => false,
        ]);

        return redirect()->route('consultations.show', $consultation->id)
            ->with('success', 'Ruang konsultasi anonim berhasil dibuka. Identitas Anda dan pendamping dirahasiakan sepenuhnya.');
    }

    /**
     * Ruang obrolan (Double-Blind Anonymous Chat Room) antara Pasien dan Mahasiswa Psikologi.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $consultation = Consultation::with(['patient', 'counselor', 'chatMessages.sender'])
            ->findOrFail($id);

        if ($user->role !== 'admin' && $consultation->patient_id !== $user->id && $consultation->counselor_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke ruang konsultasi ini.');
        }

        ChatMessage::where('consultation_id', $consultation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('consultations.chat', [
            'user' => $user,
            'consultation' => $consultation,
            'messages' => $consultation->chatMessages,
        ]);
    }

    /**
     * Mengirim pesan baru di dalam Ruang Obrolan Anonim.
     */
    public function sendMessage(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $consultation = Consultation::findOrFail($id);

        if ($user->role !== 'admin' && $consultation->patient_id !== $user->id && $consultation->counselor_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
        ]);

        ChatMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $user->id,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        return redirect()->route('consultations.show', $consultation->id);
    }

    /**
     * Mengubah status sesi konsultasi.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $consultation = Consultation::findOrFail($id);

        if ($user->role !== 'admin' && $consultation->patient_id !== $user->id && $consultation->counselor_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'completed', 'cancelled'])],
        ]);

        $consultation->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status sesi konsultasi berhasil diperbarui.');
    }

    protected function ensureDemoCounselorsExist(): void
    {
        if (User::where('role', 'counselor')->count() === 0) {
            User::create([
                'name' => 'Alya Rahmawati',
                'email' => 'konselor1@tepo.test',
                'password' => Hash::make('password123'),
                'role' => 'counselor',
                'phone' => '081298765432',
                'bio' => 'Fokus Pendampingan: Regulasi emosi, manajemen fase manik-depresi, dan penataan rutinitas tidur harian.',
            ]);

            User::create([
                'name' => 'Bagas Pratama',
                'email' => 'konselor2@tepo.test',
                'password' => Hash::make('password123'),
                'role' => 'counselor',
                'phone' => '081345678901',
                'bio' => 'Fokus Pendampingan: Konseling sebaya empatik, pengelolaan kecemasan sosial, dan kepatuhan terapi.',
            ]);
        }
    }
}
