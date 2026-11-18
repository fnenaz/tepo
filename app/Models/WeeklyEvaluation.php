<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'week_start', 'week_end', 'avg_mood_score', 'avg_sleep_hours',
        'total_entries', 'medication_compliance_pct', 'manic_days', 'depressive_days',
        'urgent_control', 'system_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'avg_mood_score' => 'decimal:2',
            'avg_sleep_hours' => 'decimal:1',
            'urgent_control' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}