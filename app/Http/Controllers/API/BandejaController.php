<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Area;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class BandejaController extends Controller
{
    /**
     * Devuelve el historial completo y paginado de todos los documentos
     * que alguna vez fueron enviados a las áreas accesibles por el usuario.
     * (OPTIMIZADO + DATOS EXTRA)
     */
    public function index()
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $accessibleAreaIds = $this->getAccessibleAreaIds($usuario);

        // ✅ OPTIMIZACIÓN: Esta consulta ahora funcionará porque DB está importado.
        $documentos = Documento::query()
            ->whereExists(function ($query) use ($accessibleAreaIds) {
                $query->select(DB::raw(1))
                    ->from('movimientos')
                    ->whereColumn('movimientos.documento_id', 'documentos.id')
                    ->whereIn('movimientos.area_destino_id', $accessibleAreaIds);
            })
            ->with(['tipoDocumento', 'areaOrigen', 'areaActual', 'latestMovement'])
            ->addSelect([
                'fue_recibido_en_area_actual' => Movimiento::selectRaw('1')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->whereIn('area_destino_id', $accessibleAreaIds)
                    ->where('estado_movimiento', 'RECIBIDO')
                    ->limit(1)
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        $documentos->through(function ($doc) {
            $doc->fue_recibido_en_area_actual = (bool) $doc->fue_recibido_en_area_actual;
            return $doc;
        });

        return $documentos;
    }

    /**
     * Devuelve solo los documentos que están pendientes de recepción
     * en cualquiera de las áreas del usuario.
     * (OPTIMIZADO + PROVEÍDO)
     */
    public function getPendientes()
    {
        $usuario = Auth::user();
        $areaIdsUnicos = $this->getAccessibleAreaIds($usuario);

        $documentos = Documento::whereIn('area_actual_id', $areaIdsUnicos)
            ->where('estado_general', 'EN TRAMITE')
            ->whereNull('respuesta_para_documento_id')
            ->whereDoesntHave('movimientos', function ($query) use ($areaIdsUnicos) {
                $query->where('estado_movimiento', 'RECIBIDO')
                    ->whereIn('area_destino_id', $areaIdsUnicos);
            })
            // --- CAMBIO CLAVE: Cargamos el último movimiento y su origen ---
            ->with(['tipoDocumento', 'latestMovement.areaOrigen'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($documentos);
    }

    /**
     * Devuelve datos agrupados para la vista (tabla principal, pendientes y áreas).
     * (OPTIMIZADO)
     */
    public function getDataForView()
    {
        $usuario = Auth::user();
        $areaIdsUnicos = $this->getAccessibleAreaIds($usuario);

        $todosLosDocumentos = Documento::whereHas('movimientos', function ($query) use ($areaIdsUnicos) {
            $query->whereIn('area_destino_id', $areaIdsUnicos);
        })
            ->with(['tipoDocumento', 'areaOrigen', 'latestMovement'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $documentosPendientes = Documento::whereIn('area_actual_id', $areaIdsUnicos)
            ->where('estado_general', 'EN TRAMITE')
            ->with(['tipoDocumento', 'areaOrigen', 'latestMovement'])
            ->get();

        $areas = Area::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return response()->json([
            'todosLosDocumentos' => $todosLosDocumentos,
            'documentosPendientes' => $documentosPendientes,
            'areas' => $areas,
        ]);
    }

    /**
     * Helper para obtener los IDs de área accesibles por un usuario.
     */
    private function getAccessibleAreaIds($usuario)
    {
        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);
        return $accessibleAreaIds->unique();
    }
}
