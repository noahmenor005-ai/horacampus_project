<?php

namespace App\Services;

use App\Models\Auditoire;
use App\Models\Cours;
use App\Models\Ec;
use App\Models\Horaire;
use App\Models\Promotion;
use App\Support\TimeHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class HoraireService
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Horaire::with([
            'cours.ec',
            'auditoire.batiment',
            'enseignant',
            'promotion.mention',
            'semestre',
            'ec',
            'ue',
            'demandes',
        ]);

        $user = $request->user();
        if ($user) {
            if ($user->isEnseignant()) {
                $query->where('enseignant_id', $user->id);
            }

            if ($user->isEtudiant()) {
                $query->where('promotion_id', $user->promotion_id);
            }

            if ($user->isDecanat()) {
                $query->whereHas('promotion.mention.filiere.domaine', function (Builder $q) use ($user) {
                    $q->where('faculte_id', $user->faculte_id);
                });
            }
        }

        foreach (['promotion_id', 'enseignant_id', 'auditoire_id', 'semestre_id', 'ec_id', 'ue_id', 'domaine_id', 'filiere_id', 'mention_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('jour')) {
            $query->where('jour', $request->input('jour'));
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('faculte_id')) {
            $query->whereHas('promotion.mention.filiere.domaine', function (Builder $q) use ($request) {
                $q->where('faculte_id', $request->input('faculte_id'));
            });
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function (Builder $builder) use ($term) {
                $builder->whereHas('cours.ec', fn (Builder $q) => $q->where('nom', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('ec', fn (Builder $q) => $q->where('nom', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('auditoire', fn (Builder $q) => $q->where('nom', 'like', $term))
                    ->orWhereHas('enseignant', fn (Builder $q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))
                    ->orWhereHas('promotion', fn (Builder $q) => $q->where('nom', 'like', $term));
            });
        }

        return $query->orderBy('date')->orderBy('heure_debut');
    }

    public function create(array $data): Horaire
    {
        $data = $this->normalize($data);
        $this->assertSchedulable($data);

        return Horaire::create($data);
    }

    public function update(Horaire $horaire, array $data): Horaire
    {
        $data = $this->normalize($data);
        $this->assertSchedulable($data, $horaire);
        $horaire->update($data);

        return $horaire;
    }

    public function assertSchedulable(array $data, ?Horaire $ignore = null): void
    {
        $conflicts = $this->conflictsFor($data, $ignore);
        if (!empty($conflicts)) {
            throw ValidationException::withMessages(['planification' => $conflicts]);
        }
    }

    public function conflictsFor(array $data, ?Horaire $ignore = null): array
    {
        $messages = [];
        $data = $this->normalize($data);

        if (empty($data['heure_debut']) || empty($data['heure_fin']) || !TimeHelper::isValidRange($data['heure_debut'], $data['heure_fin'])) {
            $messages[] = 'Impossible de programmer ce cours : l\'horaire est invalide (l\'heure de fin doit être postérieure à l\'heure de début).';
            return $messages;
        }

        $date = \Illuminate\Support\Carbon::parse($data['date'])->toDateString();

        $query = Horaire::query()
            ->whereDate('date', $date)
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->where('heure_debut', '<', $data['heure_fin'])
            ->where('heure_fin', '>', $data['heure_debut']);

        if ($ignore) {
            $query->whereKeyNot($ignore->getKey());
        }

        $debut = substr((string) $data['heure_debut'], 0, 5);
        $fin = substr((string) $data['heure_fin'], 0, 5);

        if (!empty($data['auditoire_id']) && !$this->isPlaceholderRoom((int) $data['auditoire_id'])) {
            $auditoire = $query->clone()->where('auditoire_id', $data['auditoire_id'])->first();
            if ($auditoire) {
                $nomSalle = optional($auditoire->auditoire)->nom ?: 'sélectionnée';
                $messages[] = "Impossible de programmer ce cours : la salle « {$nomSalle} » est déjà occupée de {$debut} à {$fin}.";
            }
        }

        $enseignant = $query->clone()->where('enseignant_id', $data['enseignant_id'])->first();
        if ($enseignant) {
            $nom = optional($enseignant->enseignant)->nom_complet ?: 'sélectionné';
            $messages[] = "Impossible de programmer ce cours : l'enseignant {$nom} est déjà occupé de {$debut} à {$fin}.";
        }

        $promotion = $query->clone()->where('promotion_id', $data['promotion_id'])->first();
        if ($promotion) {
            $nom = optional($promotion->promotion)->nom ?: 'sélectionnée';
            $messages[] = "Impossible de programmer ce cours : la promotion « {$nom} » est déjà occupée de {$debut} à {$fin}.";
        }

        $ecId = $data['ec_id'] ?? null;
        if (!$ecId && !empty($data['cours_id'])) {
            $ecId = optional(Cours::find($data['cours_id']))->ec_id;
        }

        if ($ecId) {
            $ecConflict = $query->clone()
                ->where(function (Builder $builder) use ($ecId) {
                    $builder->where('ec_id', $ecId)
                        ->orWhereHas('cours', fn (Builder $q) => $q->where('ec_id', $ecId));
                })
                ->first();

            if ($ecConflict) {
                $ec = Ec::find($ecId);
                $messages[] = "Impossible de programmer ce cours : l'EC « " . ($ec->nom ?? $ecId) . " » est déjà programmé de {$debut} à {$fin}.";
            }
        }

        $dispo = app(DisponibiliteService::class);
        if (!empty($data['enseignant_id']) && !$dispo->estDisponible((int) $data['enseignant_id'], $date, $data['heure_debut'], $data['heure_fin'])) {
            $messages[] = "Impossible de programmer ce cours : l'enseignant n'est pas disponible de {$debut} à {$fin}.";
        }

        $effectif = (int) ($data['effectif_attendu'] ?? 0);
        if (!$effectif && !empty($data['promotion_id'])) {
            $promotionModel = Promotion::find($data['promotion_id']);
            $effectif = $promotionModel ? (int) $promotionModel->effectif : 0;
        }

        $auditoireModel = !empty($data['auditoire_id']) ? Auditoire::find($data['auditoire_id']) : null;
        if ($auditoireModel && !$this->isPlaceholderRoom((int) $auditoireModel->id) && $effectif > $auditoireModel->capacite) {
            $messages[] = "La capacité de l'auditoire « {$auditoireModel->nom} » ({$auditoireModel->capacite} places) est insuffisante pour un effectif attendu de {$effectif} étudiants.";
        }

        return $messages;
    }

    public function estProgrammable(array $data, ?Horaire $ignore = null): bool
    {
        return empty($this->conflictsFor($data, $ignore));
    }

    public function conflictCount(): int
    {
        $horaires = Horaire::all()->groupBy(fn ($h) => optional($h->date)->format('Y-m-d'));

        $conflicts = 0;
        foreach ($horaires as $group) {
            $list = $group->all();
            for ($i = 0; $i < count($list); $i++) {
                for ($j = $i + 1; $j < count($list); $j++) {
                    $a = $list[$i];
                    $b = $list[$j];
                    if ($a->statut === Horaire::STATUT_ANNULE || $b->statut === Horaire::STATUT_ANNULE) {
                        continue;
                    }
                    $overlap = $a->heure_debut < $b->heure_fin && $a->heure_fin > $b->heure_debut;
                    if ($overlap && ($a->auditoire_id === $b->auditoire_id || $a->enseignant_id === $b->enseignant_id || $a->promotion_id === $b->promotion_id)) {
                        $conflicts++;
                    }
                }
            }
        }

        return $conflicts;
    }

    public function chartByDay(): Collection
    {
        return Horaire::query()
            ->selectRaw('date, count(*) as total')
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->date->format('Y-m-d') => (int) $row->total]);
    }

    public function isPlaceholderRoom(?int $auditoireId): bool
    {
        if (!$auditoireId) {
            return true;
        }

        $room = Auditoire::find($auditoireId);

        return $room && $room->nom === 'EN-ATTENTE';
    }

    public function placeholderAuditoire(): ?Auditoire
    {
        return Auditoire::where('nom', 'EN-ATTENTE')->first();
    }

    public function weeklyGrid($horaires, string $start = '08:00', string $end = '18:00'): array
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $hours = [];
        $startH = (int) substr($start, 0, 2);
        $endH = (int) substr($end, 0, 2);
        for ($h = $startH; $h < $endH; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        $grid = [];
        foreach ($hours as $hour) {
            foreach ($jours as $jour) {
                $grid[$hour][$jour] = [];
            }
        }

        foreach ($horaires as $horaire) {
            $jour = $horaire->jour;
            if (!in_array($jour, $jours, true)) {
                continue;
            }
            $slot = substr((string) $horaire->heure_debut, 0, 2) . ':00';
            if (!isset($grid[$slot][$jour])) {
                $grid[$slot][$jour] = [];
            }
            $grid[$slot][$jour][] = $horaire;
        }

        return compact('jours', 'hours', 'grid');
    }

    private function normalize(array $data): array
    {
        if (!empty($data['heure_debut'])) {
            $data['heure_debut'] = TimeHelper::normalize($data['heure_debut']);
        }
        if (!empty($data['heure_fin'])) {
            $data['heure_fin'] = TimeHelper::normalize($data['heure_fin']);
        }

        if (!empty($data['date']) && empty($data['jour'])) {
            $data['jour'] = Horaire::jourFr($data['date']);
        }

        return $data;
    }

}
