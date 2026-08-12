<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LmdCrudController extends Controller
{
    private function map(string $resource): ?string
    {
        return [
            'domaines' => 'decanat.domaines.index',
            'filieres' => 'decanat.filieres.index',
            'mentions' => 'decanat.mentions.index',
            'promotions' => 'decanat.promotions.index',
            'annees' => 'decanat.annees-academiques.index',
            'semestres' => 'decanat.semestres.index',
            'ues' => 'decanat.ues.index',
            'ecs' => 'decanat.ecs.index',
        ][$resource] ?? null;
    }

    public function index(Request $request, string $resource)
    {
        return $this->forward($resource);
    }

    public function create(string $resource)
    {
        $targets = [
            'domaines' => 'decanat.domaines.create',
            'filieres' => 'decanat.filieres.create',
            'mentions' => 'decanat.mentions.create',
            'promotions' => 'decanat.promotions.create',
            'annees' => 'decanat.annees-academiques.create',
            'semestres' => 'decanat.semestres.create',
            'ues' => 'decanat.ues.create',
            'ecs' => 'decanat.ecs.create',
        ];

        abort_unless(isset($targets[$resource]), 404);

        return redirect()->route($targets[$resource]);
    }

    public function store(Request $request, string $resource)
    {
        return $this->forward($resource);
    }

    public function edit(string $resource, $id)
    {
        $targets = [
            'domaines' => ['decanat.domaines.edit', 'domaine'],
            'filieres' => ['decanat.filieres.edit', 'filiere'],
            'mentions' => ['decanat.mentions.edit', 'mention'],
            'promotions' => ['decanat.promotions.edit', 'promotion'],
            'annees' => ['decanat.annees-academiques.edit', 'annee'],
            'semestres' => ['decanat.semestres.edit', 'semestre'],
            'ues' => ['decanat.ues.edit', 'ue'],
            'ecs' => ['decanat.ecs.edit', 'ec'],
        ];

        abort_unless(isset($targets[$resource]), 404);

        return redirect()->route($targets[$resource][0], [$targets[$resource][1] => $id]);
    }

    public function update(Request $request, string $resource, $id)
    {
        return $this->forward($resource);
    }

    public function destroy(string $resource, $id)
    {
        return $this->forward($resource);
    }

    private function forward(string $resource)
    {
        $route = $this->map($resource);
        abort_unless($route, 404);

        return redirect()->route($route);
    }
}
