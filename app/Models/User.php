<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'bio', 'avatar',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dailyEntries()
    {
        return $this->hasMany(DailyEntry::class);
    }

    public function sleepLogs()
    {
        return $this->hasMany(SleepLog::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function medicationLogs()
    {
        return $this->hasMany(MedicationLog::class);
    }

    public function weeklyEvaluations()
    {
        return $this->hasMany(WeeklyEvaluation::class);
    }

    public function consultationsAsPatient()
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }

    public function consultationsAsCounselor()
    {
        return $this->hasMany(Consultation::class, 'counselor_id');
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    public function isCounselor(): bool
    {
        return $this->role === 'counselor';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kode anonim dua arah untuk menjaga kerahasiaan identitas pasien dan mahasiswa psikologi.
     */
    public function getAnonymousPatientCodeAttribute(): string
    {
        $hash = strtoupper(substr(hash('sha256', 'tepo-patient-anon-' . $this->id), 0, 5));

        return 'Pasien Anonim #PA-' . $hash;
    }

    public function getAnonymousCounselorCodeAttribute(): string
    {
        $hash = strtoupper(substr(hash('sha256', 'tepo-counselor-anon-' . $this->id), 0, 5));

        return 'Pendamping Sebaya #PS-' . $hash;
    }
}
