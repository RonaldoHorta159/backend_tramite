<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    // Listar todas las áreas (para la tabla del admin)
    public function index()
    {
        return Area::orderBy('nombre')->get();
    }

    // Guardar una nueva área
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:areas,nombre',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $area = Area::create($validated);
        return response()->json($area, 201);
    }

    // Mostrar un área específica (para editar)
    public function show(Area $area)
    {
        return $area;
    }

    // Actualizar un área existente
    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:areas,nombre,' . $area->id,
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $area->update($validated);
        return response()->json($area);
    }

    // "Eliminar" un área (la cambiaremos a INACTIVO)
    public function destroy(Area $area)
    {
        // Buena práctica: no eliminar registros, solo desactivarlos.
        $area->estado = 'INACTIVO';
        $area->save();

        return response()->json(null, 204); // 204: No Content
    }
}
