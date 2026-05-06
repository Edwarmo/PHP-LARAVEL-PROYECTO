<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida la actualización de una sala existente desde el panel admin.
 */
final class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'min:3', 'max:100'],
            'type'           => ['required', 'string', 'max:50'],
            'capacity'       => ['required', 'integer', 'min:1', 'max:1000'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'is_active'      => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'           => 'El nombre de la sala es obligatorio.',
            'name.min'                => 'El nombre debe tener al menos :min caracteres.',
            'type.required'           => 'El tipo de sala es obligatorio.',
            'capacity.required'       => 'La capacidad es obligatoria.',
            'capacity.integer'        => 'La capacidad debe ser un número entero.',
            'capacity.min'            => 'La capacidad mínima es 1 persona.',
            'price_per_hour.required' => 'El precio por hora es obligatorio.',
            'price_per_hour.numeric'  => 'El precio debe ser un valor numérico.',
            'price_per_hour.min'      => 'El precio no puede ser negativo.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name'           => 'nombre',
            'type'           => 'tipo',
            'capacity'       => 'capacidad',
            'description'    => 'descripción',
            'price_per_hour' => 'precio por hora',
            'is_active'      => 'estado activo',
        ];
    }
}
