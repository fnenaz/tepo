<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('medication_name')->comment('Nama obat, misal: Lithium Carbonate');
            $table->string('dosage')->comment('Dosis per minum, misal: 300 mg');
            $table->string('frequency')->comment('Aturan pakai resep, misal: 2x1 sehari (Pagi & Malam)');
            $table->string('prescribing_doctor')->nullable()->comment('Nama dokter/psikiater');
            $table->text('instructions')->nullable()->comment('Instruksi khusus resep');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->date('log_date');
            $table->string('medication_name')->comment('Nama obat');
            $table->string('dosage')->comment('Dosis obat');
            $table->string('prescription_rule')->nullable()->comment('Resep / jadwal aturan pakai obat ini');
            $table->integer('quantity_taken')->default(1)->comment('Jumlah unit/tablet yang diminum');
            $table->time('scheduled_time')->nullable()->comment('Jam konsumsi');
            $table->enum('status', ['on_time', 'missed', 'overdose'])->default('on_time');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_logs');
        Schema::dropIfExists('prescriptions');
    }
};
