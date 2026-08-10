<?php

namespace App\Http\Requests;

use App\Models\Domaine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnseignantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->isDecanat();
    }

    public function rules(): array
    {
        $enseignantId = null;
        $routeParam = $this->route('enseignant');
        if ($routeParam) {
            $enseignantId = $routeParam instanceof \App\Models\User ? $routeParam->id : $routeParam->id ?? null;
        }

        return [
            'nom' => ['required', 'string', 'max:100'],
            'postnom' => ['nullable', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'matricule' => ['nullable', 'string', 'max:50', Rule::unique('users', 'matricule')->ignore($enseignantId)],
            'sexe' => ['nullable', Rule::in(['M', 'F', 'Autre'])],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($enseignantId)],
            'faculte_id' => ['nullable', 'exists:facultes,id'],
            'ec_ids' => ['nullable', 'array'],
            'ec_ids.*' => ['integer', 'exists:ecs,id'],
            'specialite' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();
            $faculteId = $this->user()->faculte_id;
            if ($this->user()->isDecanat() && $faculteId) {
                if (isset($data['faculte_id']) && (int)$data['faculte_id'] !== (int)$faculteId) {
                    $validator->errors()->add('faculte_id', 'Vous ne pouvez gérer que les enseignants de votre propre faculté.');
                }
            }
        });
    }
}
