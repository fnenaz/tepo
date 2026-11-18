<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('coping_recommendation_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_recommendations');
    }
};