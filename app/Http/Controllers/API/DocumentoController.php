<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\DerivarDocumentoRequest;
use App\Http\Requests\StoreDocumentoRequest;
use App\Models\TipoDocumento;
use App\Services\DocumentoService;
use Exception;
use PhpParser\Node\Stmt\Else_;

class DocumentoController extends Controller
{
    protected $documentoService;

    public function __construct(DocumentoService $documentoService)
    {
        $this->documentoService = $documentoService;
    }

    public function index()
    {
        $usuarioId = Auth::id();
        // ✅ OPTIMIZACIÓN: Usamos with() para cargar la relación 'latestMovement' de forma eficiente.
        $documentos = Documento::where('usuario_creador_id', $usuarioId)
            ->with(['tipoDocumento', 'areaActual', 'areaOrigen', 'latestMovement'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($documentos);
    }

    public function store(StoreDocumentoRequest $request) // 👈 3. Usar el FormRequest
    {
        try {
            // La validación y autorización ya ocurrieron automáticamente.
            $validatedData = $request->validated();

            // Delegamos toda la lógica de creación al servicio.
            $documento = $this->documentoService->crearNuevoTramite(
                $validatedData,
                $request->user(),
                $request->file('archivo_pdf')
            );

            // Devolvemos la respuesta exitosa.
            return response()->json($documento->load('tipoDocumento', 'areaActual'), 201);

        } catch (Exception $e) {
            // Manejo de cualquier error inesperado que pueda ocurrir en el servicio.
            return response()->json([
                'message' => 'Error al crear el documento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Documento $documento)
    {
        $documento->load([
            'tipoDocumento',
            'areaOrigen',
            'areaActual',
            'usuarioCreador.empleado',
            'movimientos.areaOrigen',
            'movimientos.areaDestino',
            'movimientos.usuario.empleado'
        ]);

        $documento->movimientos->each(function ($movimiento) use ($documento) {
            $movimiento->codigo_unico = $documento->codigo_unico;
            $tipoNombre = $documento->tipoDocumento?->nombre ?? 'N/A';
            $movimiento->documento_completo = $tipoNombre . ' ' . $documento->nro_documento;
            $movimiento->asunto = $documento->asunto;
            $movimiento->nro_folios = $documento->nro_folios;
        });

        return response()->json($documento);
    }

    public function derivar(DerivarDocumentoRequest $request, Documento $documento)
    {
        if ($documento->respuesta_para_documento_id !== null) {
            return response()->json(['message' => 'Este documento es una respuesta. No se puede derivar.'], 403);
        }

        $validatedData = $request->validated();
        $usuario = Auth::user();

        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);

        if (!$accessibleAreaIds->contains($documento->area_actual_id)) {
            return response()->json(['message' => 'Acción no autorizada para derivar desde esta área.'], 403);
        }

        DB::beginTransaction();
        try {
            $haSidoRecibido = Movimiento::where('documento_id', $documento->id)
                ->where('area_destino_id', $documento->area_actual_id)
                ->where('estado_movimiento', 'RECIBIDO')
                ->exists();

            if (!$haSidoRecibido) {
                Movimiento::create([
                    'documento_id' => $documento->id,
                    'area_origen_id' => $documento->area_actual_id,
                    'area_destino_id' => $documento->area_actual_id,
                    'usuario_id' => $usuario->id,
                    'proveido' => 'Documento recepcionado automáticamente para derivación.',
                    'estado_movimiento' => 'RECIBIDO',
                ]);
            }

            Movimiento::create([
                'documento_id' => $documento->id,
                'area_origen_id' => $documento->area_actual_id,
                'area_destino_id' => $validatedData['area_destino_id'],
                'usuario_id' => $usuario->id,
                'proveido' => $validatedData['proveido'],
                'estado_movimiento' => 'DERIVADO',
            ]);

            $documento->area_actual_id = $validatedData['area_destino_id'];
            $documento->save();

            DB::commit();
            return response()->json(['message' => 'Documento derivado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al derivar el documento', 'error' => $e->getMessage()], 500);
        }
    }


    public function recepcionar(Documento $documento)
    {
        if ($documento->respuesta_para_documento_id !== null) {
            return response()->json(['message' => 'Este documento es una respuesta. No se puede recepcionar.'], 403);
        }

        $usuario = Auth::user();
        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);

        if (!$accessibleAreaIds->contains($documento->area_actual_id)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $yaRecepcionado = Movimiento::where('documento_id', $documento->id)
            ->where('area_destino_id', $documento->area_actual_id)
            ->where('estado_movimiento', 'RECIBIDO')
            ->exists();

        if ($yaRecepcionado) {
            return response()->json(['message' => 'Este documento ya ha sido recepcionado en el área.'], 200);
        }

        Movimiento::create([
            'documento_id' => $documento->id,
            'area_origen_id' => $documento->area_actual_id,
            'area_destino_id' => $documento->area_actual_id,
            'usuario_id' => $usuario->id,
            'proveido' => 'Documento recepcionado en el área.',
            'estado_movimiento' => 'RECIBIDO',
        ]);

        return response()->json(['message' => 'Documento recepcionado correctamente.']);
    }


    public function responder(Request $request, Documento $documento)
    {
        if ($documento->estado_general === 'FINALIZADO') {
            return response()->json(['message' => 'Acción no permitida. Este trámite ya está finalizado.'], 403);
        }

        $validatedData = $request->validate([
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
            'asunto' => 'required|string|max:255',
            'nro_folios' => 'required|integer|min:1',
            'area_destino_id' => 'required|integer|exists:areas,id',
            'archivo_pdf' => 'sometimes|file|mimes:pdf|max:2048',
            'area_origen_id' => 'required|integer|exists:areas,id',
        ]);

        $usuario = Auth::user();

        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);
        if (!$accessibleAreaIds->contains($validatedData['area_origen_id'])) {
            return response()->json(['message' => 'No tiene permiso para responder desde esta oficina.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Crear el documento de respuesta
            $correlativo = $this->getSiguienteCorrelativoArea($validatedData['area_origen_id']);
            $rutaArchivo = $request->hasFile('archivo_pdf') ? $request->file('archivo_pdf')->store('public/pdfs') : null;
            $areaOrigen = Area::find($validatedData['area_origen_id']);

            $respuestaDoc = Documento::create([
                'respuesta_para_documento_id' => $documento->id,
                'tipo_documento_id' => $validatedData['tipo_documento_id'],
                'nro_documento' => $correlativo,
                'correlativo_area' => $correlativo,
                'asunto' => $validatedData['asunto'],
                'nro_folios' => $validatedData['nro_folios'],
                'archivo_pdf' => $rutaArchivo,
                'usuario_creador_id' => $usuario->id,
                'area_origen_id' => $validatedData['area_origen_id'],
                'area_actual_id' => $validatedData['area_destino_id'],
                'estado_general' => 'FINALIZADO',
                'codigo_unico' => 'TEMP-' . time(),
            ]);

            $respuestaDoc->codigo_unico = "{$areaOrigen->codigo}-" . date('Y') . '-' . str_pad($correlativo, 6, '0', STR_PAD_LEFT);
            $respuestaDoc->save();

            Movimiento::create([
                'documento_id' => $respuestaDoc->id,
                'area_origen_id' => $validatedData['area_origen_id'],
                'area_destino_id' => $validatedData['area_destino_id'],
                'usuario_id' => $usuario->id,
                'proveido' => 'Respuesta al trámite ' . $documento->codigo_unico,
                'estado_movimiento' => 'ENVIADO',
            ]);

            // 2. LÓGICA AÑADIDA: Finalizamos el trámite original
            $documento->estado_general = 'FINALIZADO';
            $documento->save();

            Movimiento::create([
                'documento_id' => $documento->id,
                'area_origen_id' => $documento->area_actual_id,
                'area_destino_id' => $documento->area_actual_id,
                'usuario_id' => $usuario->id,
                'proveido' => 'Trámite finalizado con la emisión de la respuesta: ' . $respuestaDoc->codigo_unico,
                'estado_movimiento' => 'FINALIZADO',
            ]);

            DB::commit();
            return response()->json(['message' => 'Respuesta enviada y trámite finalizado correctamente.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al enviar la respuesta', 'error' => $e->getMessage()], 500);
        }
    }



    public function finalizar(Documento $documento)
    {
        if ($documento->estado_general === 'FINALIZADO') {
            return response()->json(['message' => 'Este trámite ya se encuentra finalizado.'], 400);
        }

        $usuario = Auth::user();
        $accessibleAreaIds = $usuario->areas()->pluck('areas.id');
        $accessibleAreaIds->push($usuario->primary_area_id);
        if (!$accessibleAreaIds->contains($documento->area_actual_id)) {
            return response()->json(['message' => 'No tiene permiso para finalizar trámites en esta oficina.'], 403);
        }

        $yaRecepcionado = Movimiento::where('documento_id', $documento->id)
            ->where('area_destino_id', $documento->area_actual_id)
            ->where('estado_movimiento', 'RECIBIDO')
            ->exists();

        if (!$yaRecepcionado) {
            return response()->json(['message' => 'Debe recepcionar el documento antes de poder finalizarlo.'], 403);
        }

        DB::beginTransaction();
        try {
            $documento->estado_general = 'FINALIZADO';
            $documento->save();

            Movimiento::create([
                'documento_id' => $documento->id,
                'area_origen_id' => $documento->area_actual_id,
                'area_destino_id' => $documento->area_actual_id,
                'usuario_id' => $usuario->id,
                'proveido' => 'Trámite finalizado en esta oficina.',
                'estado_movimiento' => 'FINALIZADO',
            ]);

            DB::commit();
            return response()->json(['message' => 'Trámite finalizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al finalizar el trámite', 'error' => $e->getMessage()], 500);
        }
    }


    private function getSiguienteCorrelativoArea(int $areaId): int
    {
        $ultimoCorrelativo = Documento::where('area_origen_id', $areaId)
            ->whereYear('created_at', date('Y'))
            ->max('correlativo_area') ?? 0;
        return $ultimoCorrelativo + 1;
    }

    public function getSiguienteCorrelativo(int $area_id, int $tipo_documento_id)
    {
        // El servicio ya se encarga de buscar y usar los IDs.
        $siguienteNumero = $this->documentoService->getSiguienteCorrelativo($area_id, $tipo_documento_id);

        return response()->json(['siguiente_numero' => $siguienteNumero]);
    }


    public function indexPorArea(Area $area)
    {
        $documentos = Documento::where('area_origen_id', $area->id)
            ->with(['tipoDocumento', 'areaActual', 'areaOrigen', 'usuarioCreador.empleado', 'latestMovement'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return $documentos;
    }

    public function indexPorAreaUsuario(Area $area)
    {
        $user = auth()->user();
        $tienePermiso = $user->rol === 'Administrador'
            || $user->areas()->where('area_id', $area->id)->exists()
            || $user->primary_area_id == $area->id;

        if (!$tienePermiso) {
            return response()->json(['message' => 'Acceso no autorizado a esta área.'], 403);
        }

        $documentos = Documento::where('area_origen_id', $area->id)
            ->with(['tipoDocumento', 'areaActual', 'areaOrigen', 'usuarioCreador.empleado', 'latestMovement'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $documentos;
    }
}
