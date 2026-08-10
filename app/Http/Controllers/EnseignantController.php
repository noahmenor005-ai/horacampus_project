<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Models\Ec;
use App\Models\Faculte;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EnseignantController extends Controller
{
    use ScopesFaculty;

    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_ENSEIGNANT)->with(['faculte'])->latest();

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        }

        if ($request->filled('faculte_id')) {
            $query->where('faculte_id', $request->input('faculte_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(fn ($q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)->orWhere('email', 'like', $term));
        }

        $enseignants = $query->paginate(12)->withQueryString();

        return view('enseignants.index', compact('enseignants'));
    }

    public function create()
    {
        return view('enseignants.form', [
            'enseignant' => new User(['role' => User::ROLE_ENSEIGNANT, 'status' => User::STATUS_ACCEPTED]),
            'facultes' => Faculte::orderBy('nom')->get(),
            'ecs' => $this->scopeUes()->with('ecs')->get()->flatMap(fn ($ue) => $ue->ecs)->pluck('nom', 'id'),
        ]);
    }

    public function store(Request $request, AuditService $audit)
    {
        $data = $this->validateData($request);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = User::ROLE_ENSEIGNANT;
        $data['status'] = User::STATUS_ACCEPTED;

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        $enseignant = User::create($data);
        $enseignant->ecs()->sync($request->input('ec_ids', []));
        $audit->record('enseignant.created', $enseignant, $request->user(), $request->except('password'));

        return redirect()->route('enseignants.index')->with('success', 'Enseignant ajouté avec succès.');
    }

    public function edit(User $enseignant)
    {
        abort_unless($enseignant->isEnseignant(), 404);

        return view('enseignants.form', [
            'enseignant' => $enseignant,
            'facultes' => Faculte::orderBy('nom')->get(),
            'ecs' => $this->scopeUes()->with('ecs')->get()->flatMap(fn ($ue) => $ue->ecs)->pluck('nom', 'id'),
        ]);
    }

    public function update(Request $request, User $enseignant, AuditService $audit)
    {
        abort_unless($enseignant->isEnseignant(), 404);

        $data = $this->validateData($request, $enseignant);

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        $enseignant->update($data);
        $enseignant->ecs()->sync($request->input('ec_ids', []));
        $audit->record('enseignant.updated', $enseignant, $request->user(), $request->except('password'));

        return redirect()->route('enseignants.index')->with('success', 'Enseignant mis à jour.');
    }

    public function destroy(User $enseignant, AuditService $audit)
    {
        abort_unless($enseignant->isEnseignant(), 404);
        $audit->record('enseignant.deleted', $enseignant, request()->user(), $enseignant->toArray());
        $enseignant->delete();

        return back()->with('success', 'Enseignant supprimé.');
    }

    private function validateData(Request $request, ?User $enseignant = null): array
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($enseignant?->id)],
            'telephone' => ['nullable', 'string', 'max:30'],
            'password' => [$enseignant ? 'nullable' : 'required', 'min:8', 'confirmed'],
            'faculte_id' => ['nullable', 'exists:facultes,id'],
            'ec_ids' => ['nullable', 'array'],
            'ec_ids.*' => ['integer', 'exists:ecs,id'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
