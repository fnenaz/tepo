<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_date',
        'entry_time',
        'period',
        'mood_level',
        'energy_level',
        'mood',
        'mood_score',
        'daily_notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'mood_level' => 'integer',
            'energy_level' => 'integer',
            'mood_score' => 'integer',
        ];
    }

    public static function moodScaleLabels(): array
    {
        return [
            -3 => 'Sangat rendah / depresi',
            -2 => 'Rendah',
            -1 => 'Agak turun',
            0 => 'Stabil',
            1 => 'Agak tinggi',
            2 => 'Tinggi',
            3 => 'Sangat tinggi / sulit dikendalikan',
        ];
    }

    public static function energyScaleLabels(): array
    {
        return [
            1 => 'Sangat lemas',
            2 => 'Lemas',
            3 => 'Cukup / normal',
            4 => 'Berenergi',
            5 => 'Sangat berenergi',
        ];
    }

    public static function mapMoodLevelToPhase(int $level): string
    {
        return match (true) {
            $level <= -2 => 'depressive',
            $level === -1 => 'sad',
            $level === 0 => 'neutral',
            $level === 1 => 'happy',
            default => 'manic',
        };
    }

    public static function mapMoodLevelToScore10(int $level): int
    {
        return match ($level) {
            -3 => 1,
            -2 => 3,
            -1 => 4,
            0 => 5,
            1 => 7,
            2 => 8,
            3 => 10,
            default => 5,
        };
    }

    public function getFormattedMoodLevelAttribute(): string
    {
        return $this->mood_level > 0 ? '+' . $this->mood_level : (string) $this->mood_level;
    }

    public function getMoodLabelAttribute(): string
    {
        return self::moodScaleLabels()[$this->mood_level] ?? 'Stabil';
    }

    public function getEnergyLabelAttribute(): string
    {
        return self::energyScaleLabels()[$this->energy_level] ?? 'Normal';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moodRecommendations()
    {
        return $this->hasMany(MoodRecommendation::class);
    }

    public function copingRecommendations()
    {
        return $this->belongsToMany(
            CopingRecommendation::class,
            'mood_recommendations',
            'daily_entry_id',
            'coping_recommendation_id'
        );
    }
}
