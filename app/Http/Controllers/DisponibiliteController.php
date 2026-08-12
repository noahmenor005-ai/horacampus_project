<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Http\Requests\DisponibiliteRequest;
use App\Models\Disponibilite;
use App\Models\Semestre;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DisponibiliteService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisponibiliteController extends Controller
{
    public function index(Request $request, DisponibiliteService $service)
    {
        $user = $request->user();

        $query = Disponibilite::with(['user', 'semestre'])->latest();

        if ($user->isEnseignant()) {
            $query->where('user_id', $user->id);
        }

        if ($user->isDecanat()) {
            $query->whereHas('user', fn ($q) => $q->where('faculte_id', $user->faculte_id));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('enseignant_id')) {
            $query->where('user_id', $request->input('enseignant_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('jour', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', $term)->orWhere('prenom', 'like', $term));
            });
        }

        $disponibilites = $query->paginate(12)->withQueryString();

        return view('disponibilites.index', [
            'disponibilites' => $disponibilites,
            'enseignants' => $this->enseignantsList(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Disponibilite::class);

        return view('disponibilites.form', [
            'disponibilite' => new Disponibilite(),
            'enseignants' => $this->enseignantsList(),
            'semestres' => Semestre::orderByDesc('id')->get()->pluck('libelle', 'id'),
            'annees' => \App\Models\AnneeAcademique::orderByDesc('libelle')->pluck('libelle', 'id'),
        ]);
    }

    public function store(DisponibiliteRequest $request, DisponibiliteService $service, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('create', Disponibilite::class);

        $data = $this->hydrate($request);
        $service->assertSansChevauchement($data['user_id'], $data['jour'], $data['heure_debut'], $data['heure_fin']);

        $disponibilite = Disponibilite::create($data);
        $audit->record('disponibilite.created', $disponibilite, $request->user(), $request->validated());

        if ($data['statut'] === Disponibilite::STATUT_EN_ATTENTE) {
            $notifications->broadcast('Disponibilité en attente', 'Un enseignant a soumis une disponibilité à valider.', ['admin', 'decanat']);
        }

        return redirect()->route('disponibilites.index')->with('success', 'Disponibilité enregistrée.');
    }

    public function edit(Disponibilite $disponibilite)
    {
        $this->authorize('update', $disponibilite);

        return view('disponibilites.form', [
            'disponibilite' => $disponibilite,
            'enseignants' => $this->enseignantsList(),
            'semestres' => Semestre::orderByDesc('id')->get()->pluck('libelle', 'id'),
            'annees' => \App\Models\AnneeAcademique::orderByDesc('libelle')->pluck('libelle', 'id'),
        ]);
    }

    public function update(DisponibiliteRequest $request, Disponibilite $disponibilite, DisponibiliteService $service, AuditService $audit)
    {
        $this->authorize('update', $disponibilite);

        $data = $this->hydrate($request);
        $service->assertSansChevauchement($data['user_id'], $data['jour'], $data['heure_debut'], $data['heure_fin'], $disponibilite->id);

        $disponibilite->update($data);
        $audit->record('disponibilite.updated', $disponibilite, $request->user(), $request->validated());

        return redirect()->route('disponibilites.index')->with('success', 'Disponibilité mise à jour.');
    }

    public function updateStatus(Request $request, Disponibilite $disponibilite, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('valider', Disponibilite::class);

        $request->validate([
            'statut' => ['required', Rule::in([Disponibilite::STATUT_VALIDEE, Disponibilite::STATUT_REFUSEE, Disponibilite::STATUT_EN_ATTENTE])],
        ]);

        $statut = $request->input('statut');
        $disponibilite->update(['statut' => $statut]);
        $audit->record('disponibilite.statut', $disponibilite, $request->user(), ['statut' => $statut]);

        $label = $disponibilite->statutLabel();
        $notifications->notifyUser($disponibilite->user, 'Disponibilité ' . mb_strtolower($label), "Votre disponibilité du {$disponibilite->jour} a été mise à jour : {$label}.");

        return back()->with('success', 'Disponibilité mise à jour.');
    }

    public function destroy(Disponibilite $disponibilite, AuditService $audit)
    {
        $this->authorize('delete', $disponibilite);
        $audit->record('disponibilite.deleted', $disponibilite, request()->user(), $disponibilite->toArray());
        $disponibilite->delete();

        return back()->with('success', 'Disponibilité supprimée.');
    }

    private function hydrate(DisponibiliteRequest $request): array
    {
        $data = $request->validated();

        if ($request->user()->isEnseignant()) {
            $data['user_id'] = $request->user()->id;
        }

        $data['statut'] = $request->user()->isEnseignant()
            ? Disponibilite::STATUT_EN_ATTENTE
            : ($request->filled('statut') ? $request->input('statut') : Disponibilite::STATUT_VALIDEE);

        return $data;
    }

    private function enseignantsList()
    {
        $query = User::where('role', User::ROLE_ENSEIGNANT)->orderBy('nom');

        if (auth()->user()->isDecanat()) {
            $query->where('faculte_id', auth()->user()->faculte_id);
        }

        return $query->get()->mapWithKeys(fn ($u) => [$u->id => $u->nom_complet]);
    }
}
