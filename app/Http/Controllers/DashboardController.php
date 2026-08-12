<?php

namespace App\Http\Controllers;

use App\Models\Auditoire;
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

        // Sécurité : vérifier is_active
        if (isset($user->is_active) && !$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte est désactivé. Contactez le Décanat.']);
        }

        if ($user->isAdmin()) {
            return view('dashboard.admin', $this->adminData($horaireService));
        }

        if ($user->isDecanat()) {
            return view('dashboard.decanat', $this->decanatData($user));
        }

        if ($user->isEnseignant()) {
            return view('dashboard.enseignant', $this->enseignantData($user));
        }

        return view('dashboard.etudiant', $this->etudiantData($user));
    }

    private function adminData(HoraireService $horaireService): array
    {
        $stats = [
            ['label' => 'Étudiants', 'total' => User::where('role', User::ROLE_ETUDIANT)->count(), 'icon' => 'bi-people'],
            ['label' => 'Enseignants', 'total' => User::where('role', User::ROLE_ENSEIGNANT)->count(), 'icon' => 'bi-person-workspace'],
            ['label' => 'Décanats', 'total' => User::where('role', User::ROLE_DECANAT)->count(), 'icon' => 'bi-briefcase'],
            ['label' => 'Facultés', 'total' => Faculte::count(), 'icon' => 'bi-building'],
            ['label' => 'Salles', 'total' => Auditoire::count(), 'icon' => 'bi-door-open'],
            ['label' => 'Salles occupées', 'total' => Auditoire::where('etat', 'occupee')->count(), 'icon' => 'bi-lock'],
            ['label' => 'Demandes en attente', 'total' => DemandeAuditoire::where('statut', DemandeAuditoire::STATUT_EN_ATTENTE)->count(), 'icon' => 'bi-inbox'],
            ['label' => 'Conflits', 'total' => $horaireService->conflictCount(), 'icon' => 'bi-exclamation-triangle'],
        ];

        $demandes = DemandeAuditoire::with(['cours.ec', 'promotion', 'enseignant'])
            ->latest()
            ->take(8)
            ->get();

        $sallesParBatiment = Auditoire::with('batiment')->get()
            ->groupBy(fn ($a) => optional($a->batiment)->nom ?? 'Sans bâtiment')
            ->map->count();

        $coursParJour = $horaireService->chartByDay();

        return compact('stats', 'demandes', 'sallesParBatiment', 'coursParJour');
    }

    private function decanatData(User $user): array
    {
        $faculteId = $user->faculte_id;

        $stats = [
            ['label' => 'Enseignants (ma faculté)', 'total' => User::where('role', User::ROLE_ENSEIGNANT)->where('faculte_id', $faculteId)->count(), 'icon' => 'bi-person-workspace'],
            ['label' => 'Étudiants (ma faculté)', 'total' => User::where('role', User::ROLE_ETUDIANT)->where('faculte_id', $faculteId)->count(), 'icon' => 'bi-people'],
            ['label' => 'Horaires', 'total' => Horaire::whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-calendar-week'],
            ['label' => 'Demandes envoyées', 'total' => DemandeAuditoire::where('created_by', $user->id)->count(), 'icon' => 'bi-send'],
            ['label' => 'Demandes acceptées', 'total' => DemandeAuditoire::where('created_by', $user->id)->where('statut', DemandeAuditoire::STATUT_ACCEPTEE)->count(), 'icon' => 'bi-check2-circle'],
            ['label' => 'Disponibilités', 'total' => Disponibilite::whereHas('user', fn ($q) => $q->where('faculte_id', $faculteId))->count(), 'icon' => 'bi-clock-history'],
        ];

        $demandes = DemandeAuditoire::with(['cours.ec', 'promotion', 'enseignant'])
            ->where('created_by', $user->id)
            ->latest()
            ->take(8)
            ->get();

        $enseignants = User::where('role', User::ROLE_ENSEIGNANT)->where('faculte_id', $faculteId)->withCount('disponibilites')->take(8)->get();

        return compact('stats', 'demandes', 'enseignants');
    }

    private function enseignantData(User $user): array
    {
        $aujourdhui = now()->toDateString();
        $finSemaine = now()->addDays(6)->toDateString();

        $horairesSemaine = Horaire::with(['cours.ec', 'auditoire.batiment', 'promotion'])
            ->where('enseignant_id', $user->id)
            ->whereBetween('date', [$aujourdhui, $finSemaine])
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->take(12)
            ->get();

        $prochain = Horaire::with(['cours.ec', 'auditoire.batiment', 'promotion'])
            ->where('enseignant_id', $user->id)
            ->where('date', '>=', $aujourdhui)
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->first();

        $disponibilites = $user->disponibilites()->latest()->take(6)->get();

        $ecs = $user->ecs()->with('ue')->get();

        return compact('horairesSemaine', 'prochain', 'disponibilites', 'ecs');
    }

    private function etudiantData(User $user): array
    {
        // L'étudiant ne peut voir que ses propres données ; toute tentative de modification est bloquée côté serveur
        $aujourdhui = now()->toDateString();
        $finSemaine = now()->addDays(6)->toDateString();

        $horairesSemaine = Horaire::with(['cours.ec', 'auditoire.batiment', 'enseignant'])
            ->where('promotion_id', $user->promotion_id)
            ->whereBetween('date', [$aujourdhui, $finSemaine])
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->take(12)
            ->get();

        $prochain = Horaire::with(['cours.ec', 'auditoire.batiment', 'enseignant'])
            ->where('promotion_id', $user->promotion_id)
            ->where('date', '>=', $aujourdhui)
            ->orderBy('date')
            ->orderBy('heure_debut')
            ->first();

        $cours = Cours::with(['ec.ue', 'enseignant'])
            ->where('promotion_id', $user->promotion_id)
            ->get();

        return compact('horairesSemaine', 'prochain', 'cours');
    }
}
