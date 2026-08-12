<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DomaineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'faculte_id' => ['nullable', 'exists:facultes,id'],
            'nom' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du domaine est obligatoire.',
        ];
    }
}
