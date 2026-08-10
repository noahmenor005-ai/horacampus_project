<?php

namespace App\Services;

use App\Models\Disponibilite;
use App\Models\Horaire;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DisponibiliteService
{
    public function chevauchements(int $userId, string $jour, string $heureDebut, string $heureFin, ?int $ignoreId = null): Collection
    {
        return Disponibilite::query()
            ->where('user_id', $userId)
            ->where('jour', $jour)
            ->where('statut', '!=', Disponibilite::STATUT_REFUSEE)
            ->where('heure_debut', '<', $heureFin)
            ->where('heure_fin', '>', $heureDebut)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->get();
    }

    public function assertSansChevauchement(int $userId, string $jour, string $heureDebut, string $heureFin, ?int $ignoreId = null): void
    {
        $overlaps = $this->chevauchements($userId, $jour, $heureDebut, $heureFin, $ignoreId);
        if ($overlaps->isNotEmpty()) {
            throw ValidationException::withMessages([
                'planification' => 'Ce créneau de disponibilité chevauche une disponibilité déjà enregistrée le ' . $jour . '.',
            ]);
        }
    }

    /**
     * Vérifie qu'un enseignant dispose d'une disponibilité validée couvrant le créneau.
     */
    public function estDisponible(int $enseignantId, string $date, string $heureDebut, string $heureFin): bool
    {
        $jour = Horaire::jourFr($date);

        return Disponibilite::query()
            ->where('user_id', $enseignantId)
            ->where('jour', $jour)
            ->where('statut', Disponibilite::STATUT_VALIDEE)
            ->where('heure_debut', '<=', $heureDebut)
            ->where('heure_fin', '>=', $heureFin)
            ->exists();
    }
}
