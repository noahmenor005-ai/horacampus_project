<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Http\Requests\DemandeAuditoireRequest;
use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Horaire;
use App\Models\Semestre;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HoraireService;
use App\Services\NotificationService;
use App\Services\SalleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DemandeAuditoireController extends Controller
{
    use ScopesFaculty;

    public function index(Request $request)
    {
        $query = DemandeAuditoire::with(['cours.ec', 'promotion.mention', 'enseignant', 'auditoire'])->latest();

        $user = $request->user();
        if ($user->isDecanat()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('promotion.mention.filiere.domaine', fn ($sub) => $sub->where('faculte_id', $user->faculte_id));
            });
        }

        if ($user->isEnseignant()) {
            $query->where('enseignant_id', $user->id);
        }

        if ($user->isEtudiant()) {
            $query->where('promotion_id', $user->promotion_id);
        }

        foreach (['promotion_id', 'enseignant_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('statut')) {
            $statut = $request->input('statut');
            if (in_array($statut, [DemandeAuditoire::STATUT_PENDING, DemandeAuditoire::STATUT_EN_ATTENTE], true)) {
                $query->enAttente();
            } else {
                $query->where('statut', $statut);
            }
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('cours.ec', fn ($ec) => $ec->where('nom', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('enseignant', fn ($e) => $e->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))
                    ->orWhereHas('promotion', fn ($p) => $p->where('nom', 'like', $term));
            });
        }

        $demandes = $query->paginate(12)->withQueryString();

        return view('demandes.index', [
            'demandes' => $demandes,
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
            'enseignants' => $this->scopeEnseignants()->get()->mapWithKeys(fn ($u) => [$u->id => $u->nom_complet]),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', DemandeAuditoire::class);

        $demande = new DemandeAuditoire(['statut' => DemandeAuditoire::STATUT_PENDING]);

        if ($request->filled('horaire_id')) {
            $horaire = Horaire::with(['cours', 'promotion', 'enseignant'])->find($request->input('horaire_id'));
            if ($horaire) {
                \App\Support\FacultyGuard::assert($horaire);
                $demande->horaire_id = $horaire->id;
                $demande->cours_id = $horaire->cours_id;
                $demande->ec_id = $horaire->ec_id;
                $demande->enseignant_id = $horaire->enseignant_id;
                $demande->promotion_id = $horaire->promotion_id;
                $demande->semestre_id = $horaire->semestre_id;
                $demande->date = $horaire->date;
                $demande->heure_debut = $horaire->heure_debut;
                $demande->heure_fin = $horaire->heure_fin;
                $demande->effectif_attendu = $horaire->effectif_attendu ?: optional($horaire->promotion)->effectif;
            }
        }

        return view('demandes.form', [
            'demande' => $demande,
            'cours' => $this->coursScopes(),
            'semestres' => Semestre::orderByDesc('id')->get(),
        ]);
    }

    public function store(DemandeAuditoireRequest $request, AuditService $audit, NotificationService $notifications)
    {
        $this->authorize('create', DemandeAuditoire::class);

        $data = $this->resolveFromCours($request->validated());

        if (empty($data['cours_id']) || empty($data['enseignant_id']) || empty($data['promotion_id'])) {
            throw ValidationException::withMessages([
                'cours_id' => 'La demande doit être liée à un cours, un enseignant et une promotion.',
            ]);
        }

        $demande = DemandeAuditoire::create($data);
        $audit->record('demande.created', $demande, $request->user(), $request->validated());
        $notifications->broadcast('Nouvelle demande de salle', "Une demande de salle a été soumise pour le " . $demande->date->format('d/m/Y') . '.', ['admin']);

        $indexRoute = $request->user()->isDecanat() ? 'decanat.demandes-salles.index' : 'demandes.index';

        return redirect()->route($indexRoute)->with('success', 'Demande de salle envoyée à l\'administration (statut : en attente).');
    }

    public function show(DemandeAuditoire $demande, SalleService $salleService)
    {
        \App\Support\FacultyGuard::assert($demande);
        $demande->load(['cours.ec.ue', 'promotion.mention', 'enseignant', 'auditoire.batiment', 'semestre', 'createur', 'horaire', 'ec']);

        $sallesDisponibles = auth()->user()->isAdmin()
            ? $salleService->suggerer($demande)
            : collect();

        return view('demandes.show', compact('demande', 'sallesDisponibles'));
    }

    public function edit(DemandeAuditoire $demande)
    {
        $this->authorize('update', $demande);

        return view('demandes.form', [
            'demande' => $demande,
            'cours' => $this->coursScopes(),
            'semestres' => Semestre::orderByDesc('id')->get(),
        ]);
    }

    public function update(DemandeAuditoireRequest $request, DemandeAuditoire $demande, AuditService $audit, SalleService $salleService)
    {
        $this->authorize('update', $demande);

        $data = $this->resolveFromCours($request->validated(), $demande);

        if (auth()->user()->isAdmin() && $request->filled('auditoire_id')) {
            $data['auditoire_id'] = $request->input('auditoire_id');
        } else {
            $data['auditoire_id'] = null;
        }

        $demande->update($data);
        $audit->record('demande.updated', $demande, $request->user(), $request->validated());

        return redirect()->route('demandes.index')->with('success', 'Demande mise à jour.');
    }

    public function destroy(DemandeAuditoire $demande, AuditService $audit)
    {
        $this->authorize('delete', $demande);
        $audit->record('demande.deleted', $demande, request()->user(), $demande->toArray());
        $demande->delete();

        return back()->with('success', 'Demande supprimée.');
    }

    public function updateStatus(Request $request, DemandeAuditoire $demande, HoraireService $horaires, SalleService $salleService, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('attribuerSalle', $demande);

        $request->validate([
            'statut' => ['required', Rule::in([DemandeAuditoire::STATUT_ACCEPTEE, DemandeAuditoire::STATUT_REFUSEE, DemandeAuditoire::STATUT_EN_ATTENTE])],
            'auditoire_id' => ['nullable', 'exists:auditoires,id'],
            'motif_refus' => ['nullable', 'string', 'max:500'],
        ]);

        $statut = $request->input('statut');

        if ($statut === DemandeAuditoire::STATUT_ACCEPTEE) {
            $auditoireId = $request->input('auditoire_id') ?: $demande->auditoire_id;

            if (!$auditoireId) {
                throw ValidationException::withMessages([
                    'auditoire_id' => 'Veuillez attribuer une salle (ou choisir parmi les suggestions).',
                ]);
            }

            $demande->update(['auditoire_id' => $auditoireId, 'statut' => DemandeAuditoire::STATUT_ACCEPTEE, 'motif_refus' => null]);

            if ($demande->horaire_id && $demande->horaire) {
                $horaire = $horaires->update($demande->horaire, [
                    'cours_id' => $demande->cours_id,
                    'auditoire_id' => $auditoireId,
                    'enseignant_id' => $demande->enseignant_id,
                    'promotion_id' => $demande->promotion_id,
                    'semestre_id' => $demande->semestre_id,
                    'source_demande_id' => $demande->id,
                    'ec_id' => $demande->ec_id,
                    'date' => $demande->date->format('Y-m-d'),
                    'heure_debut' => $demande->heure_debut,
                    'heure_fin' => $demande->heure_fin,
                    'effectif_attendu' => $demande->effectif_attendu,
                    'statut' => Horaire::STATUT_VALIDE,
                ]);
            } else {
                $horaire = $horaires->create([
                    'cours_id' => $demande->cours_id,
                    'auditoire_id' => $auditoireId,
                    'enseignant_id' => $demande->enseignant_id,
                    'promotion_id' => $demande->promotion_id,
                    'semestre_id' => $demande->semestre_id,
                    'source_demande_id' => $demande->id,
                    'ec_id' => $demande->ec_id,
                    'date' => $demande->date->format('Y-m-d'),
                    'heure_debut' => $demande->heure_debut,
                    'heure_fin' => $demande->heure_fin,
                    'effectif_attendu' => $demande->effectif_attendu,
                    'statut' => Horaire::STATUT_VALIDE,
                ]);
                $demande->update(['horaire_id' => $horaire->id]);
            }

            $notifications->notifyUser($demande->enseignant, 'Salle attribuée', "Une salle vous a été attribuée le {$demande->date->format('d/m/Y')} de {$demande->heure_debut} à {$demande->heure_fin}.");
            $notifications->notifyUser($demande->createur, 'Demande acceptée', "Votre demande de salle a été acceptée. Salle : {$demande->auditoire->nom}.");
            $audit->record('demande.acceptee', $demande, $request->user(), ['auditoire_id' => $auditoireId, 'horaire_id' => $horaire->id]);

            return back()->with('success', 'Demande acceptée et horaire programmé.');
        }

        if ($statut === DemandeAuditoire::STATUT_REFUSEE) {
            $motif = $request->input('motif_refus');
            if (!$motif) {
                throw ValidationException::withMessages(['motif_refus' => 'Un motif est requis pour refuser une demande.']);
            }

            $demande->update(['statut' => DemandeAuditoire::STATUT_REFUSEE, 'motif_refus' => $motif, 'auditoire_id' => null]);
            $notifications->notifyUser($demande->createur, 'Demande refusée', 'Votre demande de salle a été refusée. Motif : ' . $motif);
            $audit->record('demande.refusee', $demande, $request->user(), ['motif' => $motif]);

            return back()->with('success', 'Demande refusée.');
        }

        $demande->update(['statut' => DemandeAuditoire::STATUT_EN_ATTENTE, 'motif_refus' => null]);
        $audit->record('demande.rouverte', $demande, $request->user(), []);

        return back()->with('success', 'Demande remise en attente.');
    }

    public function sallesDisponibles(DemandeAuditoire $demande, SalleService $salleService)
    {
        $this->authorize('attribuerSalle', $demande);

        return view('demandes.salles', [
            'demande' => $demande,
            'salles' => $salleService->suggerer($demande),
        ]);
    }

    private function coursScopes()
    {
        $query = Cours::with(['ec.ue', 'promotion.mention', 'enseignant']);

        if ($this->isScoped()) {
            $query->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        return $query->orderBy('id')->get()->mapWithKeys(fn ($c) => [
            $c->id => $c->intitule . ' — ' . $c->promotion->nom . ' (' . $c->enseignant->nom_complet . ')',
        ]);
    }

    private function resolveFromCours(array $data, ?DemandeAuditoire $demande = null): array
    {
        $cours = $demande?->cours;

        if (!$cours && !empty($data['cours_id'])) {
            $cours = Cours::with(['promotion'])->find($data['cours_id']);
        }

        if (!$cours && !empty($data['horaire_id'])) {
            $horaire = Horaire::with('cours')->find($data['horaire_id']);
            $cours = $horaire?->cours;
            $data['date'] = $data['date'] ?? optional($horaire?->date)->format('Y-m-d');
            $data['heure_debut'] = $data['heure_debut'] ?? $horaire?->heure_debut;
            $data['heure_fin'] = $data['heure_fin'] ?? $horaire?->heure_fin;
            $data['enseignant_id'] = $data['enseignant_id'] ?? $horaire?->enseignant_id;
            $data['promotion_id'] = $data['promotion_id'] ?? $horaire?->promotion_id;
            $data['ec_id'] = $data['ec_id'] ?? $horaire?->ec_id;
            $data['semestre_id'] = $data['semestre_id'] ?? $horaire?->semestre_id;
            $data['cours_id'] = $data['cours_id'] ?? $horaire?->cours_id;
        }

        if ($cours) {
            $data['enseignant_id'] = $data['enseignant_id'] ?? $cours->enseignant_id;
            $data['promotion_id'] = $data['promotion_id'] ?? $cours->promotion_id;
            $data['ec_id'] = $data['ec_id'] ?? $cours->ec_id;
        }

        $data['created_by'] = auth()->id();
        $data['statut'] = DemandeAuditoire::STATUT_PENDING;
        $data['auditoire_id'] = null;
        $data['commentaire'] = $data['commentaire'] ?? ($data['note'] ?? null);
        $data['note'] = $data['note'] ?? ($data['commentaire'] ?? null);

        return $data;
    }
}
