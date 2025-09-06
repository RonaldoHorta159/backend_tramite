<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;
use App\Models\Area;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos Tipos de Documento
        TipoDocumento::create(['nombre' => 'Oficio']);
        TipoDocumento::create(['nombre' => 'Memorando']);
        TipoDocumento::create(['nombre' => 'Solicitud']);
        TipoDocumento::create(['nombre' => 'Informe']);

        // Crear algunas Áreas (además de la que ya crea UserSeeder)
        Area::create(['nombre' => 'Gerencia General']);
        Area::create(['nombre' => 'Recursos Humanos']);
        Area::create(['nombre' => 'Logística']);
    }
}
