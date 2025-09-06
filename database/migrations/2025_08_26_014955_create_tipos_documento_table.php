<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Renombramos a 'tipos_documento' para seguir la convención de Laravel
        Schema::create('tipos_documento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Ej: Oficio, Solicitud, Memorando');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_documento');
    }
};
