<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    public function index()
    {
        return TipoDocumento::orderBy('nombre')->paginate(10);
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

    public function update(Request $request, $id) // <-- CAMBIA $tipoDocumento por $tipo_documento
    {

        $validated = $request->validate([
            // La validación ahora usa la variable correcta
            'nombre' => 'required|string|max:100|unique:tipos_documento,nombre,' . $id,
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        // Ahora que la vinculación funciona, podemos volver al método update() que es más limpio.
        $tipo_documento = TipoDocumento::findOrFail($id);
        $tipo_documento->nombre = $validated['nombre'];
        $tipo_documento->estado = $validated['estado'];
        $tipo_documento->save();
        return response()->json($tipo_documento);
    }



    public function destroy(TipoDocumento $tipo_documento) // <-- CAMBIA $tipoDocumento por $tipo_documento
    {
        $tipo_documento->estado = 'INACTIVO';
        $tipo_documento->save();

        return response()->json(null, 204);
    }

}
