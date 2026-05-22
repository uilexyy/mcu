<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('mcu_packages')->cascadeOnDelete();
            $table->string('nama_pemeriksaan', 100);
            $table->string('satuan', 50)->nullable();
            $table->string('nilai_normal', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_package_items');
    }
};
