<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Ya estamos protegiendo la ruta con el middleware auth:api,
        // así que aquí podemos simplemente retornar true.
        return true;
    }

    /**
     * Obtiene las reglas de validación que aplican a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
            'nro_documento' => 'required|string|max:50',
            'asunto' => 'required|string|max:255',
            'nro_folios' => 'required|integer|min:1',
            'area_destino_id' => 'required|integer|exists:areas,id',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:2048', // Opcional, PDF, max 2MB
        ];
    }
}
