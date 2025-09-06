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
    // Listar todos los usuarios con sus datos de empleado y área
    public function index()
    {
        return Usuario::with(['empleado', 'area'])->orderBy('nombre_usuario')->get();
    }

    // Guardar un nuevo empleado y su usuario asociado
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'dni' => 'required|string|size:8|unique:empleados,dni|unique:usuarios,nombre_usuario', // DNI debe ser único en ambas tablas
            'email' => 'required|string|email|max:150|unique:empleados,email',
            'area_id' => 'required|exists:areas,id',
            'rol' => 'required|in:Administrador,Usuario',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Usamos una transacción para asegurar que ambos registros se creen correctamente
        DB::beginTransaction();
        try {
            $empleado = Empleado::create($request->only('nombres', 'apellido_paterno', 'apellido_materno', 'dni', 'email'));

            $usuario = Usuario::create([
                'empleado_id' => $empleado->id,
                'area_id' => $request->area_id,
                'nombre_usuario' => $request->dni, // <-- CAMBIO CLAVE: El nombre de usuario es el DNI
                'rol' => $request->rol,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json($usuario->load('empleado', 'area'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el usuario', 'error' => $e->getMessage()], 500);
        }
    }


    // Mostrar un usuario específico con sus datos para editar
    public function show(Usuario $usuario)
    {
        return $usuario->load('empleado', 'area');
    }

    // Actualizar un empleado y su usuario asociado
    public function update(Request $request, Usuario $usuario)
    {
        $empleado = $usuario->empleado;

        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            // Asegura que el DNI sea único, ignorando el registro actual
            'dni' => 'required|string|size:8|unique:empleados,dni,' . $empleado->id . '|unique:usuarios,nombre_usuario,' . $usuario->id,
            'email' => 'required|string|email|max:150|unique:empleados,email,' . $empleado->id,
            'area_id' => 'required|exists:areas,id',
            'rol' => 'required|in:Administrador,Usuario',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();
        try {
            $empleado->update($request->only('nombres', 'apellido_paterno', 'apellido_materno', 'dni', 'email'));

            $usuarioData = $request->only('area_id', 'rol', 'estado');
            // --- CAMBIO CLAVE ---
            // Forzamos a que el nombre de usuario sea siempre el DNI
            $usuarioData['nombre_usuario'] = $request->dni;

            $usuario->update($usuarioData);

            if ($request->filled('password')) {
                $usuario->password = Hash::make($request->password);
                $usuario->save();
            }

            DB::commit();

            return response()->json($usuario->load('empleado', 'area'));
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
