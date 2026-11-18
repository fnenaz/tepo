<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoodRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_entry_id', 'coping_recommendation_id',
    ];

    public function dailyEntry()
    {
        return $this->belongsTo(DailyEntry::class);
    }

    public function copingRecommendation()
    {
        return $this->belongsTo(CopingRecommendation::class);
    }
}