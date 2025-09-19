<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AreaAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. CREACIÓN DE TODAS LAS ÁREAS ---
        $oficinas = [
            ['nombre' => 'TRAMITE DOCUMENTARIO', 'codigo' => 'TD'],
            ['nombre' => 'GERENCIA GENERAL', 'codigo' => 'GG'],
            ['nombre' => 'GESTION DEL TALENTO HUMANO', 'codigo' => 'GTH'],
            ['nombre' => 'LOGISTICA Y ABASTECIMIENTO', 'codigo' => 'LOG'],
            ['nombre' => 'CONTABILIDAD Y COSTOS', 'codigo' => 'CONTA'],
            ['nombre' => 'ASESORIA JURIDICA', 'codigo' => 'AJ'],
            ['nombre' => 'TESORERIA Y CONTROL FINANCIERO', 'codigo' => 'TES'],
            ['nombre' => 'PLANEAMIENTO Y PRESUPUESTO', 'codigo' => 'PP'],
            ['nombre' => 'ARCHIVO', 'codigo' => 'ARCH'],
            ['nombre' => 'ASISTENCIA SOCIAL', 'codigo' => 'AS'],
            ['nombre' => 'SERVICIOS TURISTICOS', 'codigo' => 'ST'],
            ['nombre' => 'ADMINISTRACION', 'codigo' => 'ADMIN'],
            ['nombre' => 'SECRETARIA GENERAL', 'codigo' => 'SG'],
            ['nombre' => 'TECNOLOGIAS DE INFORMACION', 'codigo' => 'TI'],
            ['nombre' => 'DEPARTAMENTO DE ALMACEN', 'codigo' => 'ALM'],
            ['nombre' => 'CULTURA Y DEPORTE', 'codigo' => 'CD'],
            ['nombre' => 'OFICINA MACHUPICCHU', 'codigo' => 'OMP'],
            ['nombre' => 'GUARDIANIA', 'codigo' => 'GUAR'],
            ['nombre' => 'DEPARTAMENTO DE PATRIMONIO', 'codigo' => 'PATRI'],
            ['nombre' => 'RELACIONES PUBLICAS Y MARKETING', 'codigo' => 'MKT'],
            ['nombre' => 'ENLACE INTERINSTITUCIONAL', 'codigo' => 'EI'],
            ['nombre' => 'MANTENIMIENTO', 'codigo' => 'MANT'],
            ['nombre' => 'DIRECTORIO', 'codigo' => 'DIR'],
            ['nombre' => 'DEPARTAMENTO DE PSICOLOGIA', 'codigo' => 'PSICO'],
            ['nombre' => 'PROCEDIMIENTO ADMINISTRATIVO DISCIPLINARIO', 'codigo' => 'PAD'],
            ['nombre' => 'COMITE DE SEGURIDAD Y SALUD', 'codigo' => 'CSS'],
            ['nombre' => 'SEGURIDAD Y SALUD EN EL TRABAJO', 'codigo' => 'SST'],
        ];

        $areaMap = [];
        foreach ($oficinas as $oficinaData) {
            $area = Area::create([
                'nombre' => $oficinaData['nombre'],
                'codigo' => $oficinaData['codigo'],
            ]);
            $areaMap[$oficinaData['nombre']] = $area->id;
        }

        // --- 2. DATOS REALES DE USUARIOS (LISTA LIMPIA SIN DUPLICADOS) ---
        $usuariosUnicos = [
            ['dni' => '72014598', 'oficina_nombre' => 'CONTABILIDAD Y COSTOS'],
            ['dni' => '46799610', 'oficina_nombre' => 'TRAMITE DOCUMENTARIO'],
            ['dni' => '45795743', 'oficina_nombre' => 'TECNOLOGIAS DE INFORMACION'], // Administrador
            ['dni' => '72519718', 'oficina_nombre' => 'TRAMITE DOCUMENTARIO'],
            ['dni' => '77696559', 'oficina_nombre' => 'TRAMITE DOCUMENTARIO'],
            ['dni' => '47996942', 'oficina_nombre' => 'SEGURIDAD Y SALUD EN EL TRABAJO'],
            ['dni' => '72743723', 'oficina_nombre' => 'GERENCIA GENERAL'],
            ['dni' => '72317761', 'oficina_nombre' => 'GERENCIA GENERAL'],
            ['dni' => '42593883', 'oficina_nombre' => 'SECRETARIA GENERAL'],
            ['dni' => '23842358', 'oficina_nombre' => 'LOGISTICA Y ABASTECIMIENTO'],
            ['dni' => '23836817', 'oficina_nombre' => 'SERVICIOS TURISTICOS'],
            ['dni' => '72373050', 'oficina_nombre' => 'GERENCIA GENERAL'],
            ['dni' => '43412746', 'oficina_nombre' => 'CONTABILIDAD Y COSTOS'],
            ['dni' => '72963017', 'oficina_nombre' => 'GESTION DEL TALENTO HUMANO'],
            ['dni' => '24001823', 'oficina_nombre' => 'ASESORIA JURIDICA'],
            ['dni' => '40803582', 'oficina_nombre' => 'TESORERIA Y CONTROL FINANCIERO'],
            ['dni' => '47372011', 'oficina_nombre' => 'ARCHIVO'],
            ['dni' => '46451993', 'oficina_nombre' => 'DEPARTAMENTO DE ALMACEN'],
            ['dni' => '45789315', 'oficina_nombre' => 'CULTURA Y DEPORTE'],
            ['dni' => '23990145', 'oficina_nombre' => 'PLANEAMIENTO Y PRESUPUESTO'],
            ['dni' => '72317767', 'oficina_nombre' => 'GERENCIA GENERAL'],
            ['dni' => '12345678', 'oficina_nombre' => 'SECRETARIA GENERAL'],
            ['dni' => '40078420', 'oficina_nombre' => 'ASISTENCIA SOCIAL'],
            ['dni' => '25311179', 'oficina_nombre' => 'OFICINA MACHUPICCHU'],
            ['dni' => '73793455', 'oficina_nombre' => 'CONTABILIDAD Y COSTOS'],
            ['dni' => '23858687', 'oficina_nombre' => 'GUARDIANIA'],
            ['dni' => '73081085', 'oficina_nombre' => 'TESORERIA Y CONTROL FINANCIERO'],
            ['dni' => '60341003', 'oficina_nombre' => 'LOGISTICA Y ABASTECIMIENTO'],
            ['dni' => '46924768', 'oficina_nombre' => 'RELACIONES PUBLICAS Y MARKETING'],
            ['dni' => '44576705', 'oficina_nombre' => 'ENLACE INTERINSTITUCIONAL'],
            ['dni' => '76675437', 'oficina_nombre' => 'DEPARTAMENTO DE ALMACEN'],
            ['dni' => '45099842', 'oficina_nombre' => 'MANTENIMIENTO'],
            ['dni' => '47254491', 'oficina_nombre' => 'TRAMITE DOCUMENTARIO'],
            ['dni' => '76162191', 'oficina_nombre' => 'TRAMITE DOCUMENTARIO'],
            ['dni' => '43793059', 'oficina_nombre' => 'MANTENIMIENTO'],
            ['dni' => '73232970', 'oficina_nombre' => 'ASISTENCIA SOCIAL'],
            ['dni' => '72429153', 'oficina_nombre' => 'CULTURA Y DEPORTE'],
            ['dni' => '42260206', 'oficina_nombre' => 'DIRECTORIO'],
            ['dni' => '73801664', 'oficina_nombre' => 'TECNOLOGIAS DE INFORMACION'],
            ['dni' => '72214663', 'oficina_nombre' => 'SECRETARIA GENERAL'],
            ['dni' => '23823650', 'oficina_nombre' => 'DEPARTAMENTO DE PATRIMONIO'],
            ['dni' => '72383718', 'oficina_nombre' => 'TRAMITE DOCUMENTARIO'],
            ['dni' => '45919890', 'oficina_nombre' => 'DEPARTAMENTO DE PATRIMONIO'],
            ['dni' => '46964513', 'oficina_nombre' => 'DEPARTAMENTO DE PSICOLOGIA'],
            ['dni' => '70750320', 'oficina_nombre' => 'PROCEDIMIENTO ADMINISTRATIVO DISCIPLINARIO'],
            ['dni' => '75730455', 'oficina_nombre' => 'TESORERIA Y CONTROL FINANCIERO'],
            ['dni' => '71443333', 'oficina_nombre' => 'GESTION DEL TALENTO HUMANO'],
        ];

        // --- 3. CREACIÓN DE EMPLEADOS Y USUARIOS ---
        foreach ($usuariosUnicos as $data) {
            $dni = $data['dni'];
            $nombreOficina = $data['oficina_nombre'];

            // Creamos el Empleado
            $empleado = Empleado::create([
                'dni' => $dni,
                'nombres' => 'Empleado',
                'apellido_paterno' => $nombreOficina,
                'apellido_materno' => $dni,
                'email' => $dni . '@tramusa.com',
            ]);

            // Determinamos el rol
            $rol = ($dni === '45795743' && $nombreOficina === 'TECNOLOGIAS DE INFORMACION') ? 'Administrador' : 'Usuario';

            // Creamos el Usuario
            User::create([
                'empleado_id' => $empleado->id,
                'primary_area_id' => $areaMap[$nombreOficina],
                'nombre_usuario' => $dni,
                'password' => Hash::make('password'),
                'rol' => $rol,
            ]);
        }
    }
}
