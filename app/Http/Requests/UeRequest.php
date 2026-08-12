<?php

namespace App\Http\Requests;

use App\Models\Promotion;
use App\Models\Ue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        $ue = $this->route('ue');
        $id = $ue instanceof Ue ? $ue->id : null;

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('ues', 'code')->ignore($id)],
            'nom' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'credit' => ['required', 'integer', 'min:0', 'max:30'],
            'promotion_id' => ['required', 'exists:promotions,id'],
            'mention_id' => ['nullable', 'exists:mentions,id'],
            'semestre_id' => ['required', 'exists:semestres,id'],
            'annee_academique_id' => ['nullable', 'exists:annees_academiques,id'],
            'statut' => ['nullable', Rule::in(array_keys(Ue::STATUTS))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $promotion = Promotion::with('mention.filiere.domaine')->find($this->input('promotion_id'));
            if (!$promotion) {
                return;
            }

            $user = $this->user();
            $facultyId = optional(optional(optional($promotion->mention)->filiere)->domaine)->faculte_id;
            if ($user && $user->isDecanat() && (int) $facultyId !== (int) $user->faculte_id) {
                $validator->errors()->add('promotion_id', 'Cette promotion n\'appartient pas à votre faculté.');
            }

            if ($this->filled('mention_id') && (int) $promotion->mention_id !== (int) $this->input('mention_id')) {
                $validator->errors()->add('mention_id', 'La mention ne correspond pas à la promotion sélectionnée.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code de l\'UE est obligatoire.',
            'code.unique' => 'Ce code d\'UE est déjà utilisé.',
            'nom.required' => 'L\'intitulé de l\'UE est obligatoire.',
            'credit.required' => 'Le nombre de crédits est obligatoire.',
            'promotion_id.required' => 'Veuillez choisir la promotion / formation concernée.',
            'semestre_id.required' => 'Veuillez choisir le semestre.',
        ];
    }
}
