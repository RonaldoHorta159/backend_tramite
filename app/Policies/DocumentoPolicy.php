<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentoPolicy
{
    /**
     * Determine whether the user can create models.
     * La lógica de autorización para crear un documento.
     * Recibe al usuario autenticado y el ID del área desde donde se quiere emitir.
     */
    public function create(User $user, int $areaOrigenId): bool
    {
        // 1. Un administrador siempre tiene permiso.
        if ($user->rol === 'Administrador') {
            return true;
        }

        // 2. Si no es admin, verificamos si el área de origen es su área principal
        //    o si está en la lista de sus áreas adicionales.
        return $user->primary_area_id === $areaOrigenId
            || $user->areas()->where('area_id', $areaOrigenId)->exists();
    }

    // Dejaremos los otros métodos para después
    // ...
}
