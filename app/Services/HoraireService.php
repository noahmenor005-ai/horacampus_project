<?php

namespace App\Services;

use App\Models\Auditoire;
use App\Models\Horaire;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class HoraireService
{
    public function filteredQuery(Request $request): Builder
    {
        $query = Horaire::with(['cours.ec', 'auditoire.batiment', 'enseignant', 'promotion.mention', 'semestre']);

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

        foreach (['jour', 'promotion_id', 'enseignant_id', 'auditoire_id', 'semestre_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
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
                    ->orWhereHas('auditoire', fn (Builder $q) => $q->where('nom', 'like', $term))
                    ->orWhereHas('enseignant', fn (Builder $q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))
                    ->orWhereHas('promotion', fn (Builder $q) => $q->where('nom', 'like', $term));
            });
        }

        return $query->orderBy('date')->orderBy('heure_debut');
    }

    public function create(array $data): Horaire
    {
        $this->assertSchedulable($data);

        return Horaire::create($data);
    }

    public function update(Horaire $horaire, array $data): Horaire
    {
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

        $date = \Illuminate\Support\Carbon::parse($data['date'])->toDateString();

        $query = Horaire::query()
            ->whereDate('date', $date)
            ->where('statut', '!=', Horaire::STATUT_ANNULE)
            ->where('heure_debut', '<', $data['heure_fin'])
            ->where('heure_fin', '>', $data['heure_debut']);

        if ($ignore) {
            $query->whereKeyNot($ignore->getKey());
        }

        $auditoire = $query->clone()->where('auditoire_id', $data['auditoire_id'])->first();
        if ($auditoire) {
            $messages[] = "L'auditoire « {$auditoire->auditoire->nom} » est déjà occupé(e) par un autre cours sur ce créneau.";
        }

        $enseignant = $query->clone()->where('enseignant_id', $data['enseignant_id'])->first();
        if ($enseignant) {
            $messages[] = "L'enseignant « {$enseignant->enseignant->nom_complet} » est déjà programmé(e) sur ce créneau.";
        }

        $promotion = $query->clone()->where('promotion_id', $data['promotion_id'])->first();
        if ($promotion) {
            $messages[] = "La promotion « {$promotion->promotion->nom} » a déjà un cours sur ce créneau.";
        }

        $effectif = (int) ($data['effectif_attendu'] ?? 0);
        if (!$effectif) {
            $promotionModel = Promotion::find($data['promotion_id']);
            $effectif = $promotionModel ? (int) $promotionModel->effectif : 0;
        }
        $auditoireModel = $data['auditoire_id'] ? Auditoire::find($data['auditoire_id']) : null;
        if ($auditoireModel && $effectif > $auditoireModel->capacite) {
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
        $horaires = Horaire::all()->groupBy(fn ($h) => $h->date->format('Y-m-d'));

        $conflicts = 0;
        foreach ($horaires as $date => $group) {
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
}
