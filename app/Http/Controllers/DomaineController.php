<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAcademicResources;
use App\Http\Requests\DomaineRequest;
use App\Models\Domaine;
use App\Models\Faculte;
use Illuminate\Http\Request;

class DomaineController extends Controller
{
    use ManagesAcademicResources;

    public function index(Request $request)
    {
        $query = Domaine::with('faculte')->withCount('filieres')->latest();

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        } elseif ($request->filled('faculte_id')) {
            $query->where('faculte_id', $request->input('faculte_id'));
        }

        $this->applySearch($query, $request, ['nom', 'description']);

        if ($request->filled('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        return view('domaines.index', [
            'domaines' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('domaines.form', [
            'domaine' => new Domaine(['actif' => true, 'faculte_id' => $this->facultyId()]),
            'facultes' => $this->facultesChoices(),
        ]);
    }

    public function store(DomaineRequest $request)
    {
        $data = $request->validated();
        $data['faculte_id'] = $this->isScoped() ? $this->facultyId() : ($data['faculte_id'] ?? $this->facultyId());
        $data['actif'] = $request->boolean('actif', true);

        Domaine::create($data);

        return redirect()->route('decanat.domaines.index')->with('success', 'Domaine créé avec succès.');
    }

    public function show(Domaine $domaine)
    {
        $this->assertOwned($domaine);
        $domaine->load(['faculte', 'filieres.mentions']);

        return view('domaines.show', compact('domaine'));
    }

    public function edit(Domaine $domaine)
    {
        $this->assertOwned($domaine);

        return view('domaines.form', [
            'domaine' => $domaine,
            'facultes' => $this->facultesChoices(),
        ]);
    }

    public function update(DomaineRequest $request, Domaine $domaine)
    {
        $this->assertOwned($domaine);

        $data = $request->validated();
        $data['faculte_id'] = $this->isScoped() ? $this->facultyId() : ($data['faculte_id'] ?? $domaine->faculte_id);
        $data['actif'] = $request->boolean('actif', $domaine->actif);

        $domaine->update($data);

        return redirect()->route('decanat.domaines.index')->with('success', 'Domaine mis à jour.');
    }

    public function destroy(Domaine $domaine)
    {
        $this->assertOwned($domaine);

        if ($domaine->filieres()->exists()) {
            return $this->denyDelete('Ce domaine ne peut pas être supprimé car il possède des filières.');
        }

        $domaine->delete();

        return back()->with('success', 'Domaine supprimé.');
    }

    public function toggle(Domaine $domaine)
    {
        return $this->toggleFlag($domaine, 'actif');
    }

    private function facultesChoices()
    {
        return $this->isScoped()
            ? Faculte::where('id', $this->facultyId())->orderBy('nom')->get()
            : Faculte::orderBy('nom')->get();
    }
}
