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
            'cours_id' => ['required', 'exists:cours,id'],
            'semestre_id' => ['nullable', 'exists:semestres,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'effectif_attendu' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
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
