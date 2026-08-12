<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAcademicResources;
use App\Http\Requests\EcRequest;
use App\Models\Ec;
use Illuminate\Http\Request;

class EcController extends Controller
{
    use ManagesAcademicResources;

    public function index(Request $request)
    {
        $query = Ec::with(['ue.promotion', 'enseignant'])->withCount('cours')->latest();

        if ($this->isScoped()) {
            $query->whereHas('ue.promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        $this->applySearch($query, $request, ['code', 'nom', 'ue.nom', 'ue.code']);

        if ($request->filled('ue_id')) {
            $query->where('ue_id', $request->input('ue_id'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        return view('ecs.index', [
            'ecs' => $query->paginate(12)->withQueryString(),
            'ues' => $this->scopeUes()->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('ecs.form', $this->formData(new Ec(['statut' => 'actif', 'coefficient' => 1])));
    }

    public function store(EcRequest $request)
    {
        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? 'actif';
        $data['coefficient'] = $data['coefficient'] ?? 1;

        $ec = Ec::create($data);

        if (!empty($data['enseignant_id'])) {
            $ec->enseignants()->syncWithoutDetaching([$data['enseignant_id']]);
        }

        return redirect()->route('decanat.ecs.index')->with('success', 'Élément constitutif créé avec succès.');
    }

    public function show(Ec $ec)
    {
        $this->assertOwned($ec);
        $ec->load(['ue.promotion.mention', 'enseignant', 'cours.enseignant', 'horaires']);

        return view('ecs.show', compact('ec'));
    }

    public function edit(Ec $ec)
    {
        $this->assertOwned($ec);

        return view('ecs.form', $this->formData($ec));
    }

    public function update(EcRequest $request, Ec $ec)
    {
        $this->assertOwned($ec);

        $data = $request->validated();
        $data['statut'] = $data['statut'] ?? $ec->statut;
        $ec->update($data);

        if (!empty($data['enseignant_id'])) {
            $ec->enseignants()->syncWithoutDetaching([$data['enseignant_id']]);
        }

        return redirect()->route('decanat.ecs.index')->with('success', 'EC mis à jour.');
    }

    public function destroy(Ec $ec)
    {
        $this->assertOwned($ec);

        if ($ec->cours()->exists()) {
            return $this->denyDelete('Cet EC ne peut pas être supprimé car il est utilisé par des cours.');
        }

        if ($ec->horaires()->exists()) {
            return $this->denyDelete('Cet EC ne peut pas être supprimé car il est programmé dans des horaires.');
        }

        $ec->delete();

        return back()->with('success', 'EC supprimé.');
    }

    public function toggle(Ec $ec)
    {
        $this->assertOwned($ec);
        $ec->update(['statut' => $ec->estActif() ? 'inactif' : 'actif']);

        return back()->with('success', $ec->estActif() ? 'EC activé.' : 'EC désactivé.');
    }

    private function formData(Ec $ec): array
    {
        return [
            'ec' => $ec,
            'ues' => $this->scopeUes()->with('promotion')->orderBy('nom')->get(),
            'enseignants' => $this->scopeEnseignants()->get(),
        ];
    }
}
