<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAcademicResources;
use App\Http\Requests\FiliereRequest;
use App\Models\Filiere;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
    use ManagesAcademicResources;

    public function index(Request $request)
    {
        $query = Filiere::with('domaine.faculte')->withCount('mentions')->latest();

        if ($this->isScoped()) {
            $query->whereHas('domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        if ($request->filled('domaine_id')) {
            $query->where('domaine_id', $request->input('domaine_id'));
        }

        $this->applySearch($query, $request, ['nom', 'description', 'domaine.nom']);

        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        return view('filieres.index', [
            'filieres' => $query->paginate(12)->withQueryString(),
            'domaines' => $this->scopeDomaines()->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('filieres.form', [
            'filiere' => new Filiere(['actif' => true]),
            'domaines' => $this->scopeDomaines()->orderBy('nom')->get(),
        ]);
    }

    public function store(FiliereRequest $request)
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', true);

        Filiere::create($data);

        return redirect()->route('decanat.filieres.index')->with('success', 'Filière créée avec succès.');
    }

    public function show(Filiere $filiere)
    {
        $this->assertOwned($filiere);
        $filiere->load(['domaine.faculte', 'mentions.promotions']);

        return view('filieres.show', compact('filiere'));
    }

    public function edit(Filiere $filiere)
    {
        $this->assertOwned($filiere);

        return view('filieres.form', [
            'filiere' => $filiere,
            'domaines' => $this->scopeDomaines()->orderBy('nom')->get(),
        ]);
    }

    public function update(FiliereRequest $request, Filiere $filiere)
    {
        $this->assertOwned($filiere);

        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', $filiere->actif);
        $filiere->update($data);

        return redirect()->route('decanat.filieres.index')->with('success', 'Filière mise à jour.');
    }

    public function destroy(Filiere $filiere)
    {
        $this->assertOwned($filiere);

        if ($filiere->mentions()->exists()) {
            return $this->denyDelete('Cette filière ne peut pas être supprimée car elle possède des mentions.');
        }

        $filiere->delete();

        return back()->with('success', 'Filière supprimée.');
    }

    public function toggle(Filiere $filiere)
    {
        return $this->toggleFlag($filiere, 'actif');
    }
}
