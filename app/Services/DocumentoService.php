<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Documento;
use App\Models\Movimiento;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentoService
{
    /**
     * Orquesta la creación de un nuevo documento y su movimiento inicial.
     *
     * @param array $data Los datos validados del FormRequest.
     * @param User $usuario El usuario que está creando el documento.
     * @param UploadedFile|null $archivoPdf El archivo PDF adjunto, si existe.
     * @return Documento El documento recién creado.
     */
    public function crearNuevoTramite(array $data, User $usuario, ?UploadedFile $archivoPdf): Documento
    {
        // Usamos una transacción para asegurar la integridad de los datos.
        return DB::transaction(function () use ($data, $usuario, $archivoPdf) {

            // 1. Obtener datos necesarios ANTES de crear nada.
            $areaOrigen = Area::findOrFail($data['area_origen_id']);
            $correlativo = $this->getSiguienteCorrelativoArea($areaOrigen->id);
            $codigoUnico = "{$areaOrigen->codigo}-" . date('Y') . '-' . str_pad($correlativo, 6, '0', STR_PAD_LEFT);

            // 2. Guardar el archivo si existe.
            $rutaArchivo = null;
            if ($archivoPdf) {
                $rutaArchivo = $archivoPdf->store('public/pdfs');
            }

            // 3. Crear el Documento con todos sus datos finales.
            $documento = Documento::create([
                'tipo_documento_id' => $data['tipo_documento_id'],
                'correlativo_area' => $correlativo,
                'codigo_unico' => $codigoUnico,
                'asunto' => $data['asunto'],
                'nro_folios' => $data['nro_folios'],
                'archivo_pdf' => $rutaArchivo,
                'usuario_creador_id' => $usuario->id,
                'area_origen_id' => $data['area_origen_id'],
                'area_actual_id' => $data['area_destino_id'],
                // Deprecado, el correlativo_area es el nro_documento ahora
                'nro_documento' => $correlativo,
            ]);

            // 4. Crear el Movimiento inicial.
            Movimiento::create([
                'documento_id' => $documento->id,
                'area_origen_id' => $data['area_origen_id'],
                'area_destino_id' => $data['area_destino_id'],
                'usuario_id' => $usuario->id,
                'proveido' => 'Trámite generado e iniciado.',
                'estado_movimiento' => 'ENVIADO',
            ]);

            // 5. Devolver el documento creado.
            return $documento;
        });
    }

    /**
     * Calcula el siguiente número correlativo para un área en el año actual.
     */
    public function getSiguienteCorrelativo(int $areaId, int $tipoDocumentoId): int
    {
        // Buscamos el tipo de documento para verificar su nombre.
        $tipoDocumento = TipoDocumento::find($tipoDocumentoId);

        // Preparamos la consulta base del query.
        $query = Documento::where('area_origen_id', $areaId)
            ->where('tipo_documento_id', $tipoDocumentoId);

        // Condicional para la regla de reinicio
        if ($tipoDocumento && str_starts_with($tipoDocumento->nombre, 'REGISTRO CONTABLE')) {
            // REINICIO MENSUAL para registros contables
            $query->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'));
        } else {
            // REINICIO ANUAL para todos los demás
            $query->whereYear('created_at', date('Y'));
        }

        // Obtenemos el máximo correlativo y le sumamos uno.
        $ultimoCorrelativo = $query->max('correlativo_area') ?? 0;

        return $ultimoCorrelativo + 1;
    }
}
