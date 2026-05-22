<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_radiology_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('mcu_registrations')->cascadeOnDelete();
            $table->foreignId('radio_user_id')->constrained('users');
            $table->text('interpretasi')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_radiology_results');
    }
};
