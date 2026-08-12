<?php

namespace App\Http\Requests;

use App\Models\Domaine;
use Illuminate\Foundation\Http\FormRequest;

class FiliereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        return [
            'domaine_id' => ['required', 'exists:domaines,id'],
            'nom' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'actif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $domaine = Domaine::find($this->input('domaine_id'));
            if (!$domaine) {
                return;
            }

            $user = $this->user();
            if ($user && $user->isDecanat() && (int) $domaine->faculte_id !== (int) $user->faculte_id) {
                $validator->errors()->add('domaine_id', 'Ce domaine n\'appartient pas à votre faculté.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'domaine_id.required' => 'Veuillez sélectionner un domaine.',
            'nom.required' => 'Le nom de la filière est obligatoire.',
        ];
    }
}
