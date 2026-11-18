<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SleepLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'log_date', 'sleep_start', 'sleep_end', 'duration_hours', 'quality', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'duration_hours' => 'decimal:1',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}