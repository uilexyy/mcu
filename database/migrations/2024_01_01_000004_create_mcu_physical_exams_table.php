<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_physical_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('mcu_registrations')->cascadeOnDelete()->unique();
            $table->foreignId('doctor_id')->constrained('users');
            $table->string('tekanan_darah', 50)->nullable();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->decimal('imt', 4, 2)->nullable();
            $table->text('anamnesis')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_physical_exams');
    }
};
