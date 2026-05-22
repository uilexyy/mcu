<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('package_id')->constrained('mcu_packages');
            $table->string('status', 50)->default('pending');
            $table->date('tanggal_jadwal')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->string('foto_ktp', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_registrations');
    }
};
