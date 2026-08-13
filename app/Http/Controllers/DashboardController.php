<?php

namespace App\Http\Controllers;

use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Disponibilite;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Horaire;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Ue;
use App\Models\User;
use App\Services\HoraireService;

class DashboardController extends Controller
{
    public function index(HoraireService $horaireService)
    {
        $user = auth()->user();

        if (isset($user->is_active) && !$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte est désactivé. Contactez le Décanat.']);
        }

        if ($user->isAdmin()) {
            return view('dashboard.admin', $this->adminData($horaireService));
        }

        if ($user->isDecanat()) {
            return view('dashboard.decanat', $this->decanatData($user, $horaireService));
        }

        if ($user->isEnseignant()) {
            return view('dashboard.enseignant', $this->enseignantData($user, $horaireService));
        }

        return view('dashboard.etudiant', $this->etudiantData($user, $horaireService));
    }

    private function adminData(HoraireService $horaireService): array
    {
        $attributed = DemandeAuditoire::where('statut', DemandeAuditoire::STATUT_ACCEPTEE)->whereNotNull('auditoire_id')->count();

        $stats = [
            ['label' => 'Facultés', 'total' => Faculte::count(), 'icon' => 'bi-building', 'url' => route('facultes.index')],
            ['label' => 'Décanats', 'total' => User::where('role', User::ROLE_DECANAT)->count(), 'icon' => 'bi-briefcase', 'url' => route('decanats.index')],
            ['label' => 'Étudiants', 'total' => User::where('role', User::ROLE_ETUDIANT)->count(), 'icon' => 'bi-people', 'url' => route('users.index', ['role' => 'etudiant'])],
            ['label' => 'Enseignants', 'total' => User::where('role', User::ROLE_ENSEIGNANT)->count(), 'icon' => 'bi-person-workspace', 'url' => route('users.index', ['role' => 'enseignant'])],
            ['label' => 'Bâtiments', 'total' => Batiment::count(), 'icon' => 'bi-buildings', 'url' => route('batiments.index')],
            ['label' => 'Auditoires', 'total' => Auditoire::where('nom', '!=', 'EN-ATTENTE')->count(), 'icon' => 'bi-door-open', 'url' => route('auditoires.index')],
            ['label' => 'Demandes en attente', 'total' => DemandeAuditoire::enAttente()->count(), 'icon' => 'bi-inbox', 'url' => route('demandes.index', ['statut' => 'pending'])],
            ['label' => 'Salles attribuées', 'total' => $attributed, 'icon' => 'bi-check2-square', 'url' => route('attributions.index', ['statut' => 'acceptee'])],
            ['label' => 'Horaires', 'total' => Horaire::where('statut', '!=', Horaire::STATUT_ANNULE)->count(), 'icon' => 'bi-calendar-week', 'url' => route('horaires.index')],
            ['label' => 'Conflits', 'total' => $horaireService->conflictCount(), 'icon' => 'bi-exclamation-triangle', 'url' => route('rapports.index')],
        ];

        $demandes = DemandeAuditoire::with(['cours.ec', 'promotion', 'enseignant', 'createur.faculte'])
            ->latest()
            ->take(8)
            ->get();

        $sallesParBatiment = Auditoire::with('batiment')->where('nom', '!=', 'EN-ATTENTE')->get()
            ->groupBy(fn ($a) => optional($a->batiment)->nom ?? 'Sans bâtiment')
            ->map->count();

        $coursParJour = $horaireService->chartByDay();
        $horairesRecents = Horaire::with(['ec', 'cours.ec', 'enseignant', 'promotion', 'auditoire'])
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->latest('date')
            ->take(6)
            ->get();

        return compact('stats', 'demandes', 'sallesParBatiment', 'coursParJour', 'horairesRecents');
    }

