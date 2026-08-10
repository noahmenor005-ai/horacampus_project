<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Models\Cours;
use App\Models\Ec;
use App\Models\Promotion;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CoursController extends Controller
{
    use ScopesFaculty;

    public function index(Request $request)
    {
        $query = Cours::with(['ec.ue', 'promotion.mention', 'enseignant'])->latest();

        if ($this->isScoped()) {
            $query->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $this->facultyId()));
        }

        foreach (['promotion_id', 'enseignant_id', 'type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('ec', fn ($ec) => $ec->where('nom', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('enseignant', fn ($e) => $e->where('nom', 'like', $term)->orWhere('prenom', 'like', $term));
            });
        }

        $cours = $query->paginate(12)->withQueryString();

        return view('cours.index', [
            'cours' => $cours,
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
            'enseignants' => User::where('role', User::ROLE_ENSEIGNANT)->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('cours.form', [
            'cours' => new Cours(),
            'ecs' => $this->ecsScopes(),
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
            'enseignants' => $this->enseignantsScoped(),
        ]);
    }

    public function store(Request $request, AuditService $audit, NotificationService $notifications)
    {
        $data = $this->validateData($request);

        $this->assertUnique($data);

        $cours = Cours::create($data);
        $audit->record('cours.created', $cours, $request->user(), $request->all());
        $notifications->notifyUser(User::find($data['enseignant_id']), 'Cours assigné', "Le cours « {$cours->intitule} » vous a été assigné.");

        return redirect()->route('cours.index')->with('success', 'Cours créé avec succès.');
    }

    public function edit(Cours $cours)
    {
        return view('cours.form', [
            'cours' => $cours,
            'ecs' => $this->ecsScopes(),
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
            'enseignants' => $this->enseignantsScoped(),
        ]);
    }

    public function update(Request $request, Cours $cours, AuditService $audit)
    {
        $data = $this->validateData($request, $cours);

        $this->assertUnique($data, $cours);

        $cours->update($data);
        $audit->record('cours.updated', $cours, $request->user(), $request->all());

        return redirect()->route('cours.index')->with('success', 'Cours mis à jour.');
    }

    public function destroy(Cours $cours, AuditService $audit)
    {
        $audit->record('cours.deleted', $cours, request()->user(), $cours->toArray());
        $cours->delete();

        return back()->with('success', 'Cours supprimé.');
    }

    private function ecsScopes()
    {
        return $this->scopeUes()->with('ecs')->get()
            ->flatMap(fn ($ue) => $ue->ecs->map(fn ($ec) => $ec->code . ' — ' . $ec->nom . ' (' . optional($ue->promotion)->nom . ')')->combine($ue->ecs->pluck('id')));
    }

    private function enseignantsScoped()
    {
        $query = User::where('role', User::ROLE_ENSEIGNANT)->orderBy('nom');

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        }

        return $query->get()->mapWithKeys(fn ($u) => [$u->id => $u->nom_complet]);
    }

    private function validateData(Request $request, ?Cours $cours = null): array
    {
        return $request->validate([
            'ec_id' => ['required', 'exists:ecs,id'],
            'enseignant_id' => ['required', 'exists:users,id'],
            'promotion_id' => ['required', 'exists:promotions,id'],
            'type' => ['required', Rule::in(array_keys(Cours::TYPES))],
            'volume_horaire' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function assertUnique(array $data, ?Cours $ignore = null): void
    {
        $exists = Cours::where('ec_id', $data['ec_id'])
            ->where('promotion_id', $data['promotion_id'])
            ->where('type', $data['type'])
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'type' => 'Ce cours existe déjà pour cet EC, cette promotion et ce type.',
            ]);
        }
    }
}
