<?php

namespace App\Services;

use App\Models\Auditoire;
use App\Models\DemandeAuditoire;
use App\Models\Horaire;
use Illuminate\Database\Eloquent\Collection;

class SalleService
{
    /**
     * Auditoires libres sur un créneau donné, dont la capacité suffit.
     */
    public function auditoiresDisponibles(
        string $date,
        string $heureDebut,
        string $heureFin,
        int $effectif,
        ?int $demandeIgnoreId = null
    ): Collection {
        return Auditoire::query()
            ->with('batiment')
            ->where('disponibilite', true)
            ->where('etat', 'disponible')
            ->where('capacite', '>=', $effectif)
            ->whereDoesntHave('horaires', function ($q) use ($date, $heureDebut, $heureFin) {
                $q->whereDate('date', $date)
                    ->where('statut', '!=', Horaire::STATUT_ANNULE)
                    ->where('heure_debut', '<', $heureFin)
                    ->where('heure_fin', '>', $heureDebut);
            })
            ->whereDoesntHave('demandes', function ($q) use ($date, $heureDebut, $heureFin, $demandeIgnoreId) {
                $q->whereDate('date', $date)
                    ->whereIn('statut', [DemandeAuditoire::STATUT_EN_ATTENTE, DemandeAuditoire::STATUT_MODIFIEE, DemandeAuditoire::STATUT_ACCEPTEE])
                    ->when($demandeIgnoreId, fn ($query) => $query->whereKeyNot($demandeIgnoreId))
                    ->where('heure_debut', '<', $heureFin)
                    ->where('heure_fin', '>', $heureDebut);
            })
            ->orderBy('capacite')
            ->get();
    }

    public function suggerer(DemandeAuditoire $demande): Collection
    {
        return $this->auditoiresDisponibles(
            $demande->date->format('Y-m-d'),
            $demande->heure_debut,
            $demande->heure_fin,
            $demande->effectif_attendu,
            $demande->id
        );
    }

    public function estLibre(int $auditoireId, string $date, string $heureDebut, string $heureFin, ?int $ignoreId = null): bool
    {
        return !Auditoire::whereKey($auditoireId)
            ->where('disponibilite', true)
            ->where('etat', 'disponible')
            ->whereHas('horaires', function ($q) use ($date, $heureDebut, $heureFin) {
                $q->whereDate('date', $date)
                    ->where('statut', '!=', Horaire::STATUT_ANNULE)
                    ->where('heure_debut', '<', $heureFin)
                    ->where('heure_fin', '>', $heureDebut);
            })->exists();
    }
}
