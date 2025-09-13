<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Area;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Auth;

class BandejaController extends Controller
{
    /**
     * Devuelve el historial completo y paginado de todos los documentos
     * que alguna vez fueron enviados a las áreas accesibles por el usuario.
     */
    public function index()
    {
        $usuario = Auth::user();

        // --- LÓGICA ROBUSTA Y MULTI-ÁREA ---
        // 1. Obtenemos las áreas asociadas al usuario por tabla pivote
        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');

        // 2. Añadimos su área principal
        $accessibleAreaIds->push($usuario->primary_area_id);

        // 3. Nos aseguramos que no haya duplicados
        $areaIdsUnicos = $accessibleAreaIds->unique();

        // 4. Obtenemos documentos con movimientos hacia cualquiera de esas áreas
        $documentoIds = Movimiento::whereIn('area_destino_id', $areaIdsUnicos)
            ->distinct()
            ->pluck('documento_id');

        $documentos = Documento::whereIn('id', $documentoIds)
            ->with(['tipoDocumento', 'areaOrigen', 'areaActual'])
            ->addSelect([
                'latest_proveido' => Movimiento::select('proveido')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->orderBy('id', 'desc')
                    ->limit(1),

                'fue_recibido_en_area_actual' => Movimiento::selectRaw('1')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->whereIn('area_destino_id', $areaIdsUnicos)
                    ->where('estado_movimiento', 'RECIBIDO')
                    ->limit(1)
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        // Post-procesamos resultados
        $documentos->through(function ($doc) {
            $doc->fue_recibido_en_area_actual = (bool) $doc->fue_recibido_en_area_actual;
            $doc->latestMovement = (object) ['proveido' => $doc->latest_proveido];
            unset($doc->latest_proveido);
            return $doc;
        });

        return $documentos;
    }

    /**
     * Devuelve solo los documentos que están pendientes de recepción
     * en cualquiera de las áreas del usuario.
     */
    public function getPendientes()
    {
        $usuario = Auth::user();
        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);
        $areaIdsUnicos = $accessibleAreaIds->unique();

        $documentos = Documento::whereIn('area_actual_id', $areaIdsUnicos)
            ->where('estado_general', 'EN TRAMITE')
            ->whereNull('respuesta_para_documento_id')
            // --- AÑADIDO: La condición clave ---
            // "Donde NO EXISTA un movimiento para este documento, que sea de tipo RECIBIDO
            // y cuyo destino sea una de las áreas del usuario"
            ->whereDoesntHave('movimientos', function ($query) use ($areaIdsUnicos) {
                $query->where('estado_movimiento', 'RECIBIDO')
                    ->whereIn('area_destino_id', $areaIdsUnicos);
            })
            // --- FIN DE LA CONDICIÓN ---
            ->with(['tipoDocumento', 'areaOrigen'])
            ->addSelect([
                'latest_proveido' => Movimiento::select('proveido')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->orderBy('id', 'desc')
                    ->limit(1)
            ])
            ->get();

        // ... (el resto del método no cambia)
        $documentos = $documentos->map(function ($doc) {
            $doc->latestMovement = (object) ['proveido' => $doc->latest_proveido];
            unset($doc->latest_proveido);
            return $doc;
        });

        return response()->json($documentos);
    }

    /**
     * Devuelve datos agrupados para la vista (tabla principal, pendientes y áreas).
     */
    public function getDataForView()
    {
        $usuario = Auth::user();

        // Áreas accesibles
        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);
        $areaIdsUnicos = $accessibleAreaIds->unique();

        // Documentos recibidos (tabla principal)
        $todosLosDocumentos = Documento::whereHas('movimientos', function ($query) use ($areaIdsUnicos) {
            $query->whereIn('area_destino_id', $areaIdsUnicos);
        })
            ->with(['tipoDocumento', 'areaOrigen', 'latestMovement'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Documentos pendientes (modal)
        $documentosPendientes = Documento::whereIn('area_actual_id', $areaIdsUnicos)
            ->where('estado_general', 'EN TRAMITE')
            ->with(['tipoDocumento', 'areaOrigen', 'latestMovement'])
            ->get();

        // Áreas disponibles
        $areas = Area::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return response()->json([
            'todosLosDocumentos' => $todosLosDocumentos,
            'documentosPendientes' => $documentosPendientes,
            'areas' => $areas,
        ]);
    }
}
