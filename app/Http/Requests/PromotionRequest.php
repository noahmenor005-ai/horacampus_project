<?php

namespace App\Http\Requests;

use App\Models\Mention;
use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'mention_id' => ['required', 'exists:mentions,id'],
            'annee_academique_id' => ['required', 'exists:annees_academiques,id'],
            'nom' => ['required', 'string', 'max:100'],
            'niveau' => ['required', 'string', 'max:50'],
            'effectif' => ['required', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $mention = Mention::with('filiere.domaine')->find($this->input('mention_id'));
            if (!$mention) {
                return;
            }

            $user = $this->user();
            $facultyId = optional(optional($mention->filiere)->domaine)->faculte_id;
            if ($user && $user->isDecanat() && (int) $facultyId !== (int) $user->faculte_id) {
                $validator->errors()->add('mention_id', 'Cette mention n\'appartient pas à votre faculté.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'mention_id.required' => 'Veuillez sélectionner une mention.',
            'annee_academique_id.required' => 'Veuillez sélectionner une année académique.',
            'nom.required' => 'Le nom de la promotion est obligatoire.',
            'niveau.required' => 'Le niveau est obligatoire (L1, L2, L3, M1, M2).',
        ];
    }
}
