<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Auth personnel : email + password
            'email' => ['nullable', 'email', 'required_without:matricule'],
            'password' => ['nullable', 'string', 'required_with:email'],
            // Auth étudiant : nom + matricule
            'nom' => ['nullable', 'string', 'required_without:email'],
            'matricule' => ['nullable', 'string', 'required_without:email'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Veuillez saisir votre adresse e-mail ou vos identifiants étudiant (Nom + Matricule).',
            'nom.required_without' => 'Le nom est requis pour la connexion étudiant.',
            'matricule.required_without' => 'Le matricule est requis pour la connexion étudiant.',
        ];
    }
}
