<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([User::ROLE_ENSEIGNANT, User::ROLE_ETUDIANT])],
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'faculte_id' => ['required', 'exists:facultes,id'],
            'domaine_id' => ['nullable', 'required_if:role,etudiant', 'exists:domaines,id'],
            'filiere_id' => ['nullable', 'required_if:role,etudiant', 'exists:filieres,id'],
            'mention_id' => ['nullable', 'required_if:role,etudiant', 'exists:mentions,id'],
            'promotion_id' => ['nullable', 'required_if:role,etudiant', 'exists:promotions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'faculte_id.required' => 'Veuillez choisir une faculté.',
            'domaine_id.required_if' => 'Veuillez choisir un domaine.',
            'filiere_id.required_if' => 'Veuillez choisir une filière.',
            'mention_id.required_if' => 'Veuillez choisir une mention.',
            'promotion_id.required_if' => 'Veuillez choisir une promotion.',
        ];
    }
}
