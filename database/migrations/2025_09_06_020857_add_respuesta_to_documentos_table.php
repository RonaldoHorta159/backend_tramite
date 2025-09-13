<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            // Esta columna (nullable) vinculará un documento de respuesta a su original.
            $table->foreignId('respuesta_para_documento_id')->nullable()->after('id')->constrained('documentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['respuesta_para_documento_id']);
            $table->dropColumn('respuesta_para_documento_id');
        });
    }
};
