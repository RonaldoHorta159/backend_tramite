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
        Schema::table('documentos', function (Blueprint $table) {
            // Este campo guardará el número correlativo por área (1, 2, 3, ...)
            $table->integer('correlativo_area')->unsigned()->nullable()->after('nro_documento');

            // Este índice es CRUCIAL para la velocidad de la consulta.
            $table->index(['area_origen_id', 'created_at']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            //
        });
    }
};
