<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\Consultation;
use App\Models\CopingRecommendation;
use App\Models\DailyEntry;
use App\Models\MedicationLog;
use App\Models\MoodRecommendation;
use App\Models\Prescription;
use App\Models\SleepLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (CopingRecommendation::count() === 0) {
            $this->call([
                CopingRecommendationSeeder::class,
            ]);
        }

        // 1. Akun Pasien Utama
        $patient = User::firstOrCreate(
            ['email' => 'pasien@tepo.test'],
            [
                'name' => 'Muhammad Fachrial',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'phone' => '081234567890',
                'bio' => 'Pemantauan harian suasana hati, siklus tidur, dan kepatuhan resep obat.',
            ]
        );

        // 2. Akun Mahasiswa Psikologi (DEMO - Identitas dirahasiakan secara dua arah di antarmuka)
        $counselor1 = User::firstOrCreate(
            ['email' => 'konselor1@tepo.test'],
            [
                'name' => 'Alya Rahmawati',
                'password' => Hash::make('password123'),
                'role' => 'counselor',
                'phone' => '081298765432',
                'bio' => 'Fokus Pendampingan: Regulasi emosi, manajemen fase manik-depresi, dan penataan rutinitas tidur harian.',
            ]
        );

        $counselor2 = User::firstOrCreate(
            ['email' => 'konselor2@tepo.test'],
            [
                'name' => 'Bagas Pratama',
                'password' => Hash::make('password123'),
                'role' => 'counselor',
                'phone' => '081345678901',
                'bio' => 'Fokus Pendampingan: Konseling sebaya empatik, pengelolaan kecemasan sosial, dan kepatuhan terapi.',
            ]
        );

        // 3. Akun Admin
        User::firstOrCreate(
            ['email' => 'admin@tepo.test'],
            [
                'name' => 'Administrator Klinis TEPO',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081100001111',
                'bio' => 'Pengelola sistem dan pustaka intervensi koping TEPO.',
            ]
        );

        // 4. Seed Data Klinis 2 Minggu (14 Hari) berdasarkan sampel pasien bipolar asli
        self::seedClinicalDataForPatient($patient);

        // 5. Seed Contoh Sesi Konsultasi Anonim Dua Arah
        if (Consultation::where('patient_id', $patient->id)->count() === 0) {
            $consultation = Consultation::create([
                'patient_id' => $patient->id,
                'counselor_id' => $counselor1->id,
                'status' => 'active',
                'scheduled_at' => now()->subHours(2),
                'notes' => 'Dua hari terakhir jam tidur saya turun ke 2-4 jam tetapi energi dan ide rasanya sangat penuh.',
            ]);

            ChatMessage::create([
                'consultation_id' => $consultation->id,
                'sender_id' => $patient->id,
                'message' => 'Halo, dua hari terakhir ini saya hanya tidur 2 sampai 4 jam, tetapi rasanya bersemangat sekali, banyak bicara, dan muncul banyak ide tugas sekaligus.',
                'is_read' => true,
            ]);

            ChatMessage::create([
                'consultation_id' => $consultation->id,
                'sender_id' => $counselor1->id,
                'message' => 'Terima kasih sudah berbagi di ruang anonim ini. Penurunan jam tidur drastis disertai lonjakan energi dan ide berlebih merupakan sinyal penting fase tinggi. Apakah obat resep malam tetap diminum sesuai jadwal?',
                'is_read' => false,
            ]);
        }
    }

    /**
     * Mengisi 3 resep obat terpisah dan 14 hari catatan klinis asli pasien bipolar.
     */
    public static function seedClinicalDataForPatient(User $patient): void
    {
        if (DailyEntry::where('user_id', $patient->id)->count() > 0) {
            return;
        }

        // Tiga macam obat berbeda dengan resep masing-masing
        $rxLithium = Prescription::create([
            'user_id' => $patient->id,
            'medication_name' => 'Lithium Carbonate',
            'dosage' => '300 mg',
            'frequency' => '2x1 sehari (08:00 Pagi & 20:00 Malam)',
            'prescribing_doctor' => 'dr. Hendra Wijaya, Sp.KJ',
            'instructions' => 'Penstabil suasana hati utama. Diminum sesudah makan dengan air putih yang cukup.',
            'is_active' => true,
        ]);

        $rxLamotrigine = Prescription::create([
            'user_id' => $patient->id,
            'medication_name' => 'Lamotrigine',
            'dosage' => '100 mg',
            'frequency' => '1x1 sehari (08:00 Pagi)',
            'prescribing_doctor' => 'dr. Hendra Wijaya, Sp.KJ',
            'instructions' => 'Pencegahan relaps episode depresi. Diminum rutin setiap pagi.',
            'is_active' => true,
        ]);

        $rxQuetiapine = Prescription::create([
            'user_id' => $patient->id,
            'medication_name' => 'Quetiapine XR',
            'dosage' => '50 mg',
            'frequency' => '1x1 sehari (21:30 Malam sebelum tidur)',
            'prescribing_doctor' => 'dr. Hendra Wijaya, Sp.KJ',
            'instructions' => 'Membantu regulasi tidur dan meredam lonjakan manik malam hari.',
            'is_active' => true,
        ]);

        // 14 baris data klinis asli dari Catatan_Mood_2_Minggu.xlsx
        // Dipetakan ke 14 hari terakhir (hari ke-1 = 13 hari lalu, hari ke-14 = hari ini)
        $clinicalRows = [
            ['offset' => 13, 'mood' => 0,  'sleep' => 8.0,  'energy' => 3, 'med' => true,  'notes' => 'Normal seperti biasanya'],
            ['offset' => 12, 'mood' => -1, 'sleep' => 7.0,  'energy' => 1, 'med' => true,  'notes' => 'Perasaan takut ketemu orang'],
            ['offset' => 11, 'mood' => 0,  'sleep' => 12.0, 'energy' => 2, 'med' => true,  'notes' => 'Normal seperti biasanya'],
            ['offset' => 10, 'mood' => 0,  'sleep' => 10.0, 'energy' => 3, 'med' => true,  'notes' => 'Normal seperti biasanya'],
            ['offset' => 9,  'mood' => 3,  'sleep' => 5.0,  'energy' => 5, 'med' => true,  'notes' => 'Full ngerjain tugas, belanja berlebihan'],
            ['offset' => 8,  'mood' => 2,  'sleep' => 8.0,  'energy' => 4, 'med' => true,  'notes' => 'Beresin kosan, sambil nugas'],
            ['offset' => 7,  'mood' => 1,  'sleep' => 8.0,  'energy' => 2, 'med' => true,  'notes' => 'Nugas, belajar pas mau nya'],
            ['offset' => 6,  'mood' => 2,  'sleep' => 7.0,  'energy' => 4, 'med' => true,  'notes' => 'Belajar, main game, beresin kamar'],
            ['offset' => 5,  'mood' => 0,  'sleep' => 8.0,  'energy' => 1, 'med' => true,  'notes' => 'Normal seperti biasanya'],
            ['offset' => 4,  'mood' => -1, 'sleep' => 12.0, 'energy' => 1, 'med' => true,  'notes' => 'Terlalu banyak tidur siang dan malam'],
            ['offset' => 3,  'mood' => -2, 'sleep' => 5.0,  'energy' => 1, 'med' => false, 'notes' => 'Inget penyesalan yang mendalam'],
            ['offset' => 2,  'mood' => -1, 'sleep' => 9.0,  'energy' => 2, 'med' => true,  'notes' => 'Lebih ada gairah sekarang'],
            ['offset' => 1,  'mood' => 3,  'sleep' => 4.0,  'energy' => 3, 'med' => true,  'notes' => 'Banyak bicara, Percaya diri, Banyak nugas'],
            ['offset' => 0,  'mood' => 3,  'sleep' => 2.0,  'energy' => 2, 'med' => true,  'notes' => 'Semangat, Percaya Diri, dan banyak ide'],
        ];

        foreach ($clinicalRows as $row) {
            $dateStr = Carbon::today()->subDays($row['offset'])->toDateString();
            $moodLevel = $row['mood'];

            $moodPhase = DailyEntry::mapMoodLevelToPhase($moodLevel);

            $entry = DailyEntry::create([
                'user_id' => $patient->id,
                'entry_date' => $dateStr,
                'entry_time' => '20:30:00',
                'period' => 'Malam',
                'mood_level' => $moodLevel,
                'energy_level' => $row['energy'],
                'mood' => $moodPhase,
                'mood_score' => DailyEntry::mapMoodLevelToScore10($moodLevel),
                'daily_notes' => $row['notes'],
            ]);

            if (in_array($moodPhase, ['manic', 'depressive'], true)) {
                $recs = CopingRecommendation::where('phase', $moodPhase)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->take(3)
                    ->get();

                foreach ($recs as $rec) {
                    MoodRecommendation::create([
                        'daily_entry_id' => $entry->id,
                        'coping_recommendation_id' => $rec->id,
                    ]);
                }
            }

            // Catatan tidur harian sesuai durasi jam tidur asli
            $sleepHours = (float) $row['sleep'];
            $wakeHour = 7;
            $startCarbon = Carbon::parse($dateStr . ' 07:00:00')->subMinutes((int) ($sleepHours * 60));

            SleepLog::create([
                'user_id' => $patient->id,
                'log_date' => $dateStr,
                'sleep_start' => $startCarbon->format('H:i'),
                'sleep_end' => sprintf('%02d:00', $wakeHour),
                'duration_hours' => $sleepHours,
                'quality' => $sleepHours >= 7 && $sleepHours <= 9 ? 4 : ($sleepHours < 5 ? 2 : 3),
                'notes' => $sleepHours < 5 ? 'Sulit tidur karena pikiran masih aktif' : ($sleepHours >= 10 ? 'Durasi tidur panjang' : 'Tidur cukup'),
            ]);

            // Catatan konsumsi multi-obat sesuai resep masing-masing
            if ($row['med']) {
                MedicationLog::create([
                    'user_id' => $patient->id,
                    'prescription_id' => $rxLithium->id,
                    'log_date' => $dateStr,
                    'medication_name' => $rxLithium->medication_name,
                    'dosage' => $rxLithium->dosage,
                    'prescription_rule' => $rxLithium->frequency,
                    'quantity_taken' => 2,
                    'scheduled_time' => '08:00',
                    'status' => 'on_time',
                    'notes' => 'Diminum pagi dan malam sesuai resep',
                ]);

                MedicationLog::create([
                    'user_id' => $patient->id,
                    'prescription_id' => $rxLamotrigine->id,
                    'log_date' => $dateStr,
                    'medication_name' => $rxLamotrigine->medication_name,
                    'dosage' => $rxLamotrigine->dosage,
                    'prescription_rule' => $rxLamotrigine->frequency,
                    'quantity_taken' => 1,
                    'scheduled_time' => '08:00',
                    'status' => 'on_time',
                    'notes' => 'Diminum pagi sesudah sarapan',
                ]);

                MedicationLog::create([
                    'user_id' => $patient->id,
                    'prescription_id' => $rxQuetiapine->id,
                    'log_date' => $dateStr,
                    'medication_name' => $rxQuetiapine->medication_name,
                    'dosage' => $rxQuetiapine->dosage,
                    'prescription_rule' => $rxQuetiapine->frequency,
                    'quantity_taken' => 1,
                    'scheduled_time' => '21:30',
                    'status' => 'on_time',
                    'notes' => 'Diminum sebelum tidur malam',
                ]);
            } else {
                MedicationLog::create([
                    'user_id' => $patient->id,
                    'prescription_id' => $rxLithium->id,
                    'log_date' => $dateStr,
                    'medication_name' => $rxLithium->medication_name,
                    'dosage' => $rxLithium->dosage,
                    'prescription_rule' => $rxLithium->frequency,
                    'quantity_taken' => 0,
                    'scheduled_time' => '08:00',
                    'status' => 'missed',
                    'notes' => 'Tidak minum obat karena sedang terpuruk',
                ]);
            }
        }
    }
}
