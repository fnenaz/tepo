<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prescription_id',
        'log_date',
        'medication_name',
        'dosage',
        'prescription_rule',
        'quantity_taken',
        'scheduled_time',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
