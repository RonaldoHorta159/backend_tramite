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
        // Índices para la tabla 'documentos'
        Schema::table('documentos', function (Blueprint $table) {
            // Acelera la búsqueda de documentos por su estado y área actual.
            // Esencial para la consulta de "pendientes".
            $table->index(['area_actual_id', 'estado_general'], 'idx_docs_area_estado');
        });

        // Índices para la tabla 'movimientos'
        Schema::table('movimientos', function (Blueprint $table) {
            // Acelera masivamente la búsqueda de todos los documentos
            // que han pasado por un área. Esencial para la consulta de "recibidos".
            $table->index('area_destino_id', 'idx_movs_area_destino');

            // Acelera la subconsulta "whereDoesntHave" para saber si un
            // documento ya fue recibido en un área. Esencial para "pendientes".
            $table->index(['documento_id', 'estado_movimiento'], 'idx_movs_doc_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex('idx_docs_area_estado');
        });

        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropIndex('idx_movs_area_destino');
            $table->dropIndex('idx_movs_doc_estado');
        });
    }
};
