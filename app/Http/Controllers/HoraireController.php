<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Http\Requests\HoraireRequest;
use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Horaire;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HoraireService;
use App\Services\NotificationService;
use App\Services\PdfService;
use App\Support\FacultyGuard;
use Illuminate\Http\Request;

class HoraireController extends Controller
{
    use ScopesFaculty;

    private HoraireService $horaires;

    public function __construct(HoraireService $horaires)
    {
        $this->horaires = $horaires;
    }

    public function index(Request $request)
    {
        $horaires = $this->horaires->filteredQuery($request)->paginate(15)->withQueryString();

        return view('horaires.index', array_merge(compact('horaires'), $this->formData()));
    }

    public function show(Horaire $horaire)
    {
        $user = auth()->user();
        if ($user && $user->isEtudiant() && (int) $horaire->promotion_id !== (int) $user->promotion_id) {
            abort(403);
        }
        if ($user && $user->isEnseignant() && (int) $horaire->enseignant_id !== (int) $user->id) {
            abort(403);
        }
        FacultyGuard::assert($horaire);
        $horaire->load([
            'cours.ec.ue',
            'ec.ue',
            'ue',
            'auditoire.batiment',
            'enseignant',
            'promotion.mention.filiere.domaine',
            'semestre.anneeAcademique',
            'domaine',
            'filiere',
            'mention',
            'anneeAcademique',
            'demandes',
        ]);

        return view('horaires.show', compact('horaire'));
    }

    public function create()
    {
        $this->authorize('create', Horaire::class);

        return view('horaires.form', array_merge(['horaire' => new Horaire(['statut' => Horaire::STATUT_VALIDE])], $this->formData()));
    }

    public function store(HoraireRequest $request, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('create', Horaire::class);

        $horaire = $this->horaires->create($this->hydrate($request));
        $notifications->broadcast('Nouveau cours programmé', 'Un nouveau créneau horaire a été programmé.', ['admin', 'decanat']);
        $audit->record('horaire.created', $horaire, $request->user(), $request->validated());

        $route = $request->user()->isDecanat() ? 'decanat.horaires.show' : 'horaires.show';

        return redirect()->route($route, $horaire)->with('success', 'Horaire programmé. Vous pouvez maintenant demander une salle.');
    }

    public function edit(Horaire $horaire)
    {
        $this->authorize('update', $horaire);
        FacultyGuard::assert($horaire);

        return view('horaires.form', array_merge(compact('horaire'), $this->formData()));
    }

    public function update(HoraireRequest $request, Horaire $horaire, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('update', $horaire);
        FacultyGuard::assert($horaire);

        $this->horaires->update($horaire, $this->hydrate($request, $horaire));
        $notifications->broadcast('Horaire modifié', 'Un horaire a été mis à jour.', ['admin', 'decanat']);
        $audit->record('horaire.updated', $horaire, $request->user(), $request->validated());

        $route = $request->user()->isDecanat() ? 'decanat.horaires.index' : 'horaires.index';

        return redirect()->route($route)->with('success', 'Horaire mis à jour.');
    }

    public function destroy(Horaire $horaire, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('delete', $horaire);
        FacultyGuard::assert($horaire);
        $audit->record('horaire.deleted', $horaire, request()->user(), $horaire->toArray());
        $horaire->delete();
        $notifications->broadcast('Horaire supprimé', 'Un horaire a été supprimé.', ['admin', 'decanat']);

        return back()->with('success', 'Horaire supprimé.');
    }

