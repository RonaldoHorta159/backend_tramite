<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function getStats()
    {
        $usuario = Auth::user();

        $totalEnviados = Documento::where('usuario_creador_id', $usuario->id)->count();

        $totalEnBandeja = Documento::where('area_actual_id', $usuario->area_id)
            ->where('estado_general', 'EN TRAMITE')
            ->count();

        $totalFinalizados = Documento::where(function ($query) use ($usuario) {
            $query->where('usuario_creador_id', $usuario->id)
                ->orWhere('area_actual_id', $usuario->area_id);
        })->where('estado_general', 'FINALIZADO')->count();


        return response()->json([
            'totalEnviados' => $totalEnviados,
            'totalEnBandeja' => $totalEnBandeja,
            'totalFinalizados' => $totalFinalizados,
        ]);
    }
}
