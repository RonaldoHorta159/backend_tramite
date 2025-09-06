<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Asegúrate de importar la interfaz para JWT
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


// La clase debe implementar JWTSubject
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * El nombre de la tabla asociada.
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Los atributos que se pueden asignar masivamente.
     * @var array<int, string>
     */
    protected $fillable = [
        'empleado_id',
        'area_id',
        'nombre_usuario',
        'password',
        'rol',
        'estado',
    ];

    /**
     * Los atributos que deben ocultarse para las serializaciones.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', // Campo que Laravel usa para la función "recordarme"
    ];
    public function area(): BelongsTo // <-- MÉTODO AÑADIDO
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Los atributos que deben ser convertidos.
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed', // Le dice a Laravel que siempre hashee este campo
    ];

    // Métodos requeridos por la interfaz JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
