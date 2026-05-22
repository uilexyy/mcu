<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('mcu_registrations')->cascadeOnDelete()->unique();
            $table->string('pdf_path', 255)->nullable();
            $table->dateTime('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_results');
    }
};
