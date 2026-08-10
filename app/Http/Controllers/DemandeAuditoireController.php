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
            $query->where('created_by', $user->id);
        }

        if ($user->isEnseignant()) {
            $query->where('enseignant_id', $user->id);
        }

        if ($user->isEtudiant()) {
            $query->where('promotion_id', $user->promotion_id);
        }

        foreach (['statut', 'promotion_id', 'enseignant_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        $demandes = $query->paginate(12)->withQueryString();

        return view('demandes.index', [
            'demandes' => $demandes,
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', DemandeAuditoire::class);

        return view('demandes.form', [
            'demande' => new DemandeAuditoire(),
            'cours' => $this->coursScopes(),
            'semestres' => Semestre::orderByDesc('id')->get(),
        ]);
    }

    public function store(DemandeAuditoireRequest $request, AuditService $audit, NotificationService $notifications)
    {
        $this->authorize('create', DemandeAuditoire::class);

        $data = $this->resolveFromCours($request->validated());

        $demande = DemandeAuditoire::create($data);
        $audit->record('demande.created', $demande, $request->user(), $request->validated());
        $notifications->broadcast('Nouvelle demande de salle', "Une demande de salle a été soumise pour le " . $demande->date->format('d/m/Y') . '.', ['admin']);

        return redirect()->route('demandes.index')->with('success', 'Demande de salle envoyée à l\'administration.');
    }

    public function show(DemandeAuditoire $demande, SalleService $salleService)
    {
        $demande->load(['cours.ec.ue', 'promotion.mention', 'enseignant', 'auditoire.batiment', 'semestre', 'createur']);

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

            $horaire = $horaires->create([
                'cours_id' => $demande->cours_id,
                'auditoire_id' => $auditoireId,
                'enseignant_id' => $demande->enseignant_id,
                'promotion_id' => $demande->promotion_id,
                'semestre_id' => $demande->semestre_id,
                'source_demande_id' => $demande->id,
                'date' => $demande->date->format('Y-m-d'),
                'heure_debut' => $demande->heure_debut,
                'heure_fin' => $demande->heure_fin,
                'statut' => Horaire::STATUT_VALIDE,
            ]);

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

        if (!$cours && isset($data['cours_id'])) {
            $cours = Cours::with(['promotion'])->find($data['cours_id']);
        }

        $data['enseignant_id'] = $cours->enseignant_id;
        $data['promotion_id'] = $cours->promotion_id;
        $data['created_by'] = auth()->id();
        $data['statut'] = DemandeAuditoire::STATUT_EN_ATTENTE;
        $data['auditoire_id'] = null;

        return $data;
    }
}
