<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CopingRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase', 'type', 'title', 'description', 'url', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeManic($query)
    {
        return $query->where('phase', 'manic');
    }

    public function scopeDepressive($query)
    {
        return $query->where('phase', 'depressive');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}