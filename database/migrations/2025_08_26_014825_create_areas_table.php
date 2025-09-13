<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();

            // --- AÑADE ESTA LÍNEA ---
            // Aquí creamos la columna 'codigo' que faltaba.
            // La hacemos 'unique' para asegurar que no haya dos oficinas con el mismo código.
            $table->string('codigo', 10)->unique();

            $table->timestamp('fecha_registro')->useCurrent();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
