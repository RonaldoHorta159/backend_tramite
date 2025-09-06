<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remitente extends Model
{
    use HasFactory;

    protected $table = 'remitentes';

    /**
     * Los atributos que se pueden asignar masivamente.
     * @var array<int, string>
     */
    protected $fillable = [
        'tipo_persona',
        'dni',
        'nombres_razon_social',
        'apellido_paterno',
        'apellido_materno',
        'ruc',
        'celular',
        'email',
        'direccion',
    ];
}