    public function demanderSalle(Request $request, Horaire $horaire, AuditService $audit, NotificationService $notifications)
    {
        $this->authorize('create', DemandeAuditoire::class);
        FacultyGuard::assert($horaire);

        if ($horaire->demandeEnAttente()) {
            return back()->with('error', 'Une demande de salle est déjà en attente pour cet horaire.');
        }

        $request->validate([
            'commentaire' => ['nullable', 'string', 'max:500'],
            'effectif_attendu' => ['nullable', 'integer', 'min:1'],
        ]);

        $cours = $horaire->cours;
        if (!$cours && $horaire->ec_id) {
            $cours = $this->findOrCreateCours($horaire->ec_id, $horaire->enseignant_id, $horaire->promotion_id);
            $horaire->update(['cours_id' => $cours->id]);
        }

        if (!$cours) {
            return back()->with('error', 'Impossible de créer la demande : aucun EC/cours n\'est lié à cet horaire.');
        }

        $effectif = $request->input('effectif_attendu')
            ?: $horaire->effectif_attendu
            ?: optional($horaire->promotion)->effectif
            ?: 1;

        $demande = DemandeAuditoire::create([
            'cours_id' => $cours->id,
            'enseignant_id' => $horaire->enseignant_id,
            'promotion_id' => $horaire->promotion_id,
            'semestre_id' => $horaire->semestre_id,
            'horaire_id' => $horaire->id,
            'ec_id' => $horaire->ec_id ?: optional($cours)->ec_id,
            'created_by' => auth()->id(),
            'date' => $horaire->date->format('Y-m-d'),
            'heure_debut' => substr($horaire->heure_debut, 0, 5),
            'heure_fin' => substr($horaire->heure_fin, 0, 5),
            'effectif_attendu' => $effectif,
            'statut' => DemandeAuditoire::STATUT_PENDING,
            'commentaire' => $request->input('commentaire'),
            'note' => $request->input('commentaire'),
        ]);

        $horaire->update(['source_demande_id' => $demande->id]);
        $audit->record('demande.created_from_horaire', $demande, $request->user(), ['horaire_id' => $horaire->id]);
        $notifications->broadcast('Nouvelle demande de salle', 'Une demande de salle a été soumise pour le ' . $demande->date->format('d/m/Y') . '.', ['admin']);

        $route = $request->user()->isDecanat() ? 'decanat.demandes-salles.show' : 'demandes.show';

        return redirect()->route($route, $demande)->with('success', 'Demande de salle envoyée à l\'administration (statut : en attente).');
    }

    public function print(Request $request)
    {
        return view('horaires.print', ['horaires' => $this->horaires->filteredQuery($request)->get()]);
    }

    public function export(Request $request)
    {
        $rows = $this->horaires->filteredQuery($request)->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Jour', 'Date', 'Début', 'Fin', 'Cours', 'Auditoire', 'Enseignant', 'Promotion', 'Semestre']);

            foreach ($rows as $horaire) {
                fputcsv($out, [
                    $horaire->jour,
                    $horaire->date?->format('d/m/Y'),
                    substr($horaire->heure_debut, 0, 5),
                    substr($horaire->heure_fin, 0, 5),
                    optional($horaire->cours)->intitule ?: optional($horaire->ec)->nom,
                    optional($horaire->auditoire)->nom,
                    optional($horaire->enseignant)->nom_complet,
                    optional($horaire->promotion)->nom,
                    optional($horaire->semestre)->libelle,
                ]);
            }

            fclose($out);
        }, 'horaires.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request, PdfService $pdf)
    {
        $horaires = $this->horaires->filteredQuery($request)->get();

        return $pdf->render('horaires.pdf', [
            'horaires' => $horaires,
            'title' => 'Planning des horaires',
            'orientation' => 'landscape',
        ], 'horaires-' . now()->format('Y-m-d'));
    }

    private function hydrate(HoraireRequest $request, ?Horaire $horaire = null): array
    {
        $data = $request->validated();

        if (empty($data['cours_id']) && !empty($data['ec_id'])) {
            $enseignantId = $data['enseignant_id'] ?? $horaire->enseignant_id ?? null;
            $promotionId = $data['promotion_id'] ?? $horaire->promotion_id ?? null;
            $cours = $this->findOrCreateCours((int) $data['ec_id'], $enseignantId, $promotionId);
            $data['cours_id'] = $cours->id;
            $data['enseignant_id'] = $data['enseignant_id'] ?? $cours->enseignant_id;
            $data['promotion_id'] = $data['promotion_id'] ?? $cours->promotion_id;
        }

        $cours = $horaire?->cours ?? (!empty($data['cours_id']) ? Cours::with('promotion', 'ec.ue')->find($data['cours_id']) : null);

        if ($cours) {
            $data['enseignant_id'] = $data['enseignant_id'] ?? $cours->enseignant_id;
            $data['promotion_id'] = $data['promotion_id'] ?? $cours->promotion_id;
            $data['ec_id'] = $data['ec_id'] ?? $cours->ec_id;
            $data['ue_id'] = $data['ue_id'] ?? optional($cours->ec)->ue_id;
            $data['effectif_attendu'] = $data['effectif_attendu'] ?? optional($cours->promotion)->effectif;
        }

        if (!empty($data['promotion_id'])) {
            $promotion = Promotion::with('mention.filiere.domaine')->find($data['promotion_id']);
            if ($promotion) {
                $data['mention_id'] = $data['mention_id'] ?? $promotion->mention_id;
                $data['filiere_id'] = $data['filiere_id'] ?? optional($promotion->mention)->filiere_id;
                $data['domaine_id'] = $data['domaine_id'] ?? optional(optional($promotion->mention)->filiere)->domaine_id;
                $data['annee_academique_id'] = $data['annee_academique_id'] ?? $promotion->annee_academique_id;
                $data['effectif_attendu'] = $data['effectif_attendu'] ?? $promotion->effectif;
            }
        }

        if (empty($data['auditoire_id'])) {
            $placeholder = $this->horaires->placeholderAuditoire();
            if ($placeholder) {
                $data['auditoire_id'] = $placeholder->id;
            }
        }

        if (!empty($data['date'])) {
            $data['jour'] = $data['jour'] ?? Horaire::jourFr($data['date']);
        }

        if ($this->isScoped() && !empty($data['promotion_id'])) {
            $promotionCheck = Promotion::with('mention.filiere.domaine')->find($data['promotion_id']);
            FacultyGuard::assert($promotionCheck);
        }

        return $data;
    }

