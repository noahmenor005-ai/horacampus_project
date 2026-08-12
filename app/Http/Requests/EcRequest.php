<?php

namespace App\Http\Requests;

use App\Models\Ec;
use App\Models\Ue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->isDecanat() || auth()->user()->isAdmin());
    }

    public function rules(): array
    {
        $ec = $this->route('ec');
        $id = $ec instanceof Ec ? $ec->id : null;

        return [
            'ue_id' => ['required', 'exists:ues,id'],
            'code' => ['required', 'string', 'max:30', Rule::unique('ecs', 'code')->ignore($id)],
            'nom' => ['required', 'string', 'max:150'],
            'volume_horaire' => ['required', 'integer', 'min:1'],
            'coefficient' => ['nullable', 'integer', 'min:0'],
            'credit' => ['nullable', 'integer', 'min:0', 'max:30'],
            'enseignant_id' => ['nullable', 'exists:users,id'],
            'statut' => ['nullable', Rule::in(array_keys(Ec::STATUTS))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ue = Ue::with('promotion.mention.filiere.domaine')->find($this->input('ue_id'));
            if (!$ue) {
                return;
            }

            $user = $this->user();
            $facultyId = optional(optional(optional(optional($ue->promotion)->mention)->filiere)->domaine)->faculte_id;
            if ($user && $user->isDecanat() && (int) $facultyId !== (int) $user->faculte_id) {
                $validator->errors()->add('ue_id', 'Cette UE n\'appartient pas à votre faculté.');
            }

            if ($this->filled('enseignant_id') && $user && $user->isDecanat()) {
                $enseignant = \App\Models\User::find($this->input('enseignant_id'));
                if ($enseignant && (int) $enseignant->faculte_id !== (int) $user->faculte_id) {
                    $validator->errors()->add('enseignant_id', 'Cet enseignant n\'appartient pas à votre faculté.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'ue_id.required' => 'Un EC doit appartenir à une UE.',
            'code.required' => 'Le code de l\'EC est obligatoire.',
            'code.unique' => 'Ce code d\'EC est déjà utilisé.',
            'nom.required' => 'L\'intitulé de l\'EC est obligatoire.',
            'volume_horaire.required' => 'Le nombre d\'heures est obligatoire.',
        ];
    }
}
