<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAcademicResources;
use App\Http\Requests\UeRequest;
use App\Models\AnneeAcademique;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\Ue;
use Illuminate\Http\Request;

class UeController extends Controller
{
    use ManagesAcademicResources;

    public function index(Request $request)
    {
        $query = Ue::with(['promotion.mention', 'semestre', 'anneeAcademique'])->withCount('ecs')->latest();

        if ($this->isScoped()) {
            $query->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        $this->applySearch($query, $request, ['code', 'nom', 'description', 'promotion.nom']);

        foreach (['promotion_id', 'semestre_id', 'mention_id', 'annee_academique_id', 'statut'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        return view('ues.index', [
            'ues' => $query->paginate(12)->withQueryString(),
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
            'semestres' => Semestre::orderBy('libelle')->get(),
        ]);
    }

    public function create()
    {
        return view('ues.form', $this->formData(new Ue(['statut' => 'actif', 'credit' => 0])));
    }

    public function store(UeRequest $request)
    {
        $data = $this->hydrate($request->validated());
        Ue::create($data);

        return redirect()->route('decanat.ues.index')->with('success', 'Unité d\'enseignement créée avec succès.');
    }

    public function show(Ue $ue)
    {
        $this->assertOwned($ue);
        $ue->load(['promotion.mention.filiere.domaine', 'semestre', 'anneeAcademique', 'mention', 'ecs.enseignant']);

        return view('ues.show', compact('ue'));
    }

    public function edit(Ue $ue)
    {
        $this->assertOwned($ue);

        return view('ues.form', $this->formData($ue));
    }

    public function update(UeRequest $request, Ue $ue)
    {
        $this->assertOwned($ue);
        $ue->update($this->hydrate($request->validated(), $ue));

        return redirect()->route('decanat.ues.index')->with('success', 'UE mise à jour.');
    }

    public function destroy(Ue $ue)
    {
        $this->assertOwned($ue);

        if ($ue->ecs()->exists()) {
            return $this->denyDelete('Cette UE ne peut pas être supprimée car elle possède des EC.');
        }

        $ue->delete();

        return back()->with('success', 'UE supprimée.');
    }

    public function toggle(Ue $ue)
    {
        $this->assertOwned($ue);
        $ue->update(['statut' => $ue->estActif() ? 'inactif' : 'actif']);

        return back()->with('success', $ue->estActif() ? 'UE activée.' : 'UE désactivée.');
    }

    private function hydrate(array $data, ?Ue $ue = null): array
    {
        $data['statut'] = $data['statut'] ?? 'actif';

        if (empty($data['mention_id']) && !empty($data['promotion_id'])) {
            $promotion = Promotion::find($data['promotion_id']);
            $data['mention_id'] = $promotion->mention_id ?? null;
            if (empty($data['annee_academique_id'])) {
                $data['annee_academique_id'] = $promotion->annee_academique_id ?? null;
            }
        }

        return $data;
    }

    private function formData(Ue $ue): array
    {
        return [
            'ue' => $ue,
            'promotions' => $this->scopePromotions()->with('mention')->orderBy('nom')->get(),
            'mentions' => $this->scopeMentions()->orderBy('nom')->get(),
            'semestres' => Semestre::with('anneeAcademique')->orderByDesc('id')->get(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ];
    }
}
