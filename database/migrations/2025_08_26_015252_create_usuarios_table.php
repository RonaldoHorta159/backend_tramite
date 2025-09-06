<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();

            // Definición de la llave foránea para empleado
            $table->foreignId('empleado_id')
                ->constrained('empleados') // Se enlaza a la tabla 'empleados'
                ->onDelete('cascade');     // Si se borra un empleado, se borra su usuario

            // Definición de la llave foránea para area
            $table->foreignId('area_id')
                ->constrained('areas')      // Se enlaza a la tabla 'areas'
                ->onDelete('cascade');     // Si se borra un área, se borran sus usuarios

            $table->string('nombre_usuario', 50)->unique();
            $table->string('password'); // <-- ¡LÍNEA CORREGIDA!
            $table->enum('rol', ['Administrador', 'Usuario']);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
