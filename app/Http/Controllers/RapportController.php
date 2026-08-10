<?php

namespace App\Http\Controllers;

use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Disponibilite;
use App\Models\Faculte;
use App\Models\Horaire;
use App\Models\Promotion;
use App\Models\User;
use App\Services\PdfService;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function index()
    {
        return view('rapports.index', ['stats' => $this->stats()]);
    }

    public function pdf(PdfService $pdf)
    {
        return $pdf->render('rapports.pdf', [
            'stats' => $this->stats(),
            'title' => 'Rapport de gestion — HoraCampus',
            'orientation' => 'landscape',
        ], 'rapport-horacampus-' . now()->format('Y-m-d'));
    }

    private function stats(): array
    {
        $parStatut = function ($query) {
            return [
                DemandeAuditoire::STATUT_EN_ATTENTE => (clone $query)->where('statut', DemandeAuditoire::STATUT_EN_ATTENTE)->count(),
                DemandeAuditoire::STATUT_ACCEPTEE => (clone $query)->where('statut', DemandeAuditoire::STATUT_ACCEPTEE)->count(),
                DemandeAuditoire::STATUT_REFUSEE => (clone $query)->where('statut', DemandeAuditoire::STATUT_REFUSEE)->count(),
            ];
        };

        return [
            'facultes' => Faculte::count(),
            'promotions' => Promotion::count(),
            'cours' => Cours::count(),
            'horaires' => Horaire::where('statut', '!=', Horaire::STATUT_ANNULE)->count(),
            'conflits' => app(\App\Services\HoraireService::class)->conflictCount(),
            'enseignants' => User::where('role', User::ROLE_ENSEIGNANT)->count(),
            'etudiants' => User::where('role', User::ROLE_ETUDIANT)->count(),
            'etudiants_actifs' => User::where('role', User::ROLE_ETUDIANT)->where('status', User::STATUS_ACCEPTED)->count(),
            'demandes' => $parStatut(DemandeAuditoire::query()),
            'disponibilites' => Disponibilite::where('statut', Disponibilite::STATUT_VALIDEE)->count(),
            'auditoires' => Auditoire::count(),
            'capacite_totale' => Auditoire::sum('capacite'),
            'salles_occupees_aujourdhui' => Horaire::where('statut', '!=', Horaire::STATUT_ANNULE)
                ->whereDate('date', now()->toDateString())
                ->distinct('auditoire_id')
                ->count('auditoire_id'),
            'batiments' => Batiment::count(),
            'occupation_par_batiment' => Batiment::with('auditoires')->get()->mapWithKeys(function ($batiment) {
                $horairesSemaine = Horaire::where('statut', '!=', Horaire::STATUT_ANNULE)
                    ->whereBetween('date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                    ->whereIn('auditoire_id', $batiment->auditoires->pluck('id'))
                    ->distinct('auditoire_id')
                    ->count('auditoire_id');

                return [$batiment->nom => [
                    'auditoires' => $batiment->auditoires->count(),
                    'utilises' => $horairesSemaine,
                ]];
            }),
        ];
    }
}
