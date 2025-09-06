<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id(); // Equivale a INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
            $table->string('nombre', 100)->unique(); // UNIQUE KEY `uq_nombre_area`
            $table->timestamp('fecha_registro')->useCurrent(); // DEFAULT current_timestamp()
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            // Laravel automáticamente añade las columnas created_at y updated_at,
            // que son muy útiles para auditoría. Podemos dejarlas o quitarlas.
            // Por ahora, las dejaremos.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