    private function findOrCreateCours(int $ecId, ?int $enseignantId, ?int $promotionId): Cours
    {
        $existing = Cours::where('ec_id', $ecId)
            ->when($promotionId, fn ($q) => $q->where('promotion_id', $promotionId))
            ->first();

        if ($existing) {
            if ($enseignantId && !$existing->enseignant_id) {
                $existing->update(['enseignant_id' => $enseignantId]);
            }
            return $existing;
        }

        $ec = Ec::with('ue')->findOrFail($ecId);
        $promotionId = $promotionId ?: optional($ec->ue)->promotion_id;
        $enseignantId = $enseignantId ?: $ec->enseignant_id;

        abort_unless($promotionId && $enseignantId, 422, 'Impossible de créer le cours : promotion et enseignant requis.');

        return Cours::create([
            'ec_id' => $ecId,
            'enseignant_id' => $enseignantId,
            'promotion_id' => $promotionId,
            'type' => 'CM',
            'volume_horaire' => $ec->volume_horaire ?: 0,
        ]);
    }

    private function formData(): array
    {
        $coursQuery = Cours::with(['ec.ue', 'promotion.mention', 'enseignant']);
        if (auth()->user() && auth()->user()->isDecanat()) {
            $coursQuery->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }

        $enseignantsQuery = User::where('role', User::ROLE_ENSEIGNANT)->orderBy('nom');
        if (auth()->user() && auth()->user()->isDecanat()) {
            $enseignantsQuery->where('faculte_id', auth()->user()->faculte_id);
        }

        $promotionsQuery = Promotion::with('mention.filiere.domaine');
        if (auth()->user() && auth()->user()->isDecanat()) {
            $promotionsQuery->whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }

        $auditoires = Auditoire::with('batiment')->where(function ($q) {
            $q->where('disponibilite', true)->orWhere('nom', 'EN-ATTENTE');
        })->orderBy('nom')->get()->filter(fn ($a) => $a->nom !== 'EN-ATTENTE');

        return [
            'cours' => $coursQuery->get()->mapWithKeys(fn ($c) => [$c->id => $c->intitule . ' — ' . optional($c->promotion)->nom . ' (' . optional($c->enseignant)->nom_complet . ')']),
            'auditoires' => $auditoires->mapWithKeys(fn ($a) => [$a->id => $a->nom . ' (' . $a->capacite . ' places)' . (optional($a->batiment)->nom ? ' — ' . $a->batiment->nom : '')]),
            'enseignants' => $enseignantsQuery->get(),
            'promotions' => $promotionsQuery->get(),
            'domaines' => $this->scopeDomaines()->orderBy('nom')->get(),
            'filieres' => $this->scopeFilieres()->orderBy('nom')->get(),
            'mentions' => $this->scopeMentions()->orderBy('nom')->get(),
            'ues' => $this->scopeUes()->orderBy('nom')->get(),
            'ecs' => $this->scopeEcs()->orderBy('nom')->get(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
            'semestres' => Semestre::orderByDesc('id')->get(),
            'jours' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            'vues' => ['jour' => 'Journalière', 'semaine' => 'Hebdomadaire', 'mois' => 'Mensuelle'],
        ];
    }
}
