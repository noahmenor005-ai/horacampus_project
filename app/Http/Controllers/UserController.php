<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Faculte;
use App\Models\Horaire;
use App\Models\Promotion;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        $query = User::with(['faculte', 'promotion'])
            ->where('id', '!=', auth()->id())
            ->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(fn ($q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)->orWhere('email', 'like', $term));
        }

        $users = $query->paginate(12)->withQueryString();

        $stats = [
            'en_attente' => User::where('status', User::STATUS_PENDING)->count(),
            'acceptes' => User::where('status', User::STATUS_ACCEPTED)->count(),
            'refuses' => User::where('status', User::STATUS_REJECTED)->count(),
            'enseignants' => User::where('role', User::ROLE_ENSEIGNANT)->count(),
            'etudiants' => User::where('role', User::ROLE_ETUDIANT)->count(),
            'decanats' => User::where('role', User::ROLE_DECANAT)->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.form', [
            'user' => new User(),
            'facultes' => Faculte::orderBy('nom')->get(),
        ]);
    }

    public function store(UserRequest $request, AuditService $audit, NotificationService $notifications)
    {
        $data = $request->validated();
        $plain = $data['password'] ?? 'password';
        $data['password'] = Hash::make($plain);
        $data['is_active'] = true;
        $data['statut_inscription'] = 'actif';

        $user = User::create($data);
        $audit->record('user.created', $user, $request->user(), $request->validated());

        if ($user->isAccepted()) {
            $notifications->notifyUser($user, 'Compte activé', 'Votre compte HoraCampus a été activé par l\'administrateur.');
        }

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.form', [
            'user' => $user,
            'facultes' => Faculte::orderBy('nom')->get(),
            'promotions' => Promotion::orderBy('nom')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user, AuditService $audit)
    {
        $this->authorize('update', $user);
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        $audit->record('user.updated', $user, $request->user(), $request->except('password'));

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function updateStatus(Request $request, User $user, NotificationService $notifications, AuditService $audit)
    {
        $this->authorize('valider', $user);

        $request->validate([
            'status' => ['required', Rule::in(User::STATUSES)],
        ]);

        $user->update(['status' => $request->input('status')]);
        $audit->record('user.status.updated', $user, $request->user(), ['status' => $request->input('status')]);

        $message = [
            User::STATUS_ACCEPTED => 'Votre compte a été accepté. Vous pouvez maintenant vous connecter à HoraCampus.',
            User::STATUS_REJECTED => 'Votre compte a été refusé par l\'administrateur.',
            User::STATUS_PENDING => 'Votre compte a été replacé en attente de validation.',
        ][$request->input('status')];

        $notifications->notifyUser($user, 'Mise à jour du compte', $message);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(User $user, AuditService $audit)
    {
        $this->authorize('delete', $user);
        $audit->record('user.deleted', $user, request()->user(), $user->toArray());
        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }

    public function decanats(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('faculte')
            ->where('role', User::ROLE_DECANAT)
            ->latest();

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(fn ($q) => $q->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)->orWhere('email', 'like', $term));
        }

        $users = $query->paginate(12)->withQueryString();

        return view('users.decanats', compact('users'));
    }
}
