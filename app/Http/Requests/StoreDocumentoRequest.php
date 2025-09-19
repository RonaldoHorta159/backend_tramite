<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Documento;

class StoreDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Aquí usamos nuestro Policy para verificar el permiso.
     */
    public function authorize(): bool
    {
        // Llama al método 'create' del DocumentoPolicy.
        // Le pasamos el ID del área de origen que viene en la petición.
        return $this->user()->can('create', [Documento::class, $this->area_origen_id]);
    }

    /**
     * Get the validation rules that apply to the request.
     * Aquí van las reglas de validación que antes estaban en el controlador.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'tipo_documento_id' => 'required|integer|exists:tipos_documento,id',
            'asunto' => 'required|string|max:255',
            'nro_folios' => 'required|integer|min:1',
            'area_destino_id' => 'required|integer|exists:areas,id',
            'archivo_pdf' => 'sometimes|nullable|file|mimes:pdf|max:10240', // opcional, nulo y hasta 10MB
            'area_origen_id' => 'required|integer|exists:areas,id',
        ];
    }
}
