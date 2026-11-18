<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\DailyEntry;
use App\Models\MedicationLog;
use App\Models\Prescription;
use App\Models\SleepLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TepoClinicalFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createTokenFor(User $user): string
    {
        return Crypt::encryptString((string) $user->id);
    }

    public function test_new_patient_account_starts_completely_empty(): void
    {
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Pasien Baru Mandiri',
            'email' => 'pasien.baru@tepo.test',
            'phone' => '081234567890',
            'role' => 'patient',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.role', 'patient');

        $token = $registerResponse->json('data.token');
        $this->assertNotEmpty($token);

        $dashboardResponse = $this->withToken($token)->getJson('/api/patient/dashboard');

        $dashboardResponse->assertStatus(200)
            ->assertJsonPath('data.clinical_summary.filled_days', 0)
            ->assertJsonPath('data.clinical_summary.total_mood_logs', 0)
            ->assertJsonPath('data.today_sleep', null)
            ->assertJsonCount(0, 'data.today_moods')
            ->assertJsonCount(0, 'data.prescriptions')
            ->assertJsonCount(0, 'data.today_meds');
    }

    public function test_patient_can_log_multiple_moods_in_a_single_day_with_clinical_scale(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $token = $this->createTokenFor($patient);
        $today = Carbon::today()->toDateString();

        $morningResponse = $this->withToken($token)->postJson('/api/patient/mood', [
            'entry_date' => $today,
            'period' => 'Pagi',
            'entry_time' => '08:15',
            'mood_level' => 3,
            'energy_level' => 5,
            'daily_notes' => 'Banyak bicara, ide meluap, dorongan belanja tinggi',
        ]);

        $morningResponse->assertStatus(201)
            ->assertJsonPath('data.period', 'Pagi')
            ->assertJsonPath('data.mood_level', 3)
            ->assertJsonPath('data.formatted_mood_level', '+3')
            ->assertJsonPath('data.mood_phase', 'manic');

        $this->assertNotEmpty($morningResponse->json('data.coping_recommendations'));

        $afternoonResponse = $this->withToken($token)->postJson('/api/patient/mood', [
            'entry_date' => $today,
            'period' => 'Sore',
            'entry_time' => '16:30',
            'mood_level' => -2,
            'energy_level' => 1,
            'daily_notes' => 'Suasana hati turun drastis di sore hari',
        ]);

        $afternoonResponse->assertStatus(201)
            ->assertJsonPath('data.period', 'Sore')
            ->assertJsonPath('data.mood_level', -2)
            ->assertJsonPath('data.mood_phase', 'depressive');

        $this->assertNotEmpty($afternoonResponse->json('data.coping_recommendations'));

        $nightResponse = $this->withToken($token)->postJson('/api/patient/mood', [
            'entry_date' => $today,
            'period' => 'Malam',
            'entry_time' => '21:00',
            'mood_level' => 0,
            'energy_level' => 3,
            'daily_notes' => 'Sudah kembali tenang sebelum tidur',
        ]);

        $nightResponse->assertStatus(201)
            ->assertJsonPath('data.period', 'Malam')
            ->assertJsonPath('data.mood_level', 0)
            ->assertJsonPath('data.mood_phase', 'neutral');

        $this->assertSame(3, DailyEntry::where('user_id', $patient->id)->whereDate('entry_date', $today)->count());

        $this->withToken($token)->getJson('/api/patient/dashboard')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data.today_moods');
    }

    public function test_patient_can_manage_multiple_prescriptions_and_log_medications(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $token = $this->createTokenFor($patient);

        $rx1 = $this->withToken($token)->postJson('/api/patient/prescriptions', [
            'medication_name' => 'Lithium Carbonate',
            'dosage' => '300 mg',
            'frequency' => '2x1 sehari (Pagi & Malam)',
            'prescribing_doctor' => 'dr. Hendra Wijaya, Sp.KJ',
        ])->assertStatus(201);

        $rx2 = $this->withToken($token)->postJson('/api/patient/prescriptions', [
            'medication_name' => 'Lamotrigine',
            'dosage' => '100 mg',
            'frequency' => '1x1 sehari (Pagi)',
            'prescribing_doctor' => 'dr. Hendra Wijaya, Sp.KJ',
        ])->assertStatus(201);

        $rx1Id = $rx1->json('data.id');
        $rx2Id = $rx2->json('data.id');

        $this->withToken($token)->postJson('/api/patient/medication', [
            'prescription_id' => $rx1Id,
            'quantity_taken' => 1,
            'scheduled_time' => '08:00',
            'status' => 'on_time',
            'notes' => 'Dosis pagi sesudah makan',
        ])->assertStatus(201)
            ->assertJsonPath('data.medication_name', 'Lithium Carbonate')
            ->assertJsonPath('data.prescription_rule', '2x1 sehari (Pagi & Malam)');

        $this->withToken($token)->postJson('/api/patient/medication', [
            'prescription_id' => $rx2Id,
            'quantity_taken' => 1,
            'scheduled_time' => '08:05',
            'status' => 'on_time',
            'notes' => 'Diminum bersamaan pagi hari',
        ])->assertStatus(201)
            ->assertJsonPath('data.medication_name', 'Lamotrigine')
            ->assertJsonPath('data.prescription_rule', '1x1 sehari (Pagi)');

        $this->withToken($token)->getJson('/api/patient/prescriptions')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->assertSame(2, MedicationLog::where('user_id', $patient->id)->count());
    }

    public function test_two_week_clinical_report_calculates_accurate_summary_metrics(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $token = $this->createTokenFor($patient);

        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();

        DailyEntry::create([
            'user_id' => $patient->id,
            'entry_date' => $yesterday,
            'entry_time' => '20:00',
            'period' => 'Malam',
            'mood_level' => 2,
            'energy_level' => 4,
            'mood' => 'manic',
            'mood_score' => 8,
            'daily_notes' => 'Beresin kamar sambil nugas',
        ]);

        DailyEntry::create([
            'user_id' => $patient->id,
            'entry_date' => $today,
            'entry_time' => '09:00',
            'period' => 'Pagi',
            'mood_level' => 0,
            'energy_level' => 3,
            'mood' => 'neutral',
            'mood_score' => 5,
            'daily_notes' => 'Normal seperti biasanya',
        ]);

        DailyEntry::create([
            'user_id' => $patient->id,
            'entry_date' => $today,
            'entry_time' => '18:00',
            'period' => 'Sore',
            'mood_level' => -2,
            'energy_level' => 1,
            'mood' => 'depressive',
            'mood_score' => 3,
            'daily_notes' => 'Mulai merasa terpuruk',
        ]);

        SleepLog::create([
            'user_id' => $patient->id,
            'log_date' => $yesterday,
            'sleep_start' => '01:00',
            'sleep_end' => '07:00',
            'duration_hours' => 6.0,
            'quality' => 3,
        ]);

        SleepLog::create([
            'user_id' => $patient->id,
            'log_date' => $today,
            'sleep_start' => '23:00',
            'sleep_end' => '07:00',
            'duration_hours' => 8.0,
            'quality' => 4,
        ]);

        MedicationLog::create([
            'user_id' => $patient->id,
            'log_date' => $yesterday,
            'medication_name' => 'Lithium Carbonate',
            'dosage' => '300 mg',
            'quantity_taken' => 1,
            'status' => 'on_time',
        ]);

        MedicationLog::create([
            'user_id' => $patient->id,
            'log_date' => $today,
            'medication_name' => 'Lithium Carbonate',
            'dosage' => '300 mg',
            'quantity_taken' => 0,
            'status' => 'missed',
        ]);

        $report = $this->withToken($token)->getJson('/api/patient/reports?days=14');

        $report->assertStatus(200)
            ->assertJsonPath('data.summary_metrics.filled_days', 2)
            ->assertJsonPath('data.summary_metrics.total_mood_logs', 3)
            ->assertJsonPath('data.summary_metrics.avg_mood', 0)
            ->assertJsonPath('data.summary_metrics.min_mood', -2)
            ->assertJsonPath('data.summary_metrics.max_mood', 2)
            ->assertJsonPath('data.summary_metrics.avg_sleep', 7)
            ->assertJsonPath('data.summary_metrics.med_days_yes', 1)
            ->assertJsonCount(2, 'data.daily_rows');
    }

    public function test_double_blind_anonymity_in_counseling_hides_real_identities(): void
    {
        $patient = User::factory()->create([
            'name' => 'Budi Raharjo',
            'email' => 'budi.rahasia@tepo.test',
            'role' => 'patient',
            'password' => Hash::make('password123'),
        ]);

        $counselor = User::factory()->create([
            'name' => 'Siti Nurbaya',
            'email' => 'siti.psikologi@tepo.test',
            'role' => 'counselor',
            'bio' => 'Fokus regulasi emosi harian',
            'password' => Hash::make('password123'),
        ]);

        $patientToken = $this->createTokenFor($patient);
        $counselorToken = $this->createTokenFor($counselor);

        $createRes = $this->withToken($patientToken)->postJson('/api/consultations', [
            'counselor_id' => $counselor->id,
            'notes' => 'Saya butuh teman bicara secara rahasia tentang kecemasan hari ini.',
        ]);

        $createRes->assertStatus(201);
        $consultationId = $createRes->json('data.id');

        $this->withToken($counselorToken)->postJson("/api/consultations/{$consultationId}/messages", [
            'message' => 'Silakan ceritakan saja, di ruang ini identitas kita berdua tetap anonim.',
        ])->assertStatus(201);

        // Sisi Mahasiswa Psikologi: tidak boleh melihat nama atau email asli pasien
        $counselorList = $this->withToken($counselorToken)->getJson('/api/consultations');
        $counselorList->assertStatus(200);
        $this->assertStringNotContainsString('Budi Raharjo', $counselorList->getContent());
        $this->assertStringNotContainsString('budi.rahasia@tepo.test', $counselorList->getContent());

        $counselorView = $this->withToken($counselorToken)->getJson("/api/consultations/{$consultationId}");
        $counselorView->assertStatus(200)
            ->assertJsonPath('data.partner_anonymous_code', $patient->anonymous_patient_code);

        $counselorBody = $counselorView->getContent();
        $this->assertStringContainsString('#PA-', $counselorBody);
        $this->assertStringNotContainsString('Budi Raharjo', $counselorBody);
        $this->assertStringNotContainsString('budi.rahasia@tepo.test', $counselorBody);

        // Sisi Pasien: tidak boleh melihat nama atau email asli mahasiswa psikologi
        $patientList = $this->withToken($patientToken)->getJson('/api/consultations');
        $patientList->assertStatus(200);
        $this->assertStringNotContainsString('Siti Nurbaya', $patientList->getContent());
        $this->assertStringNotContainsString('siti.psikologi@tepo.test', $patientList->getContent());

        $patientView = $this->withToken($patientToken)->getJson("/api/consultations/{$consultationId}");
        $patientView->assertStatus(200)
            ->assertJsonPath('data.partner_anonymous_code', $counselor->anonymous_counselor_code);

        $patientBody = $patientView->getContent();
        $this->assertStringContainsString('#PS-', $patientBody);
        $this->assertStringNotContainsString('Siti Nurbaya', $patientBody);
        $this->assertStringNotContainsString('siti.psikologi@tepo.test', $patientBody);
    }

    public function test_rbac_and_idor_protection(): void
    {
        $patient1 = User::factory()->create(['role' => 'patient']);
        $patient2 = User::factory()->create(['role' => 'patient']);
        $counselor = User::factory()->create(['role' => 'counselor']);

        $token1 = $this->createTokenFor($patient1);
        $token2 = $this->createTokenFor($patient2);

        // Unauthenticated API access must return 401
        $this->getJson('/api/patient/dashboard')->assertStatus(401);

        // Patient cannot access Admin endpoint -> 403
        $this->withToken($token1)->postJson('/api/admin/recommendations', [
            'phase' => 'manic',
            'type' => 'activity',
            'title' => 'Test Admin Only',
            'description' => 'Harus ditolak',
        ])->assertStatus(403);

        // Patient 2 cannot access Patient 1's consultation -> 403
        $consultation = Consultation::create([
            'patient_id' => $patient1->id,
            'counselor_id' => $counselor->id,
            'status' => 'active',
            'scheduled_at' => now(),
            'notes' => 'Rahasia pasien 1',
        ]);

        $this->withToken($token2)->getJson("/api/consultations/{$consultation->id}")->assertStatus(403);

        // Patient 2 cannot delete Patient 1's prescription -> 404
        $rx = Prescription::create([
            'user_id' => $patient1->id,
            'medication_name' => 'Lithium Carbonate',
            'dosage' => '300 mg',
            'frequency' => '2x1 sehari',
            'is_active' => true,
        ]);

        $this->withToken($token2)->deleteJson("/api/patient/prescriptions/{$rx->id}")->assertStatus(404);
        $this->assertDatabaseHas('prescriptions', ['id' => $rx->id]);
    }

    public function test_updating_sleep_and_generating_reports_multiple_times_same_day_succeeds_without_duplicate_error(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $token = $this->createTokenFor($patient);
        $today = Carbon::today()->toDateString();

        // Input pertama jam tidur hari ini -> 201 Created
        $this->withToken($token)->postJson('/api/patient/sleep', [
            'log_date' => $today,
            'duration_hours' => 6.0,
            'quality' => 3,
            'notes' => 'Input awal',
        ])->assertStatus(201);

        // Input kedua di hari yang sama harus update (200 OK), bukan duplicate constraint error
        $this->withToken($token)->postJson('/api/patient/sleep', [
            'log_date' => $today,
            'duration_hours' => 7.5,
            'quality' => 4,
            'notes' => 'Diperbarui malam hari',
        ])->assertStatus(200)
            ->assertJsonPath('data.duration_hours', '7.5');

        $this->assertSame(1, SleepLog::where('user_id', $patient->id)->count());

        // Tiga entri mood menghasilkan rata-rata 2/3 = 0.67 (menguji presisi decimal:2 pada WeeklyEvaluation)
        foreach ([2, 0, 0] as $idx => $lvl) {
            DailyEntry::create([
                'user_id' => $patient->id,
                'entry_date' => $today,
                'entry_time' => sprintf('%02d:00', 8 + $idx * 4),
                'period' => ['Pagi', 'Siang', 'Sore'][$idx],
                'mood_level' => $lvl,
                'energy_level' => 3,
                'mood' => DailyEntry::mapMoodLevelToPhase($lvl),
                'mood_score' => DailyEntry::mapMoodLevelToScore10($lvl),
            ]);
        }

        // Panggil laporan 2 kali berturut-turut di hari yang sama -> tidak boleh bentrok unique week_start
        $this->withToken($token)->getJson('/api/patient/reports?days=14')
            ->assertStatus(200)
            ->assertJsonPath('data.weekly_evaluation.avg_mood_score', '0.67');

        $this->withToken($token)->getJson('/api/patient/reports?days=14')
            ->assertStatus(200)
            ->assertJsonPath('data.weekly_evaluation.avg_mood_score', '0.67');
    }
}
