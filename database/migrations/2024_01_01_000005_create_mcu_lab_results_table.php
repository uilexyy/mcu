<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('mcu_registrations')->cascadeOnDelete();
            $table->foreignId('lab_user_id')->constrained('users');
            $table->foreignId('item_id')->constrained('mcu_package_items');
            $table->string('nilai', 50)->nullable();
            $table->enum('keterangan', ['Normal', 'Tinggi', 'Rendah'])->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['registration_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_lab_results');
    }
};
