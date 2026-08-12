<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SemestreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'annee_academique_id' => ['required', 'exists:annees_academiques,id'],
            'libelle' => ['required', 'string', 'max:100'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'annee_academique_id.required' => 'Veuillez lier le semestre à une année académique.',
            'libelle.required' => 'Le libellé du semestre est obligatoire.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
        ];
    }
}
