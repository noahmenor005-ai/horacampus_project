<?php

namespace App\Http\Requests;

use App\Models\Domaine;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EtudiantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->isDecanat();
    }

    public function rules(): array
    {
        $etudiantId = $this->route('etudiant') ? $this->route('etudiant')->id : null;
        // Support aussi pour route model binding User
        if (!$etudiantId && $this->route('etudiant') instanceof \App\Models\User) {
            $etudiantId = $this->route('etudiant')->id;
        }

        return [
            'nom' => ['required', 'string', 'max:100'],
            'postnom' => ['nullable', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'matricule' => ['required', 'string', 'max:50', Rule::unique('users', 'matricule')->ignore($etudiantId)],
            'sexe' => ['required', Rule::in(['M', 'F', 'Autre'])],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($etudiantId)],
            'faculte_id' => ['nullable', 'exists:facultes,id'],
            'domaine_id' => ['required', 'exists:domaines,id'],
            'filiere_id' => ['required', 'exists:filieres,id'],
            'mention_id' => ['required', 'exists:mentions,id'],
            'promotion_id' => ['required', 'exists:promotions,id'],
            'annee_academique_id' => ['nullable', 'exists:annees_academiques,id'],
            'statut' => ['nullable', Rule::in(['actif', 'inactif'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();
            // Ne pas faire confiance aux IDs: vérification côté serveur de la hiérarchie LMD
            $faculteId = $this->user()->faculte_id;
            // Le Décanat ne peut créer que dans sa propre faculté
            if ($this->user()->isDecanat() && $faculteId) {
                // Forcer la faculté du décanat si différente
                if (isset($data['faculte_id']) && (int)$data['faculte_id'] !== (int)$faculteId) {
                    $validator->errors()->add('faculte_id', 'Vous ne pouvez gérer que les étudiants de votre propre faculté.');
                }
                // Vérifier domaine appartient à la faculté du décanat
                if (!empty($data['domaine_id'])) {
                    $domaine = Domaine::find($data['domaine_id']);
                    if ($domaine && (int)$domaine->faculte_id !== (int)$faculteId) {
                        $validator->errors()->add('domaine_id', 'Ce domaine n’appartient pas à votre faculté.');
                    }
                }
            }

            // Vérifications hiérarchiques
            if (!empty($data['domaine_id']) && !empty($data['filiere_id'])) {
                $filiere = Filiere::find($data['filiere_id']);
                if ($filiere && (int)$filiere->domaine_id !== (int)$data['domaine_id']) {
                    $validator->errors()->add('filiere_id', 'Cette filière n’appartient pas au domaine sélectionné.');
                }
            }
            if (!empty($data['filiere_id']) && !empty($data['mention_id'])) {
                $mention = Mention::find($data['mention_id']);
                if ($mention && (int)$mention->filiere_id !== (int)$data['filiere_id']) {
                    $validator->errors()->add('mention_id', 'Cette mention n’appartient pas à la filière sélectionnée.');
                }
            }
            if (!empty($data['mention_id']) && !empty($data['promotion_id'])) {
                $promotion = Promotion::find($data['promotion_id']);
                if ($promotion && (int)$promotion->mention_id !== (int)$data['mention_id']) {
                    $validator->errors()->add('promotion_id', 'Cette promotion n’appartient pas à la mention sélectionnée.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'matricule.required' => 'Le matricule est obligatoire et doit être unique.',
            'sexe.required' => 'Le sexe est obligatoire.',
            'domaine_id.required' => 'Veuillez choisir un domaine.',
            'filiere_id.required' => 'Veuillez choisir une filière.',
            'mention_id.required' => 'Veuillez choisir une mention.',
            'promotion_id.required' => 'Veuillez choisir une promotion.',
        ];
    }
}
