<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('area_user', function (Blueprint $table) {
            $table->id();

            // --- LÍNEA CORREGIDA ---
            // Le decimos explícitamente que la tabla de usuarios se llama 'usuarios'.
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');

            // La restricción para 'area_id' probablemente es correcta, ya que la tabla es 'areas'.
            $table->foreignId('area_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_user_pivot');
    }
};
