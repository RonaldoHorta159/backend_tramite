<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_unico', 20)->unique();
            $table->string('nro_documento', 50);
            $table->string('asunto');
            $table->integer('nro_folios');
            $table->string('archivo_pdf')->nullable();

            // --- Llaves Foráneas ---
            $table->foreignId('tipo_documento_id')->constrained('tipos_documento');
            $table->foreignId('usuario_creador_id')->constrained('usuarios');
            $table->foreignId('remitente_id')->nullable()->constrained('remitentes');
            $table->foreignId('area_origen_id')->constrained('areas');
            $table->foreignId('area_actual_id')->constrained('areas');

            $table->enum('estado_general', ['EN TRAMITE', 'FINALIZADO', 'ARCHIVADO', 'RECHAZADO'])
                ->default('EN TRAMITE');

            $table->timestamps(); // Para fecha_registro (created_at) y fecha_actualizacion (updated_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
