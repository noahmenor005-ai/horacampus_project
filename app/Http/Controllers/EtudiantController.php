<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Models\Domaine;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EtudiantController extends Controller
{
    use ScopesFaculty;

    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_ETUDIANT)
            ->with(['faculte', 'promotion.mention'])
            ->latest();

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        }

        foreach (['faculte_id', 'promotion_id', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(fn ($q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)->orWhere('email', 'like', $term));
        }

        $etudiants = $query->paginate(12)->withQueryString();

        return view('etudiants.index', [
            'etudiants' => $etudiants,
            'facultes' => Faculte::orderBy('nom')->get(),
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('etudiants.form', [
            'etudiant' => new User(['role' => User::ROLE_ETUDIANT, 'status' => User::STATUS_PENDING]),
            'facultes' => Faculte::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request, AuditService $audit)
    {
        $data = $this->validateData($request);
        $data['password'] = Hash::make($data['password']);
        $data['role'] = User::ROLE_ETUDIANT;
        $data['status'] = User::STATUS_PENDING;

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        $etudiant = User::create($data);
        $audit->record('etudiant.created', $etudiant, $request->user(), $request->except('password'));

        return redirect()->route('etudiants.index')->with('success', 'Étudiant enregistré, en attente de validation.');
    }

    public function edit(User $etudiant)
    {
        abort_unless($etudiant->isEtudiant(), 404);

        return view('etudiants.form', [
            'etudiant' => $etudiant,
            'facultes' => Faculte::orderBy('nom')->get(),
            'domaines' => Domaine::where('faculte_id', $etudiant->faculte_id)->orderBy('nom')->get(),
            'filieres' => Filiere::where('domaine_id', $etudiant->domaine_id)->orderBy('nom')->get(),
            'mentions' => Mention::where('filiere_id', $etudiant->filiere_id)->orderBy('nom')->get(),
            'promotions' => Promotion::where('mention_id', $etudiant->mention_id)->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, User $etudiant, AuditService $audit)
    {
        abort_unless($etudiant->isEtudiant(), 404);

        $data = $this->validateData($request, $etudiant);

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        $etudiant->update($data);
        $audit->record('etudiant.updated', $etudiant, $request->user(), $request->except('password'));

        return redirect()->route('etudiants.index')->with('success', 'Étudiant mis à jour.');
    }

    public function destroy(User $etudiant, AuditService $audit)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        $audit->record('etudiant.deleted', $etudiant, request()->user(), $etudiant->toArray());
        $etudiant->delete();

        return back()->with('success', 'Étudiant supprimé.');
    }

    private function validateData(Request $request, ?User $etudiant = null): array
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($etudiant?->id)],
            'telephone' => ['nullable', 'string', 'max:30'],
            'password' => [$etudiant ? 'nullable' : 'required', 'min:8', 'confirmed'],
            'faculte_id' => ['required', 'exists:facultes,id'],
            'domaine_id' => ['required', 'exists:domaines,id'],
            'filiere_id' => ['required', 'exists:filieres,id'],
            'mention_id' => ['required', 'exists:mentions,id'],
            'promotion_id' => ['required', 'exists:promotions,id'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
