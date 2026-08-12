<?php

namespace App\Http\Requests;

use App\Models\Cours;
use Illuminate\Foundation\Http\FormRequest;

class DemandeAuditoireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cours_id' => ['nullable', 'exists:cours,id'],
            'horaire_id' => ['nullable', 'exists:horaires,id'],
            'ec_id' => ['nullable', 'exists:ecs,id'],
            'enseignant_id' => ['nullable', 'exists:users,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'semestre_id' => ['nullable', 'exists:semestres,id'],
            'date' => ['required', 'date'],
            'heure_debut' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'heure_fin' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'effectif_attendu' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
            'commentaire' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cours = $this->route('demande') ? $this->route('demande')->cours : Cours::find($this->input('cours_id'));

            if ($cours && $this->input('effectif_attendu') && $this->input('effectif_attendu') > $cours->promotion->effectif) {
                $validator->errors()->add(
                    'effectif_attendu',
                    "L'effectif attendu ({$this->input('effectif_attendu')}) dépasse l'effectif de la promotion ({$cours->promotion->effectif})."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'cours_id.required' => 'Veuillez choisir le cours.',
            'date.after_or_equal' => 'La date doit être aujourd\'hui ou ultérieure.',
            'heure_fin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
            'effectif_attendu.min' => 'L\'effectif attendu doit être au moins de 1.',
        ];
    }
}
