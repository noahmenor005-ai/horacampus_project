<?php

namespace App\Http\Controllers;

use App\Models\Auditoire;
use App\Models\Batiment;
use Illuminate\Http\Request;

class AuditoireController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoire::with('batiment')->latest();

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'like', $term)
                    ->orWhere('numero', 'like', $term)
                    ->orWhere('equipements', 'like', $term)
                    ->orWhereHas('batiment', fn ($b) => $b->where('nom', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('etat')) {
            $query->where('etat', $request->input('etat'));
        }

        if ($request->filled('batiment_id')) {
            $query->where('batiment_id', $request->input('batiment_id'));
        }

        return view('auditoires.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'batiments' => Batiment::orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('auditoires.form', [
            'item' => new Auditoire(['type' => 'cours', 'etat' => 'disponible', 'disponibilite' => true, 'statut' => 'actif']),
            'batiments' => Batiment::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Auditoire::create($this->validated($request));

        return redirect()->route('auditoires.index')->with('success', 'Auditoire créé.');
    }

    public function edit(Auditoire $auditoire)
    {
        return view('auditoires.form', [
            'item' => $auditoire,
            'batiments' => Batiment::orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Auditoire $auditoire)
    {
        $auditoire->update($this->validated($request, $auditoire));

        return redirect()->route('auditoires.index')->with('success', 'Auditoire mis à jour.');
    }

    public function destroy(Auditoire $auditoire)
    {
        if ($auditoire->horaires()->exists()) {
            return back()->with('error', 'Impossible de supprimer un auditoire déjà utilisé dans des horaires.');
        }

        $auditoire->delete();

        return back()->with('success', 'Auditoire supprimé.');
    }

    private function validated(Request $request, ?Auditoire $item = null): array
    {
        $unique = 'unique:auditoires,nom';
        if ($item) {
            $unique .= ',' . $item->id;
        }

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', $unique],
            'numero' => ['nullable', 'string', 'max:50'],
            'batiment_id' => ['required', 'exists:batiments,id'],
            'capacite' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:' . implode(',', array_keys(Auditoire::TYPES))],
            'etat' => ['required', 'in:' . implode(',', array_keys(Auditoire::ETATS))],
            'statut' => ['nullable', 'string', 'max:30'],
            'equipements' => ['nullable', 'array'],
            'equipements.*' => ['string'],
            'disponibilite' => ['nullable', 'boolean'],
        ]);

        $data['numero'] = $data['numero'] ?: $data['nom'];
        $data['equipements'] = isset($data['equipements']) ? implode(', ', $data['equipements']) : null;
        $data['disponibilite'] = $request->boolean('disponibilite', true);
        $data['statut'] = $data['statut'] ?? 'actif';

        return $data;
    }
}
