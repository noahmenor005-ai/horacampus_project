<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Http\Requests\HoraireRequest;
use App\Models\Cours;
use App\Models\Horaire;
use App\Models\Semestre;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HoraireService;
use App\Services\NotificationService;
use App\Services\PdfService;
use App\Services\SalleService;
use Illuminate\Http\Request;

class HoraireController extends Controller
{
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

    public function create()
    {
        $this->authorize('create', Horaire::class);

        return view('horaires.form', array_merge(['horaire' => new Horaire()], $this->formData()));
    }

    public function store(HoraireRequest $request, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('create', Horaire::class);

        $horaire = $this->horaires->create($this->hydrate($request));
        $notifications->broadcast('Nouveau cours programmé', 'Un nouveau créneau horaire a été programmé sans conflit.', ['admin', 'decanat']);
        $audit->record('horaire.created', $horaire, $request->user(), $request->validated());

        return redirect()->route('horaires.index')->with('success', 'Horaire programmé sans conflit.');
    }

    public function edit(Horaire $horaire)
    {
        $this->authorize('update', $horaire);

        return view('horaires.form', array_merge(compact('horaire'), $this->formData()));
    }

    public function update(HoraireRequest $request, Horaire $horaire, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('update', $horaire);

        $this->horaires->update($horaire, $this->hydrate($request, $horaire));
        $notifications->broadcast('Horaire modifié', 'Un horaire a été mis à jour.', ['admin', 'decanat']);
        $audit->record('horaire.updated', $horaire, $request->user(), $request->validated());

        return redirect()->route('horaires.index')->with('success', 'Horaire mis à jour.');
    }

    public function destroy(Horaire $horaire, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('delete', $horaire);
        $audit->record('horaire.deleted', $horaire, request()->user(), $horaire->toArray());
        $horaire->delete();
        $notifications->broadcast('Horaire supprimé', 'Un horaire a été supprimé.', ['admin', 'decanat']);

        return back()->with('success', 'Horaire supprimé.');
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
                    optional($horaire->cours)->intitule,
                    optional($horaire->auditoire)->nom,
                    optional($horaire->enseignant)->nom_complet,
                    optional($horaire->promotion)->nom,
                    optional($horaire->semestre)->nom,
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

        $cours = $horaire?->cours ?? Cours::find($data['cours_id']);
        if ($cours) {
            $data['enseignant_id'] = $cours->enseignant_id;
            $data['promotion_id'] = $cours->promotion_id;
            $data['effectif_attendu'] = $data['effectif_attendu'] ?? $cours->promotion->effectif;
        }

        return $data;
    }

    private function formData(): array
    {
        $coursQuery = Cours::with(['ec.ue', 'promotion.mention', 'enseignant']);

        if (auth()->user()->isDecanat()) {
            $coursQuery->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }

        $enseignantsQuery = User::where('role', User::ROLE_ENSEIGNANT)->orderBy('nom');
        if (auth()->user()->isDecanat()) {
            $enseignantsQuery->where('faculte_id', auth()->user()->faculte_id);
        }

        $promotionsQuery = \App\Models\Promotion::with('mention.filiere.domaine');
        if (auth()->user()->isDecanat()) {
            $promotionsQuery->whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }

        return [
            'cours' => $coursQuery->get()->mapWithKeys(fn ($c) => [$c->id => $c->intitule . ' — ' . $c->promotion->nom . ' (' . $c->enseignant->nom_complet . ')']),
            'auditoires' => \App\Models\Auditoire::where('disponibilite', true)->orderBy('nom')->get()->mapWithKeys(fn ($a) => [$a->id => $a->nom . ' (' . $a->capacite . ' places) — ' . $a->batiment->nom]),
            'enseignants' => $enseignantsQuery->get()->mapWithKeys(fn ($u) => [$u->id => $u->nom_complet]),
            'promotions' => $promotionsQuery->get()->pluck('nom', 'id'),
            'semestres' => Semestre::orderByDesc('id')->get()->pluck('libelle', 'id'),
            'jours' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            'vues' => ['jour' => 'Journalière', 'semaine' => 'Hebdomadaire', 'mois' => 'Mensuelle'],
        ];
    }
}
