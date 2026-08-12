<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAcademicResources;
use App\Http\Requests\MentionRequest;
use App\Models\Mention;
use Illuminate\Http\Request;

class MentionController extends Controller
{
    use ManagesAcademicResources;

    public function index(Request $request)
    {
        $query = Mention::with('filiere.domaine')->withCount('promotions')->latest();

        if ($this->isScoped()) {
            $query->whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        if ($request->filled('filiere_id')) {
            $query->where('filiere_id', $request->input('filiere_id'));
        }

        $this->applySearch($query, $request, ['nom', 'description', 'filiere.nom']);

        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        return view('mentions.index', [
            'mentions' => $query->paginate(12)->withQueryString(),
            'filieres' => $this->scopeFilieres()->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('mentions.form', [
            'mention' => new Mention(['actif' => true]),
            'filieres' => $this->scopeFilieres()->with('domaine')->orderBy('nom')->get(),
        ]);
    }

    public function store(MentionRequest $request)
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', true);

        Mention::create($data);

        return redirect()->route('decanat.mentions.index')->with('success', 'Mention créée avec succès.');
    }

    public function show(Mention $mention)
    {
        $this->assertOwned($mention);
        $mention->load(['filiere.domaine.faculte', 'promotions.anneeAcademique']);

        return view('mentions.show', compact('mention'));
    }

    public function edit(Mention $mention)
    {
        $this->assertOwned($mention);

        return view('mentions.form', [
            'mention' => $mention,
            'filieres' => $this->scopeFilieres()->with('domaine')->orderBy('nom')->get(),
        ]);
    }

    public function update(MentionRequest $request, Mention $mention)
    {
        $this->assertOwned($mention);

        $data = $request->validated();
        $data['actif'] = $request->boolean('actif', $mention->actif);
        $mention->update($data);

        return redirect()->route('decanat.mentions.index')->with('success', 'Mention mise à jour.');
    }

    public function destroy(Mention $mention)
    {
        $this->assertOwned($mention);

        if ($mention->promotions()->exists()) {
            return $this->denyDelete('Cette mention ne peut pas être supprimée car elle possède des promotions.');
        }

        $mention->delete();

        return back()->with('success', 'Mention supprimée.');
    }

    public function toggle(Mention $mention)
    {
        return $this->toggleFlag($mention, 'actif');
    }
}
