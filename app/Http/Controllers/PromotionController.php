<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CrudController;
use App\Models\AnneeAcademique;
use App\Models\Mention;
use App\Models\Promotion;

class PromotionController extends CrudController
{
    protected $model = Promotion::class;
    protected $title = 'Promotion';
    protected $route = 'promotions';
    protected $relations = ['mention', 'anneeAcademique'];
    protected $fields = [
        'mention_id' => 'required|exists:mentions,id',
        'annee_academique_id' => 'nullable|exists:annees_academiques,id',
        'nom' => 'required|string|max:100',
        'niveau' => 'required|string|max:50',
        'effectif' => 'required|integer|min:0',
    ];

    protected function selects(): array
    {
        return [
            'mention_id' => Mention::orderBy('nom')->get()->mapWithKeys(fn ($m) => [$m->id => $m->nom . ' — ' . optional($m->filiere)->nom]),
            'annee_academique_id' => AnneeAcademique::orderByDesc('id')->pluck('libelle', 'id'),
            'niveau' => ['L1' => 'L1', 'L2' => 'L2', 'L3' => 'L3', 'M1' => 'M1', 'M2' => 'M2'],
        ];
    }
}
