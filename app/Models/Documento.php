<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Añade esta línea


class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'codigo_unico',
        'nro_documento',
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
        // Laravel usa el nombre del método para la llave foránea (user_id),
        // pero nuestro modelo es User.php, así que lo especificamos.
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Remitente::class, 'remitente_id');
    }

    public function areaOrigen(): BelongsTo
    {
        // Como tenemos dos relaciones al modelo Area, debemos ser explícitos.
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
        // latestOfMany() obtiene el registro más reciente de la relación 'movimientos'
        return $this->hasOne(Movimiento::class)->latestOfMany();
    }
}
