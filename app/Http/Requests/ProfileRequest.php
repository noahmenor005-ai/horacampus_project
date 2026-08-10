<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // L'étudiant ne peut modifier AUCUNE donnée académique (promotion, domaine, filière, mention, matricule, faculté)
        // Seuls les champs personnels de base sont autorisés
        $user = $this->user();
        $rules = [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
        ];

        // Postnom autorisé pour correction mineure (mais pas matricule)
        if ($user && $user->isEtudiant()) {
            $rules['postnom'] = ['nullable', 'string', 'max:100'];
            // On ignore toute tentative de modification de champs académiques
        } else {
            $rules['postnom'] = ['nullable', 'string', 'max:100'];
        }

        // Photo optionnelle
        $rules['photo'] = ['nullable', 'image', 'max:2048'];

        return $rules;
    }
}
