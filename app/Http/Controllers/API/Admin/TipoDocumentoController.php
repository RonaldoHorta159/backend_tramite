<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    public function index()
    {
        return TipoDocumento::orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_documento,nombre',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $tipoDocumento = TipoDocumento::create($validated);
        return response()->json($tipoDocumento, 201);
    }

    public function show(TipoDocumento $tipoDocumento)
    {
        return $tipoDocumento;
    }

    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos_documento,nombre,' . $tipoDocumento->id,
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $tipoDocumento->update($validated);
        return response()->json($tipoDocumento);
    }

    public function destroy(TipoDocumento $tipoDocumento)
    {
        $tipoDocumento->estado = 'INACTIVO';
        $tipoDocumento->save();

        return response()->json(null, 204);
    }
}
