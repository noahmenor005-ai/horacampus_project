<?php

namespace App\Http\Requests;

use App\Models\Horaire;
use App\Support\TimeHelper;
use Illuminate\Foundation\Http\FormRequest;

class HoraireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isDecanat());
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['heure_debut', 'heure_fin'] as $field) {
            if ($this->filled($field)) {
                $merge[$field] = TimeHelper::normalize($this->input($field));
            }
        }

        if ($this->filled('date') && !$this->filled('jour')) {
            $merge['jour'] = Horaire::jourFr($this->input('date'));
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'cours_id' => ['nullable', 'exists:cours,id'],
            'ec_id' => ['nullable', 'exists:ecs,id'],
            'ue_id' => ['nullable', 'exists:ues,id'],
            'enseignant_id' => ['nullable', 'exists:users,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'domaine_id' => ['nullable', 'exists:domaines,id'],
            'filiere_id' => ['nullable', 'exists:filieres,id'],
            'mention_id' => ['nullable', 'exists:mentions,id'],
            'annee_academique_id' => ['nullable', 'exists:annees_academiques,id'],
            'semestre_id' => ['nullable', 'exists:semestres,id'],
            'auditoire_id' => ['nullable', 'exists:auditoires,id'],
            'effectif_attendu' => ['nullable', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'jour' => ['nullable', 'string', 'max:20'],
            'heure_debut' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'heure_fin' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'statut' => ['required', 'in:' . implode(',', array_keys(Horaire::STATUTS))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('heure_debut') && $this->filled('heure_fin') && !TimeHelper::isValidRange($this->input('heure_debut'), $this->input('heure_fin'))) {
                $validator->errors()->add('heure_fin', 'L\'heure de fin doit être postérieure à l\'heure de début.');
            }

            if (!$this->filled('cours_id') && !$this->filled('ec_id')) {
                $validator->errors()->add('ec_id', 'Veuillez choisir un EC (ou un cours).');
            }

            if (!$this->filled('cours_id') && !$this->filled('enseignant_id')) {
                $validator->errors()->add('enseignant_id', 'Veuillez choisir l\'enseignant.');
            }

            if (!$this->filled('cours_id') && !$this->filled('promotion_id')) {
                $validator->errors()->add('promotion_id', 'Veuillez choisir la promotion.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'date.required' => 'La date est obligatoire.',
            'heure_debut.required' => 'L\'heure de début est obligatoire.',
            'heure_fin.required' => 'L\'heure de fin est obligatoire.',
            'statut.required' => 'Le statut est obligatoire.',
        ];
    }
}
