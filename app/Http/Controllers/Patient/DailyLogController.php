<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\CopingRecommendation;
use App\Models\DailyEntry;
use App\Models\MedicationLog;
use App\Models\MoodRecommendation;
use App\Models\Prescription;
use App\Models\SleepLog;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DailyLogController extends Controller
{
    /**
     * Menyimpan catatan mood baru (mendukung multi-mood dalam 1 hari: pagi, siang, sore, malam).
     */
    public function storeMood(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entry_date' => ['nullable', 'date'],
            'period' => ['required', 'string', Rule::in(['Pagi', 'Siang', 'Sore', 'Malam'])],
            'entry_time' => ['required', 'date_format:H:i'],
            'mood_level' => ['required', 'integer', 'between:-3,3'],
            'energy_level' => ['required', 'integer', 'between:1,5'],
            'daily_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'period.required' => 'Pilih waktu pencatatan (Pagi, Siang, Sore, atau Malam).',
            'entry_time.required' => 'Jam pencatatan suasana hati wajib diisi.',
            'mood_level.required' => 'Pilih skala mood antara -3 hingga +3.',
            'mood_level.between' => 'Skala mood harus berada pada rentang -3 sampai +3.',
            'energy_level.required' => 'Pilih tingkat energi antara 1 hingga 5.',
            'energy_level.between' => 'Tingkat energi harus berada pada rentang 1 sampai 5.',
        ]);

        $userId = Auth::id();
        $entryDate = $validated['entry_date'] ?? Carbon::today()->toDateString();
        $moodLevel = (int) $validated['mood_level'];
        $moodPhase = DailyEntry::mapMoodLevelToPhase($moodLevel);
        $moodScore10 = DailyEntry::mapMoodLevelToScore10($moodLevel);

        $dailyEntry = DailyEntry::create([
            'user_id' => $userId,
            'entry_date' => $entryDate,
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

        $formattedLevel = $moodLevel > 0 ? '+' . $moodLevel : (string) $moodLevel;

        return redirect()->route('patient.dashboard')->with(
            'success',
            "Catatan mood sesi {$validated['period']} ({$validated['entry_time']}) dengan skala {$formattedLevel} dan energi {$validated['energy_level']}/5 berhasil ditambahkan."
        );
    }

    /**
     * Menghapus satu entri mood harian milik pasien.
     */
    public function destroyMood(int $id): RedirectResponse
    {
        $entry = DailyEntry::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $entry->delete();

        return redirect()->route('patient.dashboard')->with('info', 'Satu entri catatan suasana hati berhasil dihapus.');
    }

    /**
     * Menyimpan atau memperbarui catatan siklus tidur harian.
     */
    public function storeSleep(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sleep_start' => ['nullable', 'date_format:H:i'],
            'sleep_end' => ['nullable', 'date_format:H:i'],
            'duration_hours' => ['nullable', 'numeric', 'min:0.5', 'max:24'],
            'quality' => ['nullable', 'integer', 'between:1,5'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

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

        $sleepData = [
            'sleep_start' => $sleepStart,
            'sleep_end' => $sleepEnd,
            'duration_hours' => $durationHours,
            'quality' => $validated['quality'] ?? 3,
            'notes' => $validated['notes'] ?? null,
        ];

        $existingSleep = SleepLog::where('user_id', $userId)
            ->whereDate('log_date', $today)
            ->first();

        if ($existingSleep) {
            $existingSleep->update($sleepData);
        } else {
            SleepLog::create(array_merge([
                'user_id' => $userId,
                'log_date' => $today,
            ], $sleepData));
        }

        return redirect()->route('patient.dashboard')->with(
            'success',
            "Catatan tidur berhasil disimpan dengan durasi {$durationHours} jam."
        );
    }

    /**
     * Menambahkan resep obat baru ke daftar resep aktif pasien.
     */
    public function storePrescription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'string', 'max:255'],
            'prescribing_doctor' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ], [
            'medication_name.required' => 'Nama obat wajib diisi.',
            'dosage.required' => 'Dosis obat wajib diisi.',
            'frequency.required' => 'Aturan pakai / jadwal resep wajib diisi.',
        ]);

        Prescription::create([
            'user_id' => Auth::id(),
            'medication_name' => trim($validated['medication_name']),
            'dosage' => trim($validated['dosage']),
            'frequency' => trim($validated['frequency']),
            'prescribing_doctor' => $validated['prescribing_doctor'] ? trim($validated['prescribing_doctor']) : null,
            'instructions' => $validated['instructions'] ? trim($validated['instructions']) : null,
            'is_active' => true,
        ]);

        return redirect()->route('patient.dashboard')->with(
            'success',
            "Resep obat {$validated['medication_name']} ({$validated['dosage']} - {$validated['frequency']}) berhasil ditambahkan ke daftar resep Anda."
        );
    }

    /**
     * Menonaktifkan atau menghapus resep obat pasien.
     */
    public function destroyPrescription(int $id): RedirectResponse
    {
        $prescription = Prescription::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $prescription->delete();

        return redirect()->route('patient.dashboard')->with('info', 'Data resep obat berhasil dihapus dari daftar aktif.');
    }

    /**
     * Menyimpan entri konsumsi obat harian (bisa memilih dari resep yang ada atau input manual).
     */
    public function storeMedication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prescription_id' => ['nullable', 'integer'],
            'medication_name' => ['nullable', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'prescription_rule' => ['nullable', 'string', 'max:255'],
            'quantity_taken' => ['required', 'integer', 'min:0', 'max:20'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'string', Rule::in(['on_time', 'missed', 'overdose'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $prescription = null;
        if (! empty($validated['prescription_id'])) {
            $prescription = Prescription::where('user_id', $userId)
                ->where('id', $validated['prescription_id'])
                ->first();
        }

        $medName = $prescription ? $prescription->medication_name : trim((string) ($validated['medication_name'] ?? ''));
        $dosage = $prescription ? $prescription->dosage : trim((string) ($validated['dosage'] ?? ''));
        $rule = $prescription ? $prescription->frequency : trim((string) ($validated['prescription_rule'] ?? 'Sesuai anjuran dokter'));

        if ($medName === '' || $dosage === '') {
            return back()->withErrors(['medication_name' => 'Pilih salah satu obat dari daftar resep Anda atau isi nama dan dosis obat secara manual.'])->withInput();
        }

        MedicationLog::create([
            'user_id' => $userId,
            'prescription_id' => $prescription?->id,
            'log_date' => $today,
            'medication_name' => $medName,
            'dosage' => $dosage,
            'prescription_rule' => $rule,
            'quantity_taken' => $validated['status'] === 'missed' ? 0 : $validated['quantity_taken'],
            'scheduled_time' => $validated['scheduled_time'] ?? now()->format('H:i'),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $feedback = match ($validated['status']) {
            'on_time' => "Konsumsi {$medName} ({$dosage}) sesuai resep ({$rule}) berhasil dicatat.",
            'missed' => "Catatan dosis terlewat untuk {$medName} ({$dosage}) telah disimpan.",
            'overdose' => "Peringatan: Anda mencatat konsumsi {$medName} melebihi dosis resep. Segera hubungi dokter bila muncul keluhan fisik.",
        };

        return redirect()->route('patient.dashboard')->with('success', $feedback);
    }

    /**
     * Menghapus log obat harian.
     */
    public function destroyMedication(int $id): RedirectResponse
    {
        $log = MedicationLog::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $log->delete();

        return redirect()->route('patient.dashboard')->with('info', 'Catatan log konsumsi obat berhasil dihapus.');
    }

    /**
     * Memuat data sampel klinis 14 hari dari pasien bipolar asli.
     */
    public function seedClinicalSample(): RedirectResponse
    {
        $user = Auth::user();
        DatabaseSeeder::seedClinicalDataForPatient($user);

        return redirect()->route('patient.dashboard')->with(
            'success',
            'Data pemantauan klinis 2 minggu (14 hari) beserta 3 resep obat standar berhasil dimuat.'
        );
    }
}
