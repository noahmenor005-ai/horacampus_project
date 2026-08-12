<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnneeAcademiqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        $annee = $this->route('annee') ?? $this->route('annees_academique');
        $id = $annee->id ?? null;

        return [
            'libelle' => ['required', 'string', 'max:20', Rule::unique('annees_academiques', 'libelle')->ignore($id)],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé est obligatoire (ex. 2025-2026).',
            'libelle.unique' => 'Cette année académique existe déjà.',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
        ];
    }
}
