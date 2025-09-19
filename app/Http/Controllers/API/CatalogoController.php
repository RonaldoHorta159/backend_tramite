<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\Cache;

class CatalogoController extends Controller
{
    /**
     * Devuelve todos los tipos de documento activos (con caché de 24 horas).
     */
    public function getTiposDocumento()
    {
        $tipos = Cache::remember('catalogo:tipos_documento', 60 * 60 * 24, function () {
            return TipoDocumento::where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get();
        });

        return response()->json($tipos);
    }

    /**
     * Devuelve todas las áreas activas (con caché de 24 horas).
     */
    public function getAreas()
    {
        $areas = Cache::remember('catalogo:areas', 60 * 60 * 24, function () {
            return Area::where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get();
        });

        return response()->json($areas);
    }
}
