<?php

namespace App\Http\Requests;

use App\Models\Filiere;
use Illuminate\Foundation\Http\FormRequest;

class MentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'filiere_id' => ['required', 'exists:filieres,id'],
            'nom' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $filiere = Filiere::with('domaine')->find($this->input('filiere_id'));
            if (!$filiere) {
                return;
            }

            $user = $this->user();
            if ($user && $user->isDecanat() && (int) optional($filiere->domaine)->faculte_id !== (int) $user->faculte_id) {
                $validator->errors()->add('filiere_id', 'Cette filière n\'appartient pas à votre faculté.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'filiere_id.required' => 'Veuillez sélectionner une filière.',
            'nom.required' => 'Le nom de la mention est obligatoire.',
        ];
    }
}