    private function decanatData(User $user, HoraireService $horaireService): array
    {
        $faculteId = $user->faculte_id;

        $stats = [
            ['label' => 'Étudiants', 'total' => User::where('role', User::ROLE_ETUDIANT)->where('faculte_id', $faculteId)->count(), 'icon' => 'bi-people', 'url' => route('decanat.etudiants.index')],
            ['label' => 'Enseignants', 'total' => User::where('role', User::ROLE_ENSEIGNANT)->where('faculte_id', $faculteId)->count(), 'icon' => 'bi-person-workspace', 'url' => route('decanat.enseignants.index')],
            ['label' => 'Domaines', 'total' => Domaine::where('faculte_id', $faculteId)->count(), 'icon' => 'bi-diagram-3', 'url' => route('decanat.domaines.index')],
            ['label' => 'Filières', 'total' => Filiere::whereHas('domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-share', 'url' => route('decanat.filieres.index')],
            ['label' => 'Mentions', 'total' => Mention::whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-bookmark', 'url' => route('decanat.mentions.index')],
            ['label' => 'Promotions', 'total' => Promotion::whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-layers', 'url' => route('decanat.promotions.index')],
            ['label' => 'UE', 'total' => Ue::whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-journal-text', 'url' => route('decanat.ues.index')],
            ['label' => 'EC', 'total' => Ec::whereHas('ue.promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-list-nested', 'url' => route('decanat.ecs.index')],
            ['label' => 'Horaires', 'total' => Horaire::whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-calendar-week', 'url' => route('decanat.horaires.index')],
            ['label' => 'Demandes', 'total' => DemandeAuditoire::where('created_by', $user->id)->count(), 'icon' => 'bi-send', 'url' => route('decanat.demandes-salles.index')],
        ];

        $demandes = DemandeAuditoire::with(['cours.ec', 'promotion', 'enseignant'])
            ->where(function ($q) use ($user, $faculteId) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('promotion.mention.filiere.domaine', fn ($sub) => $sub->where('faculte_id', $faculteId));
            })
            ->latest()
            ->take(8)
            ->get();

        $enseignants = User::where('role', User::ROLE_ENSEIGNANT)
            ->where('faculte_id', $faculteId)
            ->withCount('disponibilites')
            ->take(8)
            ->get();

        $horaires = Horaire::with(['ec', 'cours.ec', 'enseignant', 'promotion', 'auditoire'])
            ->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->get();

        $grille = $horaireService->weeklyGrid($horaires);

        return compact('stats', 'demandes', 'enseignants', 'grille');
    }

    private function enseignantData(User $user, HoraireService $horaireService): array
    {
        $horaires = Horaire::with(['cours.ec', 'ec', 'auditoire.batiment', 'promotion'])
            ->where('enseignant_id', $user->id)
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get();

        $prochain = $horaires->filter(fn ($h) => $h->date && $h->date->toDateString() >= now()->toDateString())
            ->sortBy(fn ($h) => $h->date->format('Y-m-d') . $h->heure_debut)
            ->first();

        $disponibilites = $user->disponibilites()->latest()->take(8)->get();
        $ecs = $user->ecs()->with('ue')->get();
        $grille = $horaireService->weeklyGrid($horaires);

        return compact('horaires', 'prochain', 'disponibilites', 'ecs', 'grille');
    }

    private function etudiantData(User $user, HoraireService $horaireService): array
    {
        $horaires = Horaire::with(['cours.ec', 'ec', 'auditoire.batiment', 'enseignant'])
            ->where('promotion_id', $user->promotion_id)
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->get();

        $prochain = $horaires->filter(fn ($h) => $h->date && $h->date->toDateString() >= now()->toDateString())
            ->sortBy(fn ($h) => $h->date->format('Y-m-d') . $h->heure_debut)
            ->first();

        $cours = Cours::with(['ec.ue', 'enseignant'])
            ->where('promotion_id', $user->promotion_id)
            ->get();

        $grille = $horaireService->weeklyGrid($horaires);
        $parJour = $horaires->groupBy(fn ($h) => $h->jour);

        return compact('horaires', 'prochain', 'cours', 'grille', 'parJour');
    }
}
