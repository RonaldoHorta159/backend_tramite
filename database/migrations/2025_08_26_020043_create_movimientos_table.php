<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();

            // --- Llaves Foráneas ---
            $table->foreignId('documento_id')
                ->constrained('documentos')
                ->onDelete('cascade'); // Si se borra un documento, se borra su historial

            $table->foreignId('area_origen_id')->constrained('areas');
            $table->foreignId('area_destino_id')->constrained('areas');
            $table->foreignId('usuario_id')->constrained('usuarios'); // Usuario que realiza la acción

            $table->text('proveido')->nullable();
            $table->enum('estado_movimiento', [
                'ENVIADO',
                'RECIBIDO',
                'DERIVADO',
                'FINALIZADO',
                'RECHAZADO',
                'ARCHIVADO'
            ]);
            $table->string('archivo_adjunto')->nullable();
            $table->timestamps(); // Para fecha_movimiento (created_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
