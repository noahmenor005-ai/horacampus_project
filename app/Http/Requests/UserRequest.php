<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        // L'administrateur ne doit PAS créer directement les étudiants et enseignants
        // Il ne gère que les comptes Décanat (et admin)
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'telephone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_DECANAT])],
            'status' => ['required', Rule::in(User::STATUSES)],
            'faculte_id' => ['required_if:role,decanat', 'nullable', 'exists:facultes,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'L\'administrateur ne peut créer que des comptes Décanat ou Administrateur. Les étudiants et enseignants sont créés par le Décanat.',
            'faculte_id.required_if' => 'Veuillez choisir la faculté du Décanat.',
        ];
    }
}
