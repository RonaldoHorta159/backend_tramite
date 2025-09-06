<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function getTiposDocumento()
    {
        $tipos = TipoDocumento::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        return response()->json($tipos);
    }

    public function getAreas()
    {
        $areas = Area::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        return response()->json($areas);
    }
}
