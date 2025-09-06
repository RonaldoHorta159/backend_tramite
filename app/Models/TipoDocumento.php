<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada.
     * @var string
     */
    protected $table = 'tipos_documento';

    /**
     * Indica si el modelo debe tener timestamps (created_at, updated_at).
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar masivamente.
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'estado',
    ];
}
