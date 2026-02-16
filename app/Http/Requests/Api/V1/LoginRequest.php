<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'usuario_auth_id' => ['required', 'uuid'],
            'tipo_documento' => ['required', 'string', 'in:DNI,CE,RUC'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'nombre_mostrar' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'usuario_auth_id.required' => 'El identificador de usuario es obligatorio.',
            'usuario_auth_id.uuid' => 'El identificador de usuario debe ser un UUID válido.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI, CE o RUC.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'nombre_mostrar.required' => 'El nombre para mostrar es obligatorio.',
        ];
    }
}
