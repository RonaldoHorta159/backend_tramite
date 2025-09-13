<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User as Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsuarioController extends Controller
{
    // Listar todos los usuarios con datos de empleado y áreas
    public function index()
    {
        return Usuario::with(['empleado', 'primaryArea', 'areas'])
            ->orderBy('nombre_usuario')
            ->paginate(15);
    }

    // Guardar un nuevo empleado y su usuario asociado
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'dni' => 'required|string|size:8|unique:empleados,dni|unique:usuarios,nombre_usuario',
            'email' => 'required|string|email|max:150|unique:empleados,email',
            'primary_area_id' => 'required|exists:areas,id',
            'rol' => 'required|in:Administrador,Usuario',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'areas_asignadas' => 'nullable|array',
            'areas_asignadas.*' => 'exists:areas,id',
        ]);

        DB::beginTransaction();
        try {
            $empleado = Empleado::create($request->only('nombres', 'apellido_paterno', 'apellido_materno', 'dni', 'email'));

            $usuario = Usuario::create([
                'empleado_id' => $empleado->id,
                'primary_area_id' => $request->primary_area_id,
                'nombre_usuario' => $request->dni,
                'rol' => $request->rol,
                'password' => Hash::make($request->password),
            ]);

            if ($request->has('areas_asignadas')) {
                $usuario->areas()->sync($request->areas_asignadas);
            }

            DB::commit();

            return response()->json($usuario->load('empleado', 'primaryArea', 'areas'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el usuario', 'error' => $e->getMessage()], 500);
        }
    }

    // Mostrar un usuario específico
    public function show(Usuario $usuario)
    {
        return $usuario->load('empleado', 'primaryArea', 'areas');
    }

    // Actualizar un empleado y su usuario asociado
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $empleado = $usuario->empleado;

        // La validación no cambia, ya es correcta
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'dni' => 'required|string|size:8|unique:empleados,dni,' . $empleado->id,
            'nombre_usuario' => 'required|string|max:255|unique:usuarios,nombre_usuario,' . $id,
            'email' => 'required|string|email|max:150|unique:empleados,email,' . $empleado->id,
            'primary_area_id' => 'required|exists:areas,id', // <-- Campo principal
            'rol' => 'required|in:Administrador,Usuario',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'password' => 'nullable|confirmed|min:8',
            'areas_asignadas' => 'nullable|array',
            'areas_asignadas.*' => 'exists:areas,id',
        ]);

        DB::beginTransaction();
        try {
            // Actualizamos el empleado (usar update() aquí está bien, es más simple)
            $empleado->update([
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'dni' => $validated['dni'],
                'email' => $validated['email'],
            ]);

            // --- LÓGICA DE GUARDADO CORREGIDA Y EXPLÍCITA ---
            $usuario->primary_area_id = $validated['primary_area_id'];
            $usuario->rol = $validated['rol']; // Asignamos el rol
            $usuario->estado = $validated['estado'];
            $usuario->nombre_usuario = $validated['nombre_usuario'];

            if ($request->filled('password')) {
                $usuario->password = Hash::make($validated['password']);
            }

            $usuario->save(); // Guardamos explícitamente los cambios en el usuario

            // Sincronizamos las áreas asignadas
            if ($request->has('areas_asignadas')) {
                $usuario->areas()->sync($validated['areas_asignadas']);
            }

            DB::commit();

            return response()->json($usuario->load('empleado', 'primaryArea', 'areas'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el usuario', 'error' => $e->getMessage()], 500);
        }
    }

    // "Eliminar" (desactivar) un usuario y su empleado asociado
    public function destroy(Usuario $usuario)
    {
        DB::beginTransaction();
        try {
            $usuario->empleado()->update(['estado' => 'INACTIVO']);
            $usuario->update(['estado' => 'INACTIVO']);
            DB::commit();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al desactivar el usuario', 'error' => $e->getMessage()], 500);
        }
    }
}
