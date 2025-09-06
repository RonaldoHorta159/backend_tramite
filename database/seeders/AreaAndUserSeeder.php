<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AreaAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oficinas = [
            'TRAMITE DOCUMENTARIO',
            'GERENCIA GENERAL',
            'GESTION DEL TALENTO HUMANO',
            'LOGISTICA Y ABASTECIMIENTO',
            'CONTABILIDAD Y COSTOS',
            'ASESORIA JURIDICA',
            'TESORERIA Y CONTROL FINANCIERO',
            'PLANEAMIENTO Y PRESUPUESTO',
            'ARCHIVO',
            'ASISTENCIA SOCIAL',
            'SERVICIOS TURISTICOS',
            'ADMINISTRACION',
            'SECRETARIA GENERAL',
            'TECNOLOGIAS DE INFORMACION', // <-- Esta será Admin
            'DEPARTAMENTO DE ALMACEN',
            'CULTURA Y DEPORTE',
            'OFICINA MACHUPICCHU',
            'GUARDIANIA',
            'DEPARTAMENTO DE PATRIMONIO',
            'RELACIONES PUBLICAS Y MARKETING',
            'ENLACE INTERINSTITUCIONAL',
            'MANTENIMIENTO',
            'DIRECTORIO',
            'DEPARTAMENTO DE PSICOLOGIA',
            'PROCEDIMIENTO ADMINISTRATIVO DISCIPLINARIO',
            'COMITE DE SEGURIDAD Y SALUD',
        ];

        // Un contador para generar DNIs únicos
        $dniCounter = 10000000;

        foreach ($oficinas as $nombreOficina) {
            // 1. Creamos el Área
            $area = Area::create(['nombre' => $nombreOficina]);

            // 2. Creamos un Empleado genérico para esa área
            $dni = $dniCounter++;
            $empleado = Empleado::create([
                'dni' => $dni,
                'nombres' => 'Usuario',
                'apellido_paterno' => 'Oficina',
                'apellido_materno' => $nombreOficina,
                'email' => Str::slug($nombreOficina) . '@tramusa.com',
            ]);

            // 3. Determinamos el rol
            $rol = ($nombreOficina === 'TECNOLOGIAS DE INFORMACION') ? 'Administrador' : 'Usuario';

            // 4. Creamos el Usuario para el sistema con su DNI como nombre de usuario
            User::create([
                'empleado_id' => $empleado->id,
                'area_id' => $area->id,
                'nombre_usuario' => $dni, // <-- CAMBIO CLAVE: El DNI es el usuario
                'password' => Hash::make('password'), // Contraseña por defecto para todos
                'rol' => $rol, // Asignamos el rol correspondiente
            ]);
        }
    }
}
