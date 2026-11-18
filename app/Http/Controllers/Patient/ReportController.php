<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DailyEntry;
use App\Models\MedicationLog;
use App\Models\Prescription;
use App\Models\SleepLog;
use App\Models\WeeklyEvaluation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $days = (int) $request->query('days', 14);
        if (! in_array($days, [7, 14, 30], true)) {
            $days = 14;
        }

        $startDate = Carbon::today()->subDays($days - 1)->toDateString();
        $endDate = Carbon::today()->toDateString();

        $moodEntries = DailyEntry::where('user_id', $user->id)
            ->whereDate('entry_date', '>=', $startDate)
            ->whereDate('entry_date', '<=', $endDate)
            ->orderBy('entry_date', 'asc')
            ->orderBy('entry_time', 'asc')
            ->get();

        $sleepLogs = SleepLog::where('user_id', $user->id)
            ->whereDate('log_date', '>=', $startDate)
            ->whereDate('log_date', '<=', $endDate)
            ->orderBy('log_date', 'asc')
            ->get();

        $medLogs = MedicationLog::where('user_id', $user->id)
            ->whereDate('log_date', '>=', $startDate)
            ->whereDate('log_date', '<=', $endDate)
            ->orderBy('log_date', 'desc')
            ->orderBy('scheduled_time', 'asc')
            ->get();

        $prescriptions = Prescription::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $weeklyEval = $this->calculateWeeklyEvaluation($user->id, $days);

        $chartLabels = [];
        $moodLevelsData = [];
        $energyLevelsData = [];
        $sleepHoursData = [];

        $moodsGroupedByDate = $moodEntries->groupBy(fn ($item) => Carbon::parse($item->entry_date)->toDateString());
        $sleepByDate = $sleepLogs->keyBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());
        $medsGroupedByDate = $medLogs->groupBy(fn ($item) => Carbon::parse($item->log_date)->toDateString());

        // Susun baris tabel kronologis harian (seperti lembar kerja klinis 2 minggu)
        $dailyRows = [];
        $dayNamesId = [
            'Sun' => 'Min',
            'Mon' => 'Sen',
            'Tue' => 'Sel',
            'Wed' => 'Rab',
            'Thu' => 'Kam',
            'Fri' => 'Jum',
            'Sat' => 'Sab',
        ];

        for ($i = $days - 1; $i >= 0; $i--) {
            $dateObj = Carbon::today()->subDays($i);
            $dateStr = $dateObj->toDateString();
            $chartLabels[] = $dateObj->format('d M');

            $dayMoods = $moodsGroupedByDate->get($dateStr, collect());
            $daySleep = $sleepByDate->get($dateStr);
            $dayMeds = $medsGroupedByDate->get($dateStr, collect());

            $avgDayMood = $dayMoods->count() > 0 ? round($dayMoods->avg('mood_level'), 1) : null;
            $avgDayEnergy = $dayMoods->count() > 0 ? round($dayMoods->avg('energy_level'), 1) : null;

            $moodLevelsData[] = $avgDayMood;
            $energyLevelsData[] = $avgDayEnergy;
            $sleepHoursData[] = $daySleep ? (float) $daySleep->duration_hours : null;

            if ($dayMoods->count() > 0 || $daySleep || $dayMeds->count() > 0) {
                $dailyRows[] = [
                    'day_name' => $dayNamesId[$dateObj->format('D')] ?? $dateObj->format('D'),
                    'date_formatted' => $dateObj->format('d M Y'),
                    'date_str' => $dateStr,
                    'moods' => $dayMoods,
                    'avg_mood_level' => $avgDayMood,
                    'avg_energy_level' => $avgDayEnergy,
                    'sleep' => $daySleep,
                    'meds' => $dayMeds,
                    'took_medication' => $dayMeds->where('status', 'on_time')->count() > 0,
                ];
            }
        }

        // Ringkasan Otomatis (Persis rumus Excel: Hari diisi, Rata-rata mood, Terendah/Tertinggi, Rata-rata tidur, Hari minum obat Ya)
        $filledDaysCount = count($dailyRows);
        $avgMoodOverall = $moodEntries->count() > 0 ? round($moodEntries->avg('mood_level'), 2) : 0;
        $minMoodOverall = $moodEntries->count() > 0 ? $moodEntries->min('mood_level') : 0;
        $maxMoodOverall = $moodEntries->count() > 0 ? $moodEntries->max('mood_level') : 0;
        $avgSleepOverall = $sleepLogs->count() > 0 ? round($sleepLogs->avg('duration_hours'), 1) : 0;
        $daysWithMedTaken = collect($dailyRows)->where('took_medication', true)->count();

        $summaryMetrics = [
            'filled_days' => $filledDaysCount,
            'total_mood_entries' => $moodEntries->count(),
            'avg_mood' => $avgMoodOverall,
            'min_mood' => $minMoodOverall,
            'max_mood' => $maxMoodOverall,
            'avg_sleep' => $avgSleepOverall,
            'med_days_yes' => $daysWithMedTaken,
        ];

        $medStats = [
            'on_time' => $medLogs->where('status', 'on_time')->count(),
            'missed' => $medLogs->where('status', 'missed')->count(),
            'overdose' => $medLogs->where('status', 'overdose')->count(),
        ];

        return view('patient.reports', [
            'user' => $user,
            'days' => $days,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'moodEntries' => $moodEntries,
            'sleepLogs' => $sleepLogs,
            'medLogs' => $medLogs,
            'prescriptions' => $prescriptions,
            'dailyRows' => $dailyRows,
            'summaryMetrics' => $summaryMetrics,
            'weeklyEval' => $weeklyEval,
            'chartLabels' => $chartLabels,
            'moodLevelsData' => $moodLevelsData,
            'energyLevelsData' => $energyLevelsData,
            'sleepHoursData' => $sleepHoursData,
            'medStats' => $medStats,
        ]);
    }

    protected function calculateWeeklyEvaluation(int $userId, int $days = 14): WeeklyEvaluation
    {
        $weekStart = Carbon::today()->subDays($days - 1)->toDateString();
        $weekEnd = Carbon::today()->toDateString();

        $moods = DailyEntry::where('user_id', $userId)
            ->whereDate('entry_date', '>=', $weekStart)
            ->whereDate('entry_date', '<=', $weekEnd)
            ->get();

        $sleeps = SleepLog::where('user_id', $userId)
            ->whereDate('log_date', '>=', $weekStart)
            ->whereDate('log_date', '<=', $weekEnd)
            ->get();

        $meds = MedicationLog::where('user_id', $userId)
            ->whereDate('log_date', '>=', $weekStart)
            ->whereDate('log_date', '<=', $weekEnd)
            ->get();

        $totalEntries = $moods->count();
        $avgMoodLevel = $totalEntries > 0 ? round($moods->avg('mood_level'), 2) : null;
        $avgSleepHours = $sleeps->count() > 0 ? round($sleeps->avg('duration_hours'), 1) : null;

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
}
