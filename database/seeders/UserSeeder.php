<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear un Área de prueba
        $area = Area::create(['nombre' => 'Sistemas']);

        // 2. Crear un Empleado de prueba
        $empleado = Empleado::create([
            'dni' => '12345678',
            'nombres' => 'Admin',
            'apellido_paterno' => 'TRAMUSA',
            'apellido_materno' => 'S.A.',
            'email' => 'admin@tramusa.com',
        ]);

        // 3. Crear un Usuario Administrador
        User::create([
            'empleado_id' => $empleado->id,
            'area_id' => $area->id,
            'nombre_usuario' => 'admin',
            'password' => Hash::make('password'), // La contraseña será "password"
            'rol' => 'Administrador',
            'estado' => 'ACTIVO',
        ]);

        $areaRRHH = Area::where('nombre', 'Recursos Humanos')->first();

        $empleadoUser = Empleado::create([
            'dni' => '87654321',
            'nombres' => 'Usuario',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'RRHH',
            'email' => 'user@tramusa.com',
        ]);

        User::create([
            'empleado_id' => $empleadoUser->id,
            'area_id' => $areaRRHH->id,
            'nombre_usuario' => 'user_rrhh',
            'password' => Hash::make('password'),
            'rol' => 'Usuario',
            'estado' => 'ACTIVO',
        ]);
    }
}
