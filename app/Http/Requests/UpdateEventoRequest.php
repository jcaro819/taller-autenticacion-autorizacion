<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * PUT /api/eventos/{evento}
 * 1) authorize(): RF 2.3, consulta la EventoPolicy ANTES de validar → 403 si no es el dueño.
 * 2) rules(): RF 2.2, restricciones de cada campo → 422 si los datos son inválidos.
 */
class UpdateEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Gate::authorize lanza la excepción con el mensaje de la Policy (403).
        Gate::authorize('update', $this->route('evento'));

        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
        ];
    }
}
