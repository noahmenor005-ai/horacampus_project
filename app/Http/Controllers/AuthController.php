<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Domaine;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login', [
            'facultes' => Faculte::orderBy('nom')->get(),
        ]);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        // Tentative 1 : connexion étudiant par Nom + Matricule
        if (!empty($data['nom']) && !empty($data['matricule'])) {
            // Recherche insensible à la casse et aux espaces
            $nom = trim($data['nom']);
            $matricule = trim($data['matricule']);

            $etudiant = User::where('role', User::ROLE_ETUDIANT)
                ->whereRaw('LOWER(TRIM(nom)) = ?', [mb_strtolower($nom)])
                ->where('matricule', $matricule)
                ->first();

            if (!$etudiant) {
                return back()->withErrors(['matricule' => 'Aucun étudiant trouvé avec ce Nom et ce Matricule.'])->onlyInput('nom', 'matricule');
            }

            // Vérifier que le compte est actif
            if (!$etudiant->is_active) {
                return back()->withErrors(['matricule' => 'Votre compte étudiant est désactivé. Contactez le Décanat.'])->onlyInput('nom', 'matricule');
            }
            if ($etudiant->isRejected()) {
                return back()->withErrors(['matricule' => 'Votre compte a été désactivé.'])->onlyInput('nom', 'matricule');
            }
            if ($etudiant->isPending()) {
                return back()->withErrors(['matricule' => 'Votre compte est en attente de validation.'])->onlyInput('nom', 'matricule');
            }

            // Option : si un mot de passe est fourni, le vérifier aussi (sécurisé)
            if (!empty($data['password'])) {
                if (!Hash::check($data['password'], $etudiant->password)) {
                    return back()->withErrors(['password' => 'Mot de passe incorrect.'])->onlyInput('nom', 'matricule');
                }
            }

            Auth::login($etudiant, $request->boolean('remember'));
            $request->session()->regenerate();
            $etudiant->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'));
        }

        // Tentative 2 : connexion classique par email + password (admin, décanat, enseignant, et aussi étudiant si email renseigné)
        if (!empty($data['email']) && !empty($data['password'])) {
            if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
                return back()->withErrors(['email' => 'Identifiants invalides.'])->onlyInput('email');
            }

            $request->session()->regenerate();

            $user = $request->user();

            // Les étudiants authentifiés par email doivent aussi être actifs
            if ($user->isEtudiant()) {
                if (!$user->is_active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Votre compte étudiant est désactivé.']);
                }
                if ($user->isRejected()) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Votre compte a été désactivé.']);
                }
            }

            if ($user->isPending()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte est en attente de validation par l\'administrateur.']);
            }

            if ($user->isRejected()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte a été refusé. Contactez l\'administration.']);
            }

            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte est désactivé.']);
            }

            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Veuillez fournir soit Email+Mot de passe, soit Nom+Matricule.'])->onlyInput('email', 'nom');
    }

    // API pour les listes dépendantes (faculté -> domaine -> filière -> mention -> promotion)
    public function domainesParFaculte(int $faculteId): JsonResponse
    {
        // Filtrage côté serveur : si décanat, ne renvoyer que sa faculté
        if (auth()->check() && auth()->user()->isDecanat() && (int)auth()->user()->faculte_id !== $faculteId) {
            return response()->json([], 403);
        }
        return response()->json(Domaine::where('faculte_id', $faculteId)->orderBy('nom')->get(['id', 'nom']));
    }

    public function filieresParDomaine(int $domaineId): JsonResponse
    {
        $query = Filiere::where('domaine_id', $domaineId)->orderBy('nom');
        if (auth()->check() && auth()->user()->isDecanat()) {
            $query->whereHas('domaine', fn($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }
        return response()->json($query->get(['id', 'nom']));
    }

    public function mentionsParFiliere(int $filiereId): JsonResponse
    {
        $query = Mention::where('filiere_id', $filiereId)->orderBy('nom');
        if (auth()->check() && auth()->user()->isDecanat()) {
            $query->whereHas('filiere.domaine', fn($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }
        return response()->json($query->get(['id', 'nom']));
    }

    public function promotionsParMention(int $mentionId): JsonResponse
    {
        $query = Promotion::where('mention_id', $mentionId)->orderBy('nom');
        if (auth()->check() && auth()->user()->isDecanat()) {
            $query->whereHas('mention.filiere.domaine', fn($q) => $q->where('faculte_id', auth()->user()->faculte_id));
        }
        return response()->json($query->get(['id', 'nom', 'effectif']));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgotPasswordForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Lien de réinitialisation envoyé.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        return view('auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Mot de passe réinitialisé.')
            : back()->withErrors(['email' => __($status)]);
    }
}
