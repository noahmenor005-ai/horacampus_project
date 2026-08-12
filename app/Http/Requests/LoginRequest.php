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
            'identifiant' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string'],
            'nom' => ['nullable', 'string'],
            'matricule' => ['nullable', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasStudent = $this->filled('nom') && $this->filled('matricule');
            $hasStaff = ($this->filled('identifiant') || $this->filled('email') || ($this->filled('nom') && $this->filled('password') && !$this->filled('matricule')))
                && $this->filled('password');

            if (!$hasStudent && !$hasStaff) {
                $validator->errors()->add('email', 'Veuillez fournir un identifiant (email ou nom) et un mot de passe, ou Nom + Matricule pour un étudiant.');
            }
        });
    }
}
