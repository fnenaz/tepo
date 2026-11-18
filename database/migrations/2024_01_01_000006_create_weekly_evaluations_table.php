<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('avg_mood_score', 4, 2)->nullable();
            $table->decimal('avg_sleep_hours', 4, 1)->nullable();
            $table->integer('total_entries')->default(0);
            $table->integer('medication_compliance_pct')->default(0)->comment('Persentase kepatuhan obat 0-100');
            $table->integer('manic_days')->default(0);
            $table->integer('depressive_days')->default(0);
            $table->boolean('urgent_control')->default(false)->comment('True = perlu percepat kontrol psikolog');
            $table->text('system_recommendation')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_evaluations');
    }
};
