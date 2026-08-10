<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
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
        return view('auth.login');
    }

    public function registerForm()
    {
        return view('auth.register', [
            'facultes' => Faculte::orderBy('nom')->get(),
        ]);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Identifiants invalides.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        if ($user->isPending()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Votre compte est en attente de validation par l\'administrateur.']);
        }

        if ($user->isRejected()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Votre compte a été refusé. Contactez l\'administration.']);
        }

        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['status'] = User::STATUS_PENDING;

        User::create($data);

        return redirect()->route('login')->with(
            'success',
            'Votre compte a été créé et est en attente de validation par l\'administrateur.'
        );
    }

    public function domainesParFaculte(int $faculteId): JsonResponse
    {
        return response()->json(Domaine::where('faculte_id', $faculteId)->orderBy('nom')->get(['id', 'nom']));
    }

    public function filieresParDomaine(int $domaineId): JsonResponse
    {
        return response()->json(Filiere::where('domaine_id', $domaineId)->orderBy('nom')->get(['id', 'nom']));
    }

    public function mentionsParFiliere(int $filiereId): JsonResponse
    {
        return response()->json(Mention::where('filiere_id', $filiereId)->orderBy('nom')->get(['id', 'nom']));
    }

    public function promotionsParMention(int $mentionId): JsonResponse
    {
        return response()->json(Promotion::where('mention_id', $mentionId)->orderBy('nom')->get(['id', 'nom', 'effectif']));
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
