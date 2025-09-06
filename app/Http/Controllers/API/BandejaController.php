<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Area; // Asegúrate de importar el modelo Area
use App\Models\Movimiento;
use Illuminate\Support\Facades\Auth;

class BandejaController extends Controller
{

    /**
     * Devuelve el historial completo y paginado de todos los documentos
     * que alguna vez fueron enviados al área del usuario.
     * VERSIÓN CORREGIDA Y ROBUSTA
     */
    public function index()
    {
        $usuario = Auth::user();
        $userAreaId = $usuario->area_id;

        $documentoIds = Movimiento::where('area_destino_id', $userAreaId)
            ->distinct()
            ->pluck('documento_id');

        $documentos = Documento::whereIn('id', $documentoIds)
            ->with(['tipoDocumento', 'areaOrigen', 'areaActual'])
            ->addSelect([
                'latest_proveido' => Movimiento::select('proveido')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->orderBy('id', 'desc')
                    ->limit(1)
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        $documentos->through(function ($doc) {
            $doc->latestMovement = (object) ['proveido' => $doc->latest_proveido];
            unset($doc->latest_proveido);
            return $doc;
        });

        return $documentos;
    }



    /**
     * Devuelve solo los documentos que están pendientes de recepción.
     * Esta lógica no cambia, ya que es para el modal.
     */
    public function getPendientes()
    {
        $usuario = Auth::user();

        $documentos = Documento::where('area_actual_id', $usuario->area_id)
            ->where('estado_general', 'EN TRAMITE')
            ->with(['tipoDocumento', 'areaOrigen'])
            ->addSelect([
                'latest_proveido' => Movimiento::select('proveido')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->orderBy('id', 'desc')
                    ->limit(1)
            ])
            ->get();

        $documentos = $documentos->map(function ($doc) {
            $doc->latestMovement = (object) ['proveido' => $doc->latest_proveido];
            unset($doc->latest_proveido);
            return $doc;
        });

        return response()->json($documentos);
    }
    public function getDataForView()
    {
        $usuario = Auth::user();

        // Obtenemos todos los documentos recibidos (para la tabla principal)
        $todosLosDocumentos = Documento::whereHas('movimientos', function ($query) use ($usuario) {
            $query->where('area_destino_id', $usuario->area_id);
        })
            ->with(['tipoDocumento', 'areaOrigen', 'latestMovement'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Obtenemos solo los documentos pendientes (para el modal)
        $documentosPendientes = Documento::where('area_actual_id', $usuario->area_id)
            ->where('estado_general', 'EN TRAMITE')
            ->with(['tipoDocumento', 'areaOrigen', 'latestMovement'])
            ->get();

        // Obtenemos las áreas (para el modal de derivar)
        $areas = Area::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        // Devolvemos todo en un solo objeto JSON
        return response()->json([
            'todosLosDocumentos' => $todosLosDocumentos,
            'documentosPendientes' => $documentosPendientes,
            'areas' => $areas,
        ]);
    }
}
