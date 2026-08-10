<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesFaculty;
use App\Http\Requests\EtudiantRequest;
use App\Models\AnneeAcademique;
use App\Models\Domaine;
use App\Models\Etudiant;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EtudiantController extends Controller
{
    use ScopesFaculty;

    public function index(Request $request)
    {
        // Seul le Décanat peut accéder (middleware role:decanat)
        $query = User::where('role', User::ROLE_ETUDIANT)
            ->with(['faculte', 'domaine', 'filiere', 'mention', 'promotion', 'anneeAcademique'])
            ->latest();

        if ($this->isScoped()) {
            $query->where('faculte_id', $this->facultyId());
        }

        // Filtres
        if ($request->filled('faculte_id') && !$this->isScoped()) {
            $query->where('faculte_id', $request->input('faculte_id'));
        }
        $filterable = ['domaine_id', 'filiere_id', 'mention_id', 'promotion_id', 'annee_academique_id', 'status', 'is_active', 'sexe'];
        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nom', 'like', $term)
                  ->orWhere('postnom', 'like', $term)
                  ->orWhere('prenom', 'like', $term)
                  ->orWhere('matricule', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('telephone', 'like', $term);
            });
        }

        // Statut actif/inactif
        if ($request->filled('statut_inscription')) {
            $query->where('statut_inscription', $request->input('statut_inscription'));
        }

        $etudiants = $query->paginate(12)->withQueryString();

        // Données pour filtres - scopées par faculté du décanat
        $facultes = $this->isScoped()
            ? Faculte::where('id', $this->facultyId())->orderBy('nom')->get()
            : Faculte::orderBy('nom')->get();

        return view('etudiants.index', [
            'etudiants' => $etudiants,
            'facultes' => $facultes,
            'domaines' => $this->scopeDomaines()->orderBy('nom')->get(),
            'promotions' => $this->scopePromotions()->orderBy('nom')->get(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ]);
    }

    public function show(User $etudiant)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        // Vérification faculté
        if ($this->isScoped() && (int)$etudiant->faculte_id !== (int)$this->facultyId()) {
            abort(403, 'Vous ne pouvez pas consulter un étudiant d\'une autre faculté.');
        }
        $etudiant->load(['faculte', 'domaine', 'filiere', 'mention', 'promotion', 'anneeAcademique']);
        return view('etudiants.show', compact('etudiant'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isDecanat(), 403);

        $facultes = $this->isScoped()
            ? Faculte::where('id', $this->facultyId())->orderBy('nom')->get()
            : Faculte::orderBy('nom')->get();

        return view('etudiants.form', [
            'etudiant' => new User(['role' => User::ROLE_ETUDIANT, 'status' => User::STATUS_ACCEPTED, 'statut_inscription' => 'actif', 'is_active' => true, 'sexe' => 'M']),
            'facultes' => $facultes,
            'domaines' => $this->scopeDomaines()->orderBy('nom')->get(),
            'filieres' => collect(),
            'mentions' => collect(),
            'promotions' => collect(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ]);
    }

    public function store(EtudiantRequest $request, AuditService $audit)
    {
        $data = $request->validated();

        // Forcer la faculté du décanat connecté - ne jamais faire confiance au navigateur
        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        // Génération automatique du compte étudiant
        // Matricule déjà validé unique, on l'utilise comme mot de passe initial
        $plainPassword = $data['matricule'];
        // Alternative : génération aléatoire sécurisée
        // $plainPassword = Str::random(10);

        $data['password'] = Hash::make($plainPassword);
        $data['role'] = User::ROLE_ETUDIANT;
        $data['status'] = User::STATUS_ACCEPTED; // Le décanat valide immédiatement
        $data['is_active'] = true;
        $data['statut_inscription'] = $data['statut'] ?? 'actif';

        // Email facultatif : si vide, générer un email placeholder unique pour respecter contrainte NOT NULL si nécessaire
        if (empty($data['email'])) {
            // On laisse null si la DB le permet, sinon placeholder
            // Le modèle User fera en sorte que email nullable soit accepté
            // Pour garantir l'unicité, on génère matricule@etudiant.horacampus.local si besoin
            // Mais on tente null d'abord
            $data['email'] = null;
            // Si la colonne email est NOT NULL en DB, la création échouera - on catch et on génère placeholder
        }

        try {
            $etudiant = User::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Si échec dû à email NOT NULL, générer placeholder
            if (empty($data['email']) && Str::contains($e->getMessage(), 'email')) {
                $data['email'] = strtolower($data['matricule']) . '@etudiant.horacampus.local';
                $etudiant = User::create($data);
            } else {
                throw $e;
            }
        }

        // Création miroir dans la table etudiants pour conformité spec 9
        try {
            Etudiant::create([
                'user_id' => $etudiant->id,
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom,
                'postnom' => $etudiant->postnom,
                'prenom' => $etudiant->prenom,
                'sexe' => $etudiant->sexe,
                'telephone' => $etudiant->telephone,
                'email' => $etudiant->email,
                'faculte_id' => $etudiant->faculte_id,
                'domaine_id' => $etudiant->domaine_id,
                'filiere_id' => $etudiant->filiere_id,
                'mention_id' => $etudiant->mention_id,
                'promotion_id' => $etudiant->promotion_id,
                'annee_academique_id' => $etudiant->annee_academique_id,
                'statut' => $etudiant->statut_inscription ?? 'actif',
                'is_active' => true,
            ]);
        } catch (\Throwable $e) {
            // Table etudiants optionnelle, ne pas bloquer la création principale
        }

        $audit->record('etudiant.created', $etudiant, $request->user(), array_merge($request->except('password'), ['generated_password' => $plainPassword]));

        return redirect()->route('etudiants.index')->with('success', "Étudiant {$etudiant->nom} {$etudiant->prenom} créé avec succès. Matricule: {$etudiant->matricule} | Mot de passe initial: {$plainPassword} (à communiquer à l'étudiant).");
    }

    public function edit(User $etudiant)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        if ($this->isScoped() && (int)$etudiant->faculte_id !== (int)$this->facultyId()) {
            abort(403, 'Vous ne pouvez pas modifier un étudiant d\'une autre faculté.');
        }

        $facultes = $this->isScoped()
            ? Faculte::where('id', $this->facultyId())->orderBy('nom')->get()
            : Faculte::orderBy('nom')->get();

        return view('etudiants.form', [
            'etudiant' => $etudiant,
            'facultes' => $facultes,
            'domaines' => Domaine::where('faculte_id', $etudiant->faculte_id)->orderBy('nom')->get(),
            'filieres' => $etudiant->domaine_id ? Filiere::where('domaine_id', $etudiant->domaine_id)->orderBy('nom')->get() : collect(),
            'mentions' => $etudiant->filiere_id ? Mention::where('filiere_id', $etudiant->filiere_id)->orderBy('nom')->get() : collect(),
            'promotions' => $etudiant->mention_id ? Promotion::where('mention_id', $etudiant->mention_id)->orderBy('nom')->get() : collect(),
            'annees' => AnneeAcademique::orderByDesc('libelle')->get(),
        ]);
    }

    public function update(EtudiantRequest $request, User $etudiant, AuditService $audit)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        if ($this->isScoped() && (int)$etudiant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }

        $data = $request->validated();

        if ($this->isScoped()) {
            $data['faculte_id'] = $this->facultyId();
        }

        // Empêcher la modification du matricule par un non-décanat (mais décanat peut corriger)
        // Le matricule reste unique
        // Statut
        if (isset($data['statut'])) {
            $data['statut_inscription'] = $data['statut'];
            unset($data['statut']);
        }

        // Email nullable
        if (array_key_exists('email', $data) && empty($data['email'])) {
            $data['email'] = null;
        }

        $etudiant->update($data);

        // Mettre à jour la table miroir etudiants
        try {
            Etudiant::where('user_id', $etudiant->id)->update([
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom,
                'postnom' => $etudiant->postnom,
                'prenom' => $etudiant->prenom,
                'sexe' => $etudiant->sexe,
                'telephone' => $etudiant->telephone,
                'email' => $etudiant->email,
                'faculte_id' => $etudiant->faculte_id,
                'domaine_id' => $etudiant->domaine_id,
                'filiere_id' => $etudiant->filiere_id,
                'mention_id' => $etudiant->mention_id,
                'promotion_id' => $etudiant->promotion_id,
                'annee_academique_id' => $etudiant->annee_academique_id,
            ]);
        } catch (\Throwable $e) {}

        $audit->record('etudiant.updated', $etudiant, $request->user(), $request->except('password'));

        return redirect()->route('etudiants.index')->with('success', 'Étudiant mis à jour avec succès.');
    }

    // Désactiver au lieu de supprimer définitivement (spec)
    public function desactiver(User $etudiant, AuditService $audit)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        if ($this->isScoped() && (int)$etudiant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }

        $etudiant->update(['is_active' => false, 'status' => User::STATUS_REJECTED, 'statut_inscription' => 'inactif']);
        try {
            Etudiant::where('user_id', $etudiant->id)->update(['is_active' => false, 'statut' => 'inactif']);
        } catch (\Throwable $e) {}

        $audit->record('etudiant.desactivated', $etudiant, request()->user(), $etudiant->toArray());

        return back()->with('success', 'Étudiant désactivé avec succès.');
    }

    public function reactiver(User $etudiant, AuditService $audit)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        if ($this->isScoped() && (int)$etudiant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }

        $etudiant->update(['is_active' => true, 'status' => User::STATUS_ACCEPTED, 'statut_inscription' => 'actif']);
        try {
            Etudiant::where('user_id', $etudiant->id)->update(['is_active' => true, 'statut' => 'actif']);
        } catch (\Throwable $e) {}

        $audit->record('etudiant.reactivated', $etudiant, request()->user(), $etudiant->toArray());

        return back()->with('success', 'Étudiant réactivé avec succès.');
    }

    public function destroy(User $etudiant, AuditService $audit)
    {
        abort_unless($etudiant->isEtudiant(), 404);
        if ($this->isScoped() && (int)$etudiant->faculte_id !== (int)$this->facultyId()) {
            abort(403);
        }

        $audit->record('etudiant.deleted', $etudiant, request()->user(), $etudiant->toArray());
        try {
            Etudiant::where('user_id', $etudiant->id)->delete();
        } catch (\Throwable $e) {}
        $etudiant->delete();

        return back()->with('success', 'Étudiant supprimé définitivement.');
    }
}
