<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('entry_date');
            $table->time('entry_time')->default('08:00:00');
            $table->string('period', 30)->default('Pagi')->comment('Pagi, Siang, Sore, Malam');
            $table->tinyInteger('mood_level')->default(0)->comment('Skala klinis -3 s/d +3');
            $table->tinyInteger('energy_level')->default(3)->comment('Skala energi 1 s/d 5');
            $table->enum('mood', ['happy', 'sad', 'depressive', 'manic', 'neutral']);
            $table->integer('mood_score')->comment('Skor terkonversi 1-10');
            $table->text('daily_notes')->nullable()->comment('Catatan atau pemicu suasana hati');
            $table->timestamps();

            $table->index(['user_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_entries');
    }
};
