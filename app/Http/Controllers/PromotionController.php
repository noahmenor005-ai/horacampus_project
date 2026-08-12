<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAcademicResources;
use App\Http\Requests\PromotionRequest;
use App\Models\AnneeAcademique;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    use ManagesAcademicResources;

    public function index(Request $request)
    {
        $query = Promotion::with(['mention.filiere.domaine', 'anneeAcademique'])->withCount('etudiants')->latest();

        if ($this->isScoped()) {
            $query->whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        if ($request->filled('mention_id')) {
            $query->where('mention_id', $request->input('mention_id'));
        }

        if ($request->filled('annee_academique_id')) {
            $query->where('annee_academique_id', $request->input('annee_academique_id'));
        }

        if ($request->filled('niveau')) {
            $query->where('niveau', $request->input('niveau'));
        }

        $this->applySearch($query, $request, ['nom', 'niveau', 'mention.nom']);

        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        return view('promotions.index', [
            'promotions' => $query->paginate(12)->withQueryString(),
            'mentions' => $this->scopeMentions()->orderBy('nom')->get(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function create()
    {
        return view('promotions.form', $this->formData(new Promotion(['actif' => true, 'effectif' => 0, 'niveau' => 'L1'])));
    }

    public function store(PromotionRequest $request)
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', true);

        Promotion::create($data);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Promotion créée avec succès.');
    }

    public function show(Promotion $promotion)
    {
        $this->assertOwned($promotion);
        $promotion->load(['mention.filiere.domaine.faculte', 'anneeAcademique', 'ues', 'etudiants']);

        return view('promotions.show', compact('promotion'));
    }

    public function edit(Promotion $promotion)
    {
        $this->assertOwned($promotion);

        return view('promotions.form', $this->formData($promotion));
    }

    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $this->assertOwned($promotion);

        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', $promotion->actif);
        $promotion->update($data);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Promotion mise à jour.');
    }

    public function destroy(Promotion $promotion)
    {
        $this->assertOwned($promotion);

        if ($promotion->etudiants()->exists()) {
            return $this->denyDelete('Cette promotion ne peut pas être supprimée car elle possède des étudiants.');
        }

        if ($promotion->ues()->exists()) {
            return $this->denyDelete('Cette promotion ne peut pas être supprimée car elle possède des UE.');
        }

        if ($promotion->horaires()->exists()) {
            return $this->denyDelete('Cette promotion ne peut pas être supprimée car elle possède des horaires.');
        }

        $promotion->delete();

        return back()->with('success', 'Promotion supprimée.');
    }

    public function toggle(Promotion $promotion)
    {
        return $this->toggleFlag($promotion, 'actif');
    }

    private function formData(Promotion $promotion): array
    {
        return [
            'promotion' => $promotion,
            'mentions' => $this->scopeMentions()->with('filiere')->orderBy('nom')->get(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
            'routePrefix' => $this->routePrefix(),
        ];
    }

    private function routePrefix(): string
    {
        return auth()->user() && auth()->user()->isDecanat() ? 'decanat.promotions' : 'promotions';
    }
}
