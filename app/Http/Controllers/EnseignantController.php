<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Http\Requests\EnseignantRequest;
use App\Models\Enseignant;
use App\Models\Faculte;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnseignantController extends Controller
{
    use ScopesFaculty;

    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_ENSEIGNANT)->with(['faculte'])->latest();

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        } elseif ($request->filled('faculte_id')) {
            $query->where('faculte_id', $request->input('faculte_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(fn ($q) => $q->where('nom', 'like', $term)
                ->orWhere('postnom', 'like', $term)
                ->orWhere('prenom', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('matricule', 'like', $term));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $enseignants = $query->paginate(12)->withQueryString();

        return view('enseignants.index', compact('enseignants'));
    }

    public function show(User $enseignant)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        if ($this->isScoped() && (int)$enseignant->faculte_id !== (int)$this->facultyId()) {
            abort(403, 'Vous ne pouvez pas consulter un enseignant d\'une autre faculté.');
        }
        $enseignant->load(['faculte', 'ecs.ue', 'coursEnseignes', 'horairesEnseignes', 'disponibilites']);
        return view('enseignants.show', compact('enseignant'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isDecanat(), 403);

        $facultes = $this->isScoped()
            ? Faculte::where('id', $this->facultyId())->orderBy('nom')->get()
            : Faculte::orderBy('nom')->get();

        return view('enseignants.form', [
            'enseignant' => new User(['role' => User::ROLE_ENSEIGNANT, 'status' => User::STATUS_ACCEPTED, 'is_active' => true, 'sexe' => 'M']),
            'facultes' => $facultes,
            'ecs' => $this->scopeUes()->with('ecs')->get()->flatMap(fn ($ue) => $ue->ecs)->pluck('nom', 'id'),
        ]);
    }

    public function store(EnseignantRequest $request, AuditService $audit)
    {
        $data = $request->validated();

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        // Génération automatique des identifiants de connexion
        // Si aucun matricule fourni, on en génère un unique
        if (empty($data['matricule'])) {
            $data['matricule'] = 'ENS-' . strtoupper(Str::random(6)) . '-' . time() % 10000;
        }
        // Mot de passe initial = matricule ou email local part ou aléatoire
        $plainPassword = $data['matricule'] ?? Str::random(10);
        // Si l'email existe déjà comme base, on génère un mot de passe lisible
        if (strlen($plainPassword) < 8) {
            $plainPassword = Str::random(8);
        }

        $data['password'] = Hash::make($plainPassword);
        $data['role'] = User::ROLE_ENSEIGNANT;
        $data['status'] = User::STATUS_ACCEPTED;
        $data['is_active'] = true;
        $data['statut_inscription'] = 'actif';

        $enseignant = User::create($data);
        $enseignant->ecs()->sync($request->input('ec_ids', []));

        try {
            Enseignant::create([
                'user_id' => $enseignant->id,
                'matricule' => $enseignant->matricule,
                'nom' => $enseignant->nom,
                'postnom' => $enseignant->postnom,
                'prenom' => $enseignant->prenom,
                'sexe' => $enseignant->sexe,
                'telephone' => $enseignant->telephone,
                'email' => $enseignant->email,
                'faculte_id' => $enseignant->faculte_id,
                'specialite' => $enseignant->specialite ?? null,
                'statut' => 'actif',
                'is_active' => true,
            ]);
        } catch (\Throwable $e) {}

        $audit->record('enseignant.created', $enseignant, $request->user(), array_merge($request->except('password'), ['generated_password' => $plainPassword]));

        return redirect()->route('enseignants.index')->with('success', "Enseignant {$enseignant->nom} {$enseignant->prenom} créé. Identifiant: {$enseignant->email} | Mot de passe initial: {$plainPassword} (à communiquer).");
    }

    public function edit(User $enseignant)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        if ($this->isScoped() && (int)$enseignant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }

        $facultes = $this->isScoped()
            ? Faculte::where('id', $this->facultyId())->orderBy('nom')->get()
            : Faculte::orderBy('nom')->get();

        return view('enseignants.form', [
            'enseignant' => $enseignant,
            'facultes' => $facultes,
            'ecs' => $this->scopeUes()->with('ecs')->get()->flatMap(fn ($ue) => $ue->ecs)->pluck('nom', 'id'),
        ]);
    }

    public function update(EnseignantRequest $request, User $enseignant, AuditService $audit)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        if ($this->isScoped() && (int)$enseignant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }

        $data = $request->validated();

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        $enseignant->update($data);
        $enseignant->ecs()->sync($request->input('ec_ids', []));

        try {
            Enseignant::where('user_id', $enseignant->id)->update([
                'matricule' => $enseignant->matricule,
                'nom' => $enseignant->nom,
                'postnom' => $enseignant->postnom,
                'prenom' => $enseignant->prenom,
                'sexe' => $enseignant->sexe,
                'telephone' => $enseignant->telephone,
                'email' => $enseignant->email,
                'faculte_id' => $enseignant->faculte_id,
            ]);
        } catch (\Throwable $e) {}

        $audit->record('enseignant.updated', $enseignant, $request->user(), $request->except('password'));

        return redirect()->route('enseignants.index')->with('success', 'Enseignant mis à jour.');
    }

    public function desactiver(User $enseignant, AuditService $audit)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        if ($this->isScoped() && (int)$enseignant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }
        $enseignant->update(['is_active' => false, 'status' => User::STATUS_REJECTED, 'statut_inscription' => 'inactif']);
        try { Enseignant::where('user_id', $enseignant->id)->update(['is_active' => false, 'statut' => 'inactif']); } catch (\Throwable $e) {}
        $audit->record('enseignant.desactivated', $enseignant, request()->user(), $enseignant->toArray());
        return back()->with('success', 'Enseignant désactivé.');
    }

    public function reactiver(User $enseignant, AuditService $audit)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        if ($this->isScoped() && (int)$enseignant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }
        $enseignant->update(['is_active' => true, 'status' => User::STATUS_ACCEPTED, 'statut_inscription' => 'actif']);
        try { Enseignant::where('user_id', $enseignant->id)->update(['is_active' => true, 'statut' => 'actif']); } catch (\Throwable $e) {}
        $audit->record('enseignant.reactivated', $enseignant, request()->user(), $enseignant->toArray());
        return back()->with('success', 'Enseignant réactivé.');
    }

    public function destroy(User $enseignant, AuditService $audit)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        if ($this->isScoped() && (int)$enseignant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }
        $audit->record('enseignant.deleted', $enseignant, request()->user(), $enseignant->toArray());
        try { Enseignant::where('user_id', $enseignant->id)->delete(); } catch (\Throwable $e) {}
        $enseignant->delete();

        return back()->with('success', 'Enseignant supprimé.');
    }
}
