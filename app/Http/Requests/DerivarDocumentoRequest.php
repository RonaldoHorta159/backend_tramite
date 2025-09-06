<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DerivarDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_destino_id' => 'required|integer|exists:areas,id',
            'proveido' => 'required|string|max:1000',
        ];
    }
}
