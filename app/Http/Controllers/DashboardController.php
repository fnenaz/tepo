<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\CopingRecommendation;
use App\Models\DailyEntry;
use App\Models\MedicationLog;
use App\Models\Prescription;
use App\Models\SleepLog;
use App\Models\User;
use App\Models\WeeklyEvaluation;
use Carbon\Carbon;
use Database\Seeders\CopingRecommendationSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            'counselor' => redirect()->route('counselor.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('patient.dashboard'),
        };
    }

    public function patient(): View
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $start14Days = Carbon::today()->subDays(13)->toDateString();

        if (CopingRecommendation::count() === 0) {
            (new CopingRecommendationSeeder())->run();
        }

        // Satu hari bisa memiliki lebih dari 1 mood (pagi, siang, sore, malam)
        $todayMoods = DailyEntry::with('copingRecommendations')
            ->where('user_id', $user->id)
            ->whereDate('entry_date', $today)
            ->orderBy('entry_time', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $latestTodayMood = $todayMoods->last();

        $todaySleep = SleepLog::where('user_id', $user->id)
            ->whereDate('log_date', $today)
            ->first();

        // Daftar resep obat aktif milik pasien (masing-masing obat punya aturan pakai sendiri)
        $prescriptions = Prescription::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        $todayMeds = MedicationLog::where('user_id', $user->id)
            ->whereDate('log_date', $today)
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Ambil data 14 hari terakhir untuk Lembar Pemantauan Klinis 2 Minggu
        $entries14Days = DailyEntry::where('user_id', $user->id)
            ->whereDate('entry_date', '>=', $start14Days)
            ->whereDate('entry_date', '<=', $today)
            ->orderBy('entry_date', 'desc')
            ->orderBy('entry_time', 'asc')
            ->get();

        $sleeps14Days = SleepLog::where('user_id', $user->id)
            ->whereDate('log_date', '>=', $start14Days)
            ->whereDate('log_date', '<=', $today)
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());

        $meds14Days = MedicationLog::where('user_id', $user->id)
            ->whereDate('log_date', '>=', $start14Days)
            ->whereDate('log_date', '<=', $today)
            ->get()
            ->groupBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());

        // Ringkasan Otomatis 2 Minggu (sesuai format tabel klinis asli)
        $filledDaysCount = $entries14Days->groupBy(fn ($item) => Carbon::parse($item->entry_date)->toDateString())->count();
        $avgMoodLevel = $entries14Days->count() > 0 ? round($entries14Days->avg('mood_level'), 2) : 0;
        $minMoodLevel = $entries14Days->count() > 0 ? $entries14Days->min('mood_level') : 0;
        $maxMoodLevel = $entries14Days->count() > 0 ? $entries14Days->max('mood_level') : 0;
        $avgSleepHours = $sleeps14Days->count() > 0 ? round($sleeps14Days->avg('duration_hours'), 1) : 0;

        $medDaysTaken = 0;
        foreach ($meds14Days as $dateKey => $dayMedLogs) {
            if ($dayMedLogs->where('status', 'on_time')->count() > 0) {
                $medDaysTaken++;
            }
        }

        $clinicalSummary = [
            'filled_days' => $filledDaysCount,
            'total_mood_logs' => $entries14Days->count(),
            'avg_mood' => $avgMoodLevel,
            'min_mood' => $minMoodLevel,
            'max_mood' => $maxMoodLevel,
            'avg_sleep' => $avgSleepHours,
            'med_days_taken' => $medDaysTaken,
        ];

        $latestEval = WeeklyEvaluation::where('user_id', $user->id)
            ->latest('week_start')
            ->first();

        $manicRecommendations = CopingRecommendation::manic()->active()->get();
        $depressiveRecommendations = CopingRecommendation::depressive()->active()->get();

        return view('dashboard.patient', [
            'user' => $user,
            'todayMoods' => $todayMoods,
            'todayMood' => $latestTodayMood,
            'todaySleep' => $todaySleep,
            'prescriptions' => $prescriptions,
            'todayMeds' => $todayMeds,
            'entries14Days' => $entries14Days,
            'sleeps14Days' => $sleeps14Days,
            'meds14Days' => $meds14Days,
            'clinicalSummary' => $clinicalSummary,
            'latestEval' => $latestEval,
            'manicRecommendations' => $manicRecommendations,
            'depressiveRecommendations' => $depressiveRecommendations,
        ]);
    }

    public function counselor(): View
    {
        $user = Auth::user();

        $consultations = Consultation::with(['patient', 'chatMessages'])
            ->where('counselor_id', $user->id)
            ->latest()
            ->get();

        return view('dashboard.counselor', [
            'user' => $user,
            'consultations' => $consultations,
        ]);
    }

    public function admin(): View
    {
        $user = Auth::user();

        if (CopingRecommendation::count() === 0) {
            (new CopingRecommendationSeeder())->run();
        }

        return view('dashboard.admin', [
            'user' => $user,
            'recommendations' => CopingRecommendation::latest()->get(),
            'totalPatients' => User::where('role', 'patient')->count(),
            'totalCounselors' => User::where('role', 'counselor')->count(),
        ]);
    }

    public function storeRecommendation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phase' => ['required', Rule::in(['manic', 'depressive'])],
            'type' => ['required', Rule::in(['activity', 'music', 'video'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'url' => ['nullable', 'url', 'max:255'],
        ]);

        CopingRecommendation::create($validated);

        return back()->with('success', 'Rekomendasi koping baru berhasil ditambahkan.');
    }

    public function destroyRecommendation(int $id): RedirectResponse
    {
        CopingRecommendation::findOrFail($id)->delete();

        return back()->with('info', 'Rekomendasi koping berhasil dihapus.');
    }
}
