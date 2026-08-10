<?php

namespace App\Http\Requests;

use App\Models\Disponibilite;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisponibiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'semestre_id' => ['nullable', 'exists:semestres,id'],
            'jour' => ['required', Rule::in(User::JOURS)],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
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
