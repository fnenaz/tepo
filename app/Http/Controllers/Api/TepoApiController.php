<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Consultation;
use App\Models\CopingRecommendation;
use App\Models\DailyEntry;
use App\Models\MedicationLog;
use App\Models\MoodRecommendation;
use App\Models\Prescription;
use App\Models\SleepLog;
use App\Models\User;
use App\Models\WeeklyEvaluation;
use Carbon\Carbon;
use Database\Seeders\CopingRecommendationSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TepoApiController extends Controller
{
    /**
     * Registrasi akun baru (patient atau counselor).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(['patient', 'counselor'])],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => isset($validated['phone']) ? trim($validated['phone']) : null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->hasSession()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi akun berhasil.',
            'data' => [
                'user' => $this->formatUser($user),
                'anonymous_code' => $this->resolveAnonymousCode($user),
                'token' => Crypt::encryptString((string) $user->id),
            ],
        ], 201);
    }

    /**
     * Login pengguna dan kembalikan Bearer token + session cookie.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat email atau kata sandi yang dimasukkan salah.',
            ], 401);
        }

        if ($request->hasSession()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $this->formatUser($user),
                'anonymous_code' => $this->resolveAnonymousCode($user),
                'token' => Crypt::encryptString((string) $user->id),
            ],
        ]);
    }

    /**
     * Profil pengguna yang sedang aktif.
     */
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $this->formatUser($user),
                'anonymous_code' => $this->resolveAnonymousCode($user),
            ],
        ]);
    }

    /**
     * Logout sesi pengguna.
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil keluar dari sesi.',
        ]);
    }

    /**
     * Dashboard pasien: ringkasan klinis 14 hari, mood hari ini, tidur, resep, dan log obat.
     */
    public function patientDashboard(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $this->ensureCopingSeeded();

        $todayMoods = DailyEntry::with('copingRecommendations')
            ->where('user_id', $user->id)
            ->whereDate('entry_date', $today)
            ->orderBy('entry_time', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn (DailyEntry $entry) => $this->formatDailyEntry($entry));

        $todaySleep = SleepLog::where('user_id', $user->id)
            ->whereDate('log_date', $today)
            ->first();

        $prescriptions = Prescription::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        $todayMeds = MedicationLog::where('user_id', $user->id)
            ->whereDate('log_date', $today)
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'today' => $today,
                'clinical_summary' => $this->buildClinicalSummary($user->id, 14),
                'today_moods' => $todayMoods,
                'today_sleep' => $todaySleep,
                'prescriptions' => $prescriptions,
                'today_meds' => $todayMeds,
            ],
        ]);
    }

    /**
     * Catat sesi mood baru (-3 s/d +3) & tingkat energi (1 s/d 5). Mendukung multi-mood per hari.
     */
    public function storeMood(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_date' => ['nullable', 'date'],
            'period' => ['required', 'string', Rule::in(['Pagi', 'Siang', 'Sore', 'Malam'])],
            'entry_time' => ['required', 'date_format:H:i'],
            'mood_level' => ['required', 'integer', 'between:-3,3'],
            'energy_level' => ['required', 'integer', 'between:1,5'],
            'daily_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->ensureCopingSeeded();

        $moodLevel = (int) $validated['mood_level'];
        $moodPhase = DailyEntry::mapMoodLevelToPhase($moodLevel);
        $moodScore10 = DailyEntry::mapMoodLevelToScore10($moodLevel);

        $dailyEntry = DailyEntry::create([
            'user_id' => Auth::id(),
            'entry_date' => $validated['entry_date'] ?? Carbon::today()->toDateString(),
            'entry_time' => $validated['entry_time'],
            'period' => $validated['period'],
            'mood_level' => $moodLevel,
            'energy_level' => (int) $validated['energy_level'],
            'mood' => $moodPhase,
            'mood_score' => $moodScore10,
            'daily_notes' => $validated['daily_notes'] ?? null,
        ]);

        if (in_array($moodPhase, ['manic', 'depressive'], true)) {
            $recommendations = CopingRecommendation::where('phase', $moodPhase)
                ->where('is_active', true)
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($recommendations as $rec) {
                MoodRecommendation::create([
                    'daily_entry_id' => $dailyEntry->id,
                    'coping_recommendation_id' => $rec->id,
                ]);
            }
        }

        $dailyEntry->load('copingRecommendations');

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan sesi suasana hati berhasil disimpan.',
            'data' => $this->formatDailyEntry($dailyEntry),
        ], 201);
    }

    /**
     * Hapus satu entri catatan mood milik pasien.
     */
    public function destroyMood(int $id): JsonResponse
    {
        $entry = DailyEntry::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $entry->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan sesi mood berhasil dihapus.',
        ]);
    }

    /**
     * Simpan atau perbarui catatan jam tidur harian.
     */
    public function storeSleep(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'log_date' => ['nullable', 'date'],
            'sleep_start' => ['nullable', 'date_format:H:i'],
            'sleep_end' => ['nullable', 'date_format:H:i'],
            'duration_hours' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'quality' => ['nullable', 'integer', 'between:1,5'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $sleepStart = $validated['sleep_start'] ?? '23:00';
        $sleepEnd = $validated['sleep_end'] ?? '06:30';

        if (! empty($validated['duration_hours'])) {
            $durationHours = round((float) $validated['duration_hours'], 1);
        } else {
            $start = Carbon::createFromFormat('H:i', $sleepStart);
            $end = Carbon::createFromFormat('H:i', $sleepEnd);
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }
            $durationHours = round($start->diffInMinutes($end) / 60, 1);
        }

        $userId = Auth::id();
        $logDate = $validated['log_date'] ?? Carbon::today()->toDateString();

        $sleepData = [
            'sleep_start' => $sleepStart,
            'sleep_end' => $sleepEnd,
            'duration_hours' => $durationHours,
            'quality' => $validated['quality'] ?? 3,
            'notes' => $validated['notes'] ?? null,
        ];

        $sleepLog = SleepLog::where('user_id', $userId)
            ->whereDate('log_date', $logDate)
            ->first();

        if ($sleepLog) {
            $sleepLog->update($sleepData);
            $statusCode = 200;
        } else {
            $sleepLog = SleepLog::create(array_merge([
                'user_id' => $userId,
                'log_date' => $logDate,
            ], $sleepData));
            $statusCode = 201;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan tidur berhasil disimpan.',
            'data' => $sleepLog,
        ], $statusCode);
    }

    /**
     * Daftar resep obat aktif milik pasien.
     */
    public function listPrescriptions(): JsonResponse
    {
        $prescriptions = Prescription::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $prescriptions,
        ]);
    }

    /**
     * Tambah resep obat baru dengan aturan pakai masing-masing.
     */
    public function storePrescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'string', 'max:255'],
            'prescribing_doctor' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $prescription = Prescription::create([
            'user_id' => Auth::id(),
            'medication_name' => trim($validated['medication_name']),
            'dosage' => trim($validated['dosage']),
            'frequency' => trim($validated['frequency']),
            'prescribing_doctor' => isset($validated['prescribing_doctor']) ? trim($validated['prescribing_doctor']) : null,
            'instructions' => isset($validated['instructions']) ? trim($validated['instructions']) : null,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Resep obat baru berhasil ditambahkan.',
            'data' => $prescription,
        ], 201);
    }

    /**
     * Hapus resep obat milik pasien.
     */
    public function destroyPrescription(int $id): JsonResponse
    {
        $prescription = Prescription::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $prescription->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Resep obat berhasil dihapus.',
        ]);
    }

    /**
     * Catat konsumsi obat harian (memilih prescription_id atau manual).
     */
    public function storeMedication(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prescription_id' => ['nullable', 'integer'],
            'log_date' => ['nullable', 'date'],
            'medication_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'prescription_rule' => ['nullable', 'string', 'max:255'],
            'quantity_taken' => ['required', 'integer', 'min:0', 'max:20'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'string', Rule::in(['on_time', 'missed', 'overdose'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();
        $prescription = null;

        if (! empty($validated['prescription_id'])) {
            $prescription = Prescription::where('user_id', $userId)
                ->where('id', $validated['prescription_id'])
                ->first();

            if (! $prescription) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resep obat tidak ditemukan untuk akun Anda.',
                ], 422);
            }
        }

        $medName = $prescription ? $prescription->medication_name : trim((string) ($validated['medication_name'] ?? ''));
        $dosage = $prescription ? $prescription->dosage : trim((string) ($validated['dosage'] ?? ''));
        $rule = $prescription ? $prescription->frequency : trim((string) ($validated['prescription_rule'] ?? 'Sesuai anjuran dokter'));

        if ($medName === '' || $dosage === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pilih prescription_id atau isi medication_name serta dosage secara manual.',
            ], 422);
        }

        $log = MedicationLog::create([
            'user_id' => $userId,
            'prescription_id' => $prescription?->id,
            'log_date' => $validated['log_date'] ?? Carbon::today()->toDateString(),
            'medication_name' => $medName,
            'dosage' => $dosage,
            'prescription_rule' => $rule,
            'quantity_taken' => $validated['status'] === 'missed' ? 0 : (int) $validated['quantity_taken'],
            'scheduled_time' => $validated['scheduled_time'] ?? now()->format('H:i'),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan konsumsi obat harian berhasil disimpan.',
            'data' => $log,
        ], 201);
    }

    /**
     * Hapus log konsumsi obat harian.
     */
    public function destroyMedication(int $id): JsonResponse
    {
        $log = MedicationLog::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $log->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan konsumsi obat berhasil dihapus.',
        ]);
    }

    /**
     * Laporan klinis 2 minggu (atau 7/30 hari) beserta ringkasan otomatis & evaluasi.
     */
    public function patientReports(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $days = (int) $request->query('days', 14);
        if (! in_array($days, [7, 14, 30], true)) {
            $days = 14;
        }

        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $moodEntries = DailyEntry::where('user_id', $userId)
            ->whereDate('entry_date', '>=', $startDate)
            ->whereDate('entry_date', '<=', $endDate)
            ->orderBy('entry_date', 'asc')
            ->orderBy('entry_time', 'asc')
            ->get();

        $sleepLogs = SleepLog::where('user_id', $userId)
            ->whereDate('log_date', '>=', $startDate)
            ->whereDate('log_date', '<=', $endDate)
            ->orderBy('log_date', 'asc')
            ->get();

        $medLogs = MedicationLog::where('user_id', $userId)
            ->whereDate('log_date', '>=', $startDate)
            ->whereDate('log_date', '<=', $endDate)
            ->orderBy('log_date', 'desc')
            ->orderBy('scheduled_time', 'asc')
            ->get();

        $moodsGrouped = $moodEntries->groupBy(fn ($item) => Carbon::parse($item->entry_date)->toDateString());
        $sleepByDate = $sleepLogs->keyBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());
        $medsGrouped = $medLogs->groupBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());

        $dayNamesId = [
            'Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel',
            'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab',
        ];

        $dailyRows = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateObj = Carbon::today()->subDays($i);
            $dateStr = $dateObj->toDateString();
            $dayMoods = $moodsGrouped->get($dateStr, collect());
            $daySleep = $sleepByDate->get($dateStr);
            $dayMeds = $medsGrouped->get($dateStr, collect());

            if ($dayMoods->isNotEmpty() || $daySleep || $dayMeds->isNotEmpty()) {
                $dailyRows[] = [
                    'day_name' => $dayNamesId[$dateObj->format('D')] ?? $dateObj->format('D'),
                    'date' => $dateStr,
                    'moods' => $dayMoods->map(fn (DailyEntry $m) => $this->formatDailyEntry($m))->values(),
                    'avg_mood_level' => $dayMoods->isNotEmpty() ? round($dayMoods->avg('mood_level'), 1) : null,
                    'avg_energy_level' => $dayMoods->isNotEmpty() ? round($dayMoods->avg('energy_level'), 1) : null,
                    'sleep_hours' => $daySleep ? (float) $daySleep->duration_hours : null,
                    'took_medication' => $dayMeds->where('status', 'on_time')->isNotEmpty(),
                    'medications' => $dayMeds->values(),
                ];
            }
        }

        $weeklyEval = $this->calculateWeeklyEvaluation($userId, $days);

        return response()->json([
            'status' => 'success',
            'data' => [
                'days' => $days,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'summary_metrics' => $this->buildClinicalSummary($userId, $days),
                'weekly_evaluation' => $weeklyEval,
                'med_stats' => [
                    'on_time' => $medLogs->where('status', 'on_time')->count(),
                    'missed' => $medLogs->where('status', 'missed')->count(),
                    'overdose' => $medLogs->where('status', 'overdose')->count(),
                ],
                'daily_rows' => $dailyRows,
            ],
        ]);
    }

    /**
     * Daftar sesi konsultasi anonim dua arah (tanpa nama/email/telepon lawan bicara).
     */
    public function listConsultations(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->ensureDemoCounselorsExist();

        $query = Consultation::with(['patient', 'counselor', 'chatMessages'])->latest();

        if ($user->isPatient()) {
            $query->where('patient_id', $user->id);
        } elseif ($user->isCounselor()) {
            $query->where('counselor_id', $user->id);
        }

        $consultations = $query->get()->map(fn (Consultation $c) => $this->formatConsultation($c, $user));

        $payload = [
            'your_anonymous_code' => $this->resolveAnonymousCode($user),
            'consultations' => $consultations,
        ];

        if ($user->isPatient()) {
            $payload['available_counselors'] = User::where('role', 'counselor')
                ->get()
                ->map(fn (User $c) => [
                    'id' => $c->id,
                    'anonymous_counselor_code' => $c->anonymous_counselor_code,
                    'focus' => $c->bio,
                ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payload,
        ]);
    }

    /**
     * Mulai sesi konsultasi anonim baru oleh pasien.
     */
    public function storeConsultation(Request $request): JsonResponse
    {
        $this->ensureDemoCounselorsExist();

        $validated = $request->validate([
            'counselor_id' => ['nullable', 'integer'],
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        $counselorId = $validated['counselor_id'] ?? null;
        if (! $counselorId || ! User::where('id', $counselorId)->where('role', 'counselor')->exists()) {
            $counselorId = User::where('role', 'counselor')->inRandomOrder()->value('id');
        }

        /** @var User $user */
        $user = Auth::user();

        $consultation = Consultation::create([
            'patient_id' => $user->id,
            'counselor_id' => $counselorId,
            'status' => 'active',
            'scheduled_at' => now(),
            'notes' => trim($validated['notes']),
        ]);

        ChatMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $user->id,
            'message' => trim($validated['notes']),
            'is_read' => false,
        ]);

        $consultation->load(['patient', 'counselor', 'chatMessages']);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi konsultasi anonim dua arah berhasil dibuka.',
            'data' => $this->formatConsultation($consultation, $user),
        ], 201);
    }

    /**
     * Detail sesi konsultasi dan pesan obrolan dengan identitas samaran dua arah.
     */
    public function showConsultation(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $consultation = Consultation::with(['patient', 'counselor', 'chatMessages'])->findOrFail($id);

        if (! $this->canAccessConsultation($user, $consultation)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke ruang konsultasi ini.',
            ], 403);
        }

        ChatMessage::where('consultation_id', $consultation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $consultation->refresh()->load(['patient', 'counselor', 'chatMessages']);

        $messages = $consultation->chatMessages->map(function (ChatMessage $msg) use ($consultation, $user) {
            $isFromPatient = $msg->sender_id === $consultation->patient_id;
            $senderCode = $isFromPatient
                ? ($consultation->patient?->anonymous_patient_code ?? 'Pasien Anonim')
                : ($consultation->counselor?->anonymous_counselor_code ?? 'Pendamping Anonim');

            return [
                'id' => $msg->id,
                'sender_role' => $isFromPatient ? 'patient' : 'counselor',
                'sender_anonymous_code' => $senderCode,
                'is_me' => $msg->sender_id === $user->id,
                'message' => $msg->message,
                'is_read' => $msg->is_read,
                'created_at' => $msg->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'consultation' => $this->formatConsultation($consultation, $user),
                'my_anonymous_code' => $this->resolveAnonymousCode($user),
                'partner_anonymous_code' => $user->id === $consultation->patient_id
                    ? ($consultation->counselor?->anonymous_counselor_code ?? 'Pendamping Anonim')
                    : ($consultation->patient?->anonymous_patient_code ?? 'Pasien Anonim'),
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Kirim pesan baru di dalam ruang konsultasi anonim.
     */
    public function sendConsultationMessage(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $consultation = Consultation::with(['patient', 'counselor'])->findOrFail($id);

        if (! $this->canAccessConsultation($user, $consultation)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $msg = ChatMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $user->id,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        $isFromPatient = $msg->sender_id === $consultation->patient_id;

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan berhasil dikirim.',
            'data' => [
                'id' => $msg->id,
                'consultation_id' => $consultation->id,
                'sender_role' => $isFromPatient ? 'patient' : 'counselor',
                'sender_anonymous_code' => $this->resolveAnonymousCode($user),
                'message' => $msg->message,
                'created_at' => $msg->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Ubah status sesi konsultasi (active, completed, cancelled).
     */
    public function updateConsultationStatus(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $consultation = Consultation::with(['patient', 'counselor', 'chatMessages'])->findOrFail($id);

        if (! $this->canAccessConsultation($user, $consultation)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'completed', 'cancelled'])],
        ]);

        $consultation->update(['status' => $validated['status']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status sesi konsultasi berhasil diperbarui.',
            'data' => $this->formatConsultation($consultation, $user),
        ]);
    }

    /**
     * Admin: Daftar semua rekomendasi koping.
     */
    public function listRecommendations(): JsonResponse
    {
        $this->ensureCopingSeeded();

        return response()->json([
            'status' => 'success',
            'data' => CopingRecommendation::latest()->get(),
        ]);
    }

    /**
     * Admin: Tambah rekomendasi koping baru.
     */
    public function storeRecommendation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phase' => ['required', Rule::in(['manic', 'depressive'])],
            'type' => ['required', Rule::in(['activity', 'music', 'video'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'url', 'max:255'],
        ]);

        $rec = CopingRecommendation::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Rekomendasi koping berhasil ditambahkan.',
            'data' => $rec,
        ], 201);
    }

    /**
     * Admin: Hapus rekomendasi koping.
     */
    public function destroyRecommendation(int $id): JsonResponse
    {
        CopingRecommendation::findOrFail($id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rekomendasi koping berhasil dihapus.',
        ]);
    }

    // === Helper Internal ===

    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'anonymous_code' => $this->resolveAnonymousCode($user),
        ];
    }

    protected function resolveAnonymousCode(User $user): ?string
    {
        return match ($user->role) {
            'patient' => $user->anonymous_patient_code,
            'counselor' => $user->anonymous_counselor_code,
            default => null,
        };
    }

    protected function formatDailyEntry(DailyEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'entry_date' => Carbon::parse($entry->entry_date)->toDateString(),
            'entry_time' => $entry->entry_time,
            'period' => $entry->period,
            'mood_level' => $entry->mood_level,
            'formatted_mood_level' => $entry->formatted_mood_level,
            'mood_label' => $entry->mood_label,
            'energy_level' => $entry->energy_level,
            'energy_label' => $entry->energy_label,
            'mood_phase' => $entry->mood,
            'mood_score_10' => $entry->mood_score,
            'daily_notes' => $entry->daily_notes,
            'coping_recommendations' => $entry->relationLoaded('copingRecommendations') ? $entry->copingRecommendations : [],
        ];
    }

    protected function formatConsultation(Consultation $consultation, User $viewer): array
    {
        $messages = $consultation->relationLoaded('chatMessages') ? $consultation->chatMessages : collect();

        return [
            'id' => $consultation->id,
            'status' => $consultation->status,
            'scheduled_at' => $consultation->scheduled_at?->toIso8601String(),
            'notes' => $consultation->notes,
            'anonymous_patient_code' => $consultation->patient?->anonymous_patient_code ?? 'Pasien Anonim',
            'anonymous_counselor_code' => $consultation->counselor?->anonymous_counselor_code ?? 'Pendamping Anonim',
            'counselor_focus' => $consultation->counselor?->bio,
            'messages_count' => $messages->count(),
            'unread_count' => $messages->where('sender_id', '!=', $viewer->id)->where('is_read', false)->count(),
            'created_at' => $consultation->created_at?->toIso8601String(),
        ];
    }

    protected function canAccessConsultation(User $user, Consultation $consultation): bool
    {
        return $user->role === 'admin'
            || $consultation->patient_id === $user->id
            || $consultation->counselor_id === $user->id;
    }

    protected function buildClinicalSummary(int $userId, int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $entries = DailyEntry::where('user_id', $userId)
            ->whereDate('entry_date', '>=', $startDate)
            ->whereDate('entry_date', '<=', $endDate)
            ->get();

        $sleeps = SleepLog::where('user_id', $userId)
            ->whereDate('log_date', '>=', $startDate)
            ->whereDate('log_date', '<=', $endDate)
            ->get();

        $meds = MedicationLog::where('user_id', $userId)
            ->whereDate('log_date', '>=', $startDate)
            ->whereDate('log_date', '<=', $endDate)
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());

        $medDaysTaken = 0;
        foreach ($meds as $dayMedLogs) {
            if ($dayMedLogs->where('status', 'on_time')->isNotEmpty()) {
                $medDaysTaken++;
            }
        }

        return [
            'filled_days' => $entries->groupBy(fn ($item) => Carbon::parse($item->entry_date)->toDateString())->count(),
            'total_mood_logs' => $entries->count(),
            'avg_mood' => $entries->isNotEmpty() ? round($entries->avg('mood_level'), 2) : 0,
            'min_mood' => $entries->isNotEmpty() ? (int) $entries->min('mood_level') : 0,
            'max_mood' => $entries->isNotEmpty() ? (int) $entries->max('mood_level') : 0,
            'avg_sleep' => $sleeps->isNotEmpty() ? round($sleeps->avg('duration_hours'), 1) : 0,
            'med_days_yes' => $medDaysTaken,
        ];
    }

    protected function calculateWeeklyEvaluation(int $userId, int $days = 14): WeeklyEvaluation
    {
        $weekStart = Carbon::today()->subDays($days - 1)->toDateString();
        $weekEnd = Carbon::today()->toDateString();

        $moods = DailyEntry::where('user_id', $userId)->whereDate('entry_date', '>=', $weekStart)->whereDate('entry_date', '<=', $weekEnd)->get();
        $sleeps = SleepLog::where('user_id', $userId)->whereDate('log_date', '>=', $weekStart)->whereDate('log_date', '<=', $weekEnd)->get();
        $meds = MedicationLog::where('user_id', $userId)->whereDate('log_date', '>=', $weekStart)->whereDate('log_date', '<=', $weekEnd)->get();

        $totalEntries = $moods->count();
        $avgMoodLevel = $totalEntries > 0 ? round($moods->avg('mood_level'), 2) : null;
        $avgSleepHours = $sleeps->isNotEmpty() ? round($sleeps->avg('duration_hours'), 1) : null;

        $manicDays = $moods->where('mood_level', '>=', 2)->groupBy(fn ($i) => $i->entry_date->toDateString())->count();
        $depressiveDays = $moods->where('mood_level', '<=', -2)->groupBy(fn ($i) => $i->entry_date->toDateString())->count();

        $totalMeds = $meds->count();
        $onTimeMeds = $meds->where('status', 'on_time')->count();
        $overdoseMeds = $meds->where('status', 'overdose')->count();
        $compliancePct = $totalMeds > 0 ? (int) round(($onTimeMeds / $totalMeds) * 100) : 100;

        $urgentControl = false;
        $reasons = [];

        if ($depressiveDays >= 2) {
            $urgentControl = true;
            $reasons[] = "tercatat {$depressiveDays} hari dalam skala rendah/depresi (-2 s/d -3)";
        }
        if ($manicDays >= 3) {
            $urgentControl = true;
            $reasons[] = "tercatat {$manicDays} hari dalam skala tinggi/manik (+2 s/d +3)";
        }
        $shortSleepDays = $sleeps->where('duration_hours', '<=', 4)->count();
        if ($shortSleepDays >= 2) {
            $urgentControl = true;
            $reasons[] = "terdapat {$shortSleepDays} hari dengan jam tidur sangat singkat (2-4 jam/malam)";
        }
        if ($totalMeds > 0 && $compliancePct < 75) {
            $urgentControl = true;
            $reasons[] = "kepatuhan jadwal resep obat menurun ({$compliancePct}%)";
        }
        if ($overdoseMeds > 0) {
            $urgentControl = true;
            $reasons[] = "terdapat {$overdoseMeds} catatan konsumsi obat melebihi dosis resep";
        }

        if ($urgentControl) {
            $recommendation = 'INDIKASI EVALUASI KLINIS: Berdasarkan rekam data periode ini (' . implode('; ', $reasons) . '), disarankan untuk membawa laporan ini dan menjadwalkan konsultasi dengan Psikiater atau Psikolog Klinis Anda.';
        } elseif ($totalEntries === 0) {
            $recommendation = 'Belum ada catatan suasana hati pada periode ini. Mulailah mengisi skala mood (-3 s/d +3), tingkat energi (1-5), jam tidur, dan kepatuhan resep obat.';
        } else {
            $recommendation = 'Fluktuasi suasana hati, durasi tidur, dan kepatuhan konsumsi resep obat berada dalam rentang yang terkendali. Pertahankan pencatatan rutin di jam yang konsisten.';
        }

        $evalData = [
            'week_end' => $weekEnd,
            'avg_mood_score' => $avgMoodLevel,
            'avg_sleep_hours' => $avgSleepHours,
            'total_entries' => $totalEntries,
            'medication_compliance_pct' => $compliancePct,
            'manic_days' => $manicDays,
            'depressive_days' => $depressiveDays,
            'urgent_control' => $urgentControl,
            'system_recommendation' => $recommendation,
        ];

        $evaluation = WeeklyEvaluation::where('user_id', $userId)
            ->whereDate('week_start', $weekStart)
            ->first();

        if ($evaluation) {
            $evaluation->update($evalData);

            return $evaluation;
        }

        return WeeklyEvaluation::create(array_merge([
            'user_id' => $userId,
            'week_start' => $weekStart,
        ], $evalData));
    }

    protected function ensureCopingSeeded(): void
    {
        if (CopingRecommendation::count() === 0) {
            (new CopingRecommendationSeeder())->run();
        }
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
