<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Models\AnneeAcademique;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CascadeController extends Controller
{
    use ScopesFaculty;

    public function domaines(?int $faculte = null): JsonResponse
    {
        $faculteId = $this->isScoped() ? $this->facultyId() : $faculte;

        if ($this->isScoped() && $faculte && (int) $faculte !== (int) $this->facultyId()) {
            return response()->json([], 403);
        }

        $query = Domaine::query()->orderBy('nom');
        if ($faculteId) {
            $query->where('faculte_id', $faculteId);
        }

        return response()->json($query->get(['id', 'nom']));
    }

    public function filieres(int $domaine): JsonResponse
    {
        $query = Filiere::where('domaine_id', $domaine)->orderBy('nom');

        if ($this->isScoped()) {
            $query->whereHas('domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        return response()->json($query->get(['id', 'nom']));
    }

    public function mentions(int $filiere): JsonResponse
    {
        $query = Mention::where('filiere_id', $filiere)->orderBy('nom');

        if ($this->isScoped()) {
            $query->whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        return response()->json($query->get(['id', 'nom']));
    }

    public function promotions(int $mention): JsonResponse
    {
        $query = Promotion::where('mention_id', $mention)->orderBy('nom');

        if ($this->isScoped()) {
            $query->whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        return response()->json($query->get(['id', 'nom', 'effectif', 'annee_academique_id']));
    }

    public function semestres(?int $annee = null): JsonResponse
    {
        $query = Semestre::with('anneeAcademique')->orderBy('libelle');

        if ($annee) {
            $query->where('annee_academique_id', $annee);
        }

        return response()->json($query->get()->map(fn (Semestre $s) => [
            'id' => $s->id,
            'nom' => $s->libelle,
            'libelle' => $s->libelle . ($s->anneeAcademique ? ' — ' . $s->anneeAcademique->libelle : ''),
            'annee_academique_id' => $s->annee_academique_id,
        ]));
    }

    public function ues(Request $request): JsonResponse
    {
        $query = $this->scopeUes()->orderBy('nom');

        if ($request->filled('promotion_id')) {
            $query->where('promotion_id', $request->input('promotion_id'));
        }

        if ($request->filled('semestre_id')) {
            $query->where('semestre_id', $request->input('semestre_id'));
        }

        return response()->json($query->get(['id', 'code', 'nom', 'promotion_id', 'semestre_id']));
    }

    public function ecs(int $ue): JsonResponse
    {
        $query = Ec::where('ue_id', $ue)->orderBy('nom');

        if ($this->isScoped()) {
            $query->whereHas('ue.promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        return response()->json($query->get(['id', 'code', 'nom', 'enseignant_id', 'volume_horaire']));
    }

    public function enseignants(): JsonResponse
    {
        $query = User::where('role', User::ROLE_ENSEIGNANT)->orderBy('nom');

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        }

        return response()->json($query->get()->map(fn (User $u) => [
            'id' => $u->id,
            'nom' => $u->nom_complet,
        ]));
    }

    public function annees(): JsonResponse
    {
        return response()->json(AnneeAcademique::orderByDesc('libelle')->get(['id', 'libelle', 'active']));
    }
}
