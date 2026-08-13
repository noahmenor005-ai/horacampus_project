<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use Illuminate\Http\Request;

class BatimentController extends Controller
{
    public function index(Request $request)
    {
        $query = Batiment::withCount('auditoires')->latest();

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('adresse', 'like', $term)
                    ->orWhere('localisation', 'like', $term);
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        return view('batiments.index', [
            'items' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('batiments.form', ['item' => new Batiment(['statut' => 'actif', 'nombre_etages' => 1])]);
    }

    public function store(Request $request)
    {
        Batiment::create($this->validated($request));

        return redirect()->route('batiments.index')->with('success', 'Bâtiment créé.');
    }

    public function edit(Batiment $batiment)
    {
        return view('batiments.form', ['item' => $batiment]);
    }

    public function update(Request $request, Batiment $batiment)
    {
        $batiment->update($this->validated($request, $batiment));

        return redirect()->route('batiments.index')->with('success', 'Bâtiment mis à jour.');
    }

    public function destroy(Batiment $batiment)
    {
        if ($batiment->auditoires()->exists()) {
            return back()->with('error', 'Impossible de supprimer un bâtiment qui contient encore des auditoires.');
        }

        $batiment->delete();

        return back()->with('success', 'Bâtiment supprimé.');
    }

    private function validated(Request $request, ?Batiment $item = null): array
    {
        $unique = 'unique:batiments,code';
        if ($item) {
            $unique .= ',' . $item->id;
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', $unique],
            'nom' => ['required', 'string', 'max:150'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'nombre_etages' => ['nullable', 'integer', 'min:0', 'max:50'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'in:actif,inactif,maintenance'],
        ]);

        $data['adresse'] = $data['adresse'] ?: ($data['localisation'] ?? null);

        return $data;
    }
}
