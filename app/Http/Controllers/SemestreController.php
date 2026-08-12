<?php

namespace App\Http\Controllers;

use App\Http\Requests\SemestreRequest;
use App\Models\AnneeAcademique;
use App\Models\Semestre;
use Illuminate\Http\Request;

class SemestreController extends Controller
{
    public function index(Request $request)
    {
        $query = Semestre::with('anneeAcademique')->withCount('ues')->latest();

        if ($request->filled('q')) {
            $query->where('libelle', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('annee_academique_id')) {
            $query->where('annee_academique_id', $request->input('annee_academique_id'));
        }

        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        return view('semestres.index', [
            'semestres' => $query->paginate(12)->withQueryString(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ]);
    }

    public function create()
    {
        return view('semestres.form', [
            'semestre' => new Semestre(['actif' => true, 'libelle' => 'Semestre 1']),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ]);
    }

    public function store(SemestreRequest $request)
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', true);

        Semestre::create($data);

        return redirect()->route('decanat.semestres.index')->with('success', 'Semestre créé avec succès.');
    }

    public function show(Semestre $semestre)
    {
        $semestre->load(['anneeAcademique', 'ues.promotion']);

        return view('semestres.show', compact('semestre'));
    }

    public function edit(Semestre $semestre)
    {
        return view('semestres.form', [
            'semestre' => $semestre,
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ]);
    }

    public function update(SemestreRequest $request, Semestre $semestre)
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', $semestre->actif);
        $semestre->update($data);

        return redirect()->route('decanat.semestres.index')->with('success', 'Semestre mis à jour.');
    }

    public function destroy(Semestre $semestre)
    {
        if ($semestre->ues()->exists()) {
            return back()->with('error', 'Ce semestre ne peut pas être supprimé car il possède des UE.');
        }

        if ($semestre->horaires()->exists()) {
            return back()->with('error', 'Ce semestre ne peut pas être supprimé car il possède des horaires.');
        }

        $semestre->delete();

        return back()->with('success', 'Semestre supprimé.');
    }

    public function toggle(Semestre $semestre)
    {
        $semestre->update(['actif' => !$semestre->actif]);

        return back()->with('success', $semestre->actif ? 'Semestre activé.' : 'Semestre désactivé.');
    }
}
