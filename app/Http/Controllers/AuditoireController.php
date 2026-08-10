<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CrudController;
use App\Models\Auditoire;
use App\Models\Batiment;

class AuditoireController extends CrudController
{
    protected $model = Auditoire::class;
    protected $title = 'Auditoire';
    protected $route = 'auditoires';
    protected $relations = ['batiment'];
    protected $fields = [
        'nom' => 'required|string|max:100|unique:auditoires,nom',
        'batiment_id' => 'nullable|exists:batiments,id',
        'capacite' => 'required|integer|min:1',
        'type' => 'required|string',
        'equipements' => 'nullable|string',
        'disponibilite' => 'nullable|boolean',
        'etat' => 'required|string',
    ];

    protected function selects(): array
    {
        return [
            'batiment_id' => Batiment::orderBy('nom')->pluck('nom', 'id'),
            'type' => Auditoire::TYPES,
            'etat' => Auditoire::ETATS,
        ];
    }
}
