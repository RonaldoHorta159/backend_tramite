<?php

// app/Models/Area.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     * Laravel lo inferiría como 'areas', pero es buena práctica ser explícito.
     * @var string
     */
    protected $table = 'areas';

    /**
     * Laravel gestiona created_at y updated_at. Si no las quieres,
     * establece public $timestamps = false;
     * Aquí las usaremos, así que no es necesario.
     */
    public $timestamps = false; // Desactivamos timestamps porque ya tienes 'fecha_registro'

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'estado',
    ];
    // --- AÑADIR ESTA NUEVA RELACIÓN ---
    // Esto obtiene todos los usuarios que tienen acceso a esta área.
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'area_user');
    }
}
