<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('remitentes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_persona', ['NATURAL', 'JURIDICA']);
            $table->string('dni', 8)->nullable()->unique();
            $table->string('nombres_razon_social');
            $table->string('apellido_paterno', 100)->nullable();
            $table->string('apellido_materno', 100)->nullable();
            $table->string('ruc', 11)->nullable()->unique();
            $table->string('celular', 9)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps(); // Útil para saber cuándo se registró un remitente
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remitentes');
    }
};
