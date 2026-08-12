<?php

namespace App\Http\Requests;

use App\Models\Disponibilite;
use App\Models\User;
use App\Support\TimeHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisponibiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['heure_debut', 'heure_fin'] as $field) {
            if ($this->filled($field)) {
                $merge[$field] = TimeHelper::normalize($this->input($field));
            }
        }
        if ($merge) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'semestre_id' => ['nullable', 'exists:semestres,id'],
            'annee_academique_id' => ['nullable', 'exists:annees_academiques,id'],
            'jour' => ['required', Rule::in(User::JOURS)],
            'heure_debut' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'heure_fin' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'statut' => ['sometimes', 'in:' . implode(',', array_keys(Disponibilite::STATUTS))],
        ];
    }

    public function messages(): array
    {
        return [
            'jour.required' => 'Veuillez choisir le jour.',
            'heure_fin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
        ];
    }
}
