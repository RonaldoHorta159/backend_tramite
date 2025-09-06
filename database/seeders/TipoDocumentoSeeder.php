<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'ANEXO'],
            ['nombre' => 'ACTA'],
            ['nombre' => 'CARTA'],
            ['nombre' => 'CARTA CIRCULAR'],
            ['nombre' => 'CARTA MÚLTIPLE'],
            ['nombre' => 'CARTA NOTARIAL'],
            ['nombre' => 'CIRCULAR'],
            ['nombre' => 'CONTRATO'],
            ['nombre' => 'CONVENIO'],
            ['nombre' => 'DECLARACIÓN JURADA'],
            ['nombre' => 'DICTAMEN'],
            ['nombre' => 'EXPEDIENTE'],
            ['nombre' => 'INFORME'],
            ['nombre' => 'INFORME CIRCULAR'],
            ['nombre' => 'INFORME MÚLTIPLE'],
            ['nombre' => 'INVITACION'],
            ['nombre' => 'MEMO MÚLTIPLE'],
            ['nombre' => 'MEMORANDUM'],
            ['nombre' => 'MEMORANDUM CIRCULAR'],
            ['nombre' => 'MEMORIAL'],
            ['nombre' => 'NOTIFICACIÓN'],
            ['nombre' => 'OFICIO'],
            ['nombre' => 'OFICIO CIRCULAR'],
            ['nombre' => 'OFICIO MÚLTIPLE'],
            ['nombre' => 'REQUERIMIENTO'],
            ['nombre' => 'RESOLUCION'],
            ['nombre' => 'SOLICITUD'],
            ['nombre' => 'ORDEN DE COMPRA'],
            ['nombre' => 'ORDEN DE SERVICIO'],
            ['nombre' => 'CERTIFICACIÓN PRESUPUESTAL'],
            ['nombre' => 'REGISTRO CONTABLE DE PROVISIONES'],
            ['nombre' => 'REGISTRO CONTABLE DE COMPRAS'],
            ['nombre' => 'REGISTRO CONTABLE DE VENTAS'],
            ['nombre' => 'REGISTRO CONTABLE DE HONORARIOS'],
            ['nombre' => 'REGISTRO CONTABLE DE PLANILLA'],
            ['nombre' => 'RENDICION'],
        ];

        // Usamos insert para mayor eficiencia con muchos registros
        TipoDocumento::insert($tipos);
    }
}
