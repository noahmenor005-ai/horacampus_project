<?php

namespace App\Http\Requests;

use App\Models\Cours;
use App\Models\Horaire;
use Illuminate\Foundation\Http\FormRequest;

class HoraireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isDecanat();
    }

    public function rules(): array
    {
        return [
            'cours_id' => ['required', 'exists:cours,id'],
            'auditoire_id' => ['required', 'exists:auditoires,id'],
            'semestre_id' => ['nullable', 'exists:semestres,id'],
            'effectif_attendu' => ['nullable', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'statut' => ['required', 'in:' . implode(',', array_keys(Horaire::STATUTS))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cours = $this->route('horaire') ? $this->route('horaire')->cours : Cours::find($this->input('cours_id'));

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
            'auditoire_id.required' => 'Veuillez choisir l\'auditoire.',
            'heure_fin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
        ];
    }
}
