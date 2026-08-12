<?php

namespace App\Support;

use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Disponibilite;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Filiere;
use App\Models\Horaire;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FacultyGuard
{
    public static function facultyIdOf(?Model $model): ?int
    {
        if (!$model) {
            return null;
        }

        if (isset($model->faculte_id) && $model->faculte_id) {
            return (int) $model->faculte_id;
        }

        if ($model instanceof Domaine) {
            return $model->faculte_id ? (int) $model->faculte_id : null;
        }

        if ($model instanceof Filiere) {
            $model->loadMissing('domaine');
            return optional($model->domaine)->faculte_id ? (int) $model->domaine->faculte_id : null;
        }

        if ($model instanceof Mention) {
            $model->loadMissing('filiere.domaine');
            return optional(optional($model->filiere)->domaine)->faculte_id
                ? (int) $model->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof Promotion) {
            $model->loadMissing('mention.filiere.domaine');
            return optional(optional(optional($model->mention)->filiere)->domaine)->faculte_id
                ? (int) $model->mention->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof Ue) {
            $model->loadMissing('promotion.mention.filiere.domaine');
            return optional(optional(optional(optional($model->promotion)->mention)->filiere)->domaine)->faculte_id
                ? (int) $model->promotion->mention->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof Ec) {
            $model->loadMissing('ue.promotion.mention.filiere.domaine');
            return optional(optional(optional(optional(optional($model->ue)->promotion)->mention)->filiere)->domaine)->faculte_id
                ? (int) $model->ue->promotion->mention->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof Cours) {
            $model->loadMissing('promotion.mention.filiere.domaine');
            return optional(optional(optional(optional($model->promotion)->mention)->filiere)->domaine)->faculte_id
                ? (int) $model->promotion->mention->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof Horaire) {
            if ($model->domaine_id) {
                $domaine = Domaine::find($model->domaine_id);
                return $domaine ? (int) $domaine->faculte_id : null;
            }
            $model->loadMissing('promotion.mention.filiere.domaine');
            return optional(optional(optional(optional($model->promotion)->mention)->filiere)->domaine)->faculte_id
                ? (int) $model->promotion->mention->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof DemandeAuditoire) {
            $model->loadMissing('promotion.mention.filiere.domaine');
            return optional(optional(optional(optional($model->promotion)->mention)->filiere)->domaine)->faculte_id
                ? (int) $model->promotion->mention->filiere->domaine->faculte_id
                : null;
        }

        if ($model instanceof Disponibilite) {
            $model->loadMissing('user');
            return optional($model->user)->faculte_id ? (int) $model->user->faculte_id : null;
        }

        if ($model instanceof User) {
            return $model->faculte_id ? (int) $model->faculte_id : null;
        }

        return null;
    }

    public static function assert(?Model $model, ?User $user = null): void
    {
        $user = $user ?: auth()->user();

        if (!$user || !$user->isDecanat()) {
            return;
        }

        $facultyId = self::facultyIdOf($model);

        if ($facultyId && (int) $facultyId !== (int) $user->faculte_id) {
            abort(403, 'Vous ne pouvez accéder qu\'aux données de votre propre faculté.');
        }
    }

    public static function assertId(?int $facultyId, ?User $user = null): void
    {
        $user = $user ?: auth()->user();

        if (!$user || !$user->isDecanat() || !$facultyId) {
            return;
        }

        if ((int) $facultyId !== (int) $user->faculte_id) {
            abort(403, 'Vous ne pouvez accéder qu\'aux données de votre propre faculté.');
        }
    }
}
