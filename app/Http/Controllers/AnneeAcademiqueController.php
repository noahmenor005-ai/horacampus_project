<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnneeAcademiqueRequest;
use App\Models\AnneeAcademique;
use Illuminate\Http\Request;

class AnneeAcademiqueController extends Controller
{
    public function index(Request $request)
    {
        $query = AnneeAcademique::withCount(['semestres', 'promotions'])->orderByDesc('libelle');

        if ($request->filled('q')) {
            $query->where('libelle', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return view('annees.index', [
            'annees' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('annees.form', [
            'annee' => new AnneeAcademique(['active' => false]),
        ]);
    }

    public function store(AnneeAcademiqueRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $annee = AnneeAcademique::create($data);

        if ($annee->active) {
            $annee->activerUniquement();
        }

        return redirect()->route('decanat.annees-academiques.index')->with('success', 'Année académique créée avec succès.');
    }

    public function show(AnneeAcademique $annee)
    {
        $annee->load(['semestres', 'promotions.mention']);

        return view('annees.show', compact('annee'));
    }

    public function edit(AnneeAcademique $annee)
    {
        return view('annees.form', compact('annee'));
    }

    public function update(AnneeAcademiqueRequest $request, AnneeAcademique $annee)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', $annee->active);
        $annee->update($data);

        if ($annee->active) {
            $annee->activerUniquement();
        }

        return redirect()->route('decanat.annees-academiques.index')->with('success', 'Année académique mise à jour.');
    }

    public function destroy(AnneeAcademique $annee)
    {
        if ($annee->semestres()->exists()) {
            return back()->with('error', 'Cette année académique ne peut pas être supprimée car elle possède des semestres.');
        }

        if ($annee->promotions()->exists()) {
            return back()->with('error', 'Cette année académique ne peut pas être supprimée car elle possède des promotions.');
        }

        $annee->delete();

        return back()->with('success', 'Année académique supprimée.');
    }

    public function toggle(AnneeAcademique $annee)
    {
        if ($annee->active) {
            $annee->update(['active' => false]);
            return back()->with('success', 'Année académique désactivée.');
        }

        $annee->activerUniquement();

        return back()->with('success', 'Année académique activée. Les autres années ont été désactivées.');
    }
}
