<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sleep_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->time('sleep_start')->comment('Jam mulai tidur');
            $table->time('sleep_end')->comment('Jam bangun');
            $table->decimal('duration_hours', 4, 1)->comment('Durasi tidur dalam jam, dihitung otomatis');
            $table->integer('quality')->nullable()->comment('Kualitas tidur 1-5');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'log_date']); // 1 catatan tidur per hari per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sleep_logs');
    }
};