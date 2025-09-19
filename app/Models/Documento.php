<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';


    protected $fillable = [
        'respuesta_para_documento_id',
        'codigo_unico',
        'nro_documento',
        'correlativo_area',
        'asunto',
        'nro_folios',
        'archivo_pdf',
        'tipo_documento_id',
        'usuario_creador_id',
        'remitente_id',
        'area_origen_id',
        'area_actual_id',
        'estado_general',
        'nro_libro'
    ];
    // --- Definición de Relaciones ---

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Remitente::class, 'remitente_id');
    }

    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    public function areaActual(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_actual_id');
    }
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'documento_id');
    }

    public function latestMovement()
    {
        return $this->hasOne(Movimiento::class)->latestOfMany('id');
    }

    /**
     * Si este documento es una respuesta, obtiene el documento original.
     */
    public function documentoOriginal(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'respuesta_para_documento_id');
    }

    /**
     * Obtiene todos los documentos que son respuestas a este.
     */
    public function respuestas(): HasMany
    {
        return $this->hasMany(Documento::class, 'respuesta_para_documento_id');
    }


}
