<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreDocumentoRequest; // Lo mantenemos por si se usa en otro lugar, pero no en store()
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\DerivarDocumentoRequest;

class DocumentoController extends Controller
{
    public function index()
    {
        $usuarioId = Auth::id();

        $documentos = Documento::where('usuario_creador_id', $usuarioId)
            ->with(['tipoDocumento', 'areaActual', 'areaOrigen'])
            ->addSelect([
                'latest_proveido' => Movimiento::select('proveido')
                    ->whereColumn('documento_id', 'documentos.id')
                    ->orderBy('id', 'desc')
                    ->limit(1)
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $documentos->through(function ($doc) {
            $doc->latestMovement = (object) ['proveido' => $doc->latest_proveido];
            unset($doc->latest_proveido);
            return $doc;
        });

        return $documentos;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
            'asunto' => 'required|string|max:255',
            'nro_folios' => 'required|integer|min:1',
            'area_destino_id' => 'required|integer|exists:areas,id',
            'archivo_pdf' => 'required|file|mimes:pdf|max:2048',
        ]);

        $usuario = Auth::user();

        DB::beginTransaction();
        try {
            $ultimoId = Documento::max('id') ?? 0;
            $correlativo = $ultimoId + 1;

            $rutaArchivo = $request->file('archivo_pdf')->store('public/pdfs');

            $documento = Documento::create([
                'tipo_documento_id' => $validatedData['tipo_documento_id'],
                'nro_documento' => $correlativo,
                'asunto' => $validatedData['asunto'],
                'nro_folios' => $validatedData['nro_folios'],
                'archivo_pdf' => $rutaArchivo,
                'usuario_creador_id' => $usuario->id,
                'area_origen_id' => $usuario->area_id,
                'area_actual_id' => $validatedData['area_destino_id'],
                'codigo_unico' => 'TEMP-' . time(),
            ]);

            $documento->codigo_unico = 'TRAMUSA-' . date('Y') . '-' . str_pad($documento->id, 6, '0', STR_PAD_LEFT);
            $documento->save();

            Movimiento::create([
                'documento_id' => $documento->id,
                'area_origen_id' => $usuario->area_id,
                'area_destino_id' => $validatedData['area_destino_id'],
                'usuario_id' => $usuario->id,
                'proveido' => 'Trámite generado e iniciado.',
                'estado_movimiento' => 'ENVIADO',
            ]);

            DB::commit();

            return response()->json($documento->load('tipoDocumento', 'areaActual'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el documento', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Muestra los detalles y el historial de un documento específico.
     * VERSIÓN CORREGIDA Y ROBUSTA
     */
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
    /**
     * Deriva un documento a una nueva área.
     */
    public function derivar(DerivarDocumentoRequest $request, Documento $documento)
    {
        $validatedData = $request->validated();
        $usuario = Auth::user();

        DB::beginTransaction();
        try {
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

            return response()->json([
                'message' => 'Documento derivado correctamente.',
                'documento' => $documento->fresh()->load('areaActual')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al derivar el documento', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recepciona un documento en el área actual.
     */
    public function recepcionar(Documento $documento)
    {
        $usuario = Auth::user();

        if ($documento->area_actual_id !== $usuario->area_id) {
            return response()->json(['message' => 'Acción no autorizada.'], 403);
        }

        DB::beginTransaction();
        try {
            Movimiento::create([
                'documento_id' => $documento->id,
                'area_origen_id' => $documento->area_actual_id,
                'area_destino_id' => $documento->area_actual_id,
                'usuario_id' => $usuario->id,
                'proveido' => 'Documento recepcionado y trámite finalizado en el área.',
                'estado_movimiento' => 'RECIBIDO',
            ]);

            $documento->estado_general = 'FINALIZADO';
            $documento->save();

            DB::commit();

            return response()->json(['message' => 'Documento recepcionado y finalizado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al recepcionar el documento', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene el siguiente número correlativo para un nuevo documento.
     */
    public function getSiguienteCorrelativo()
    {
        $ultimoId = Documento::max('id') ?? 0;
        $siguienteNumero = $ultimoId + 1;

        return response()->json(['siguiente_numero' => $siguienteNumero]);
    }
}
