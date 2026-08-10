<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(ProfileRequest $request)
    {
        $data = $request->validated();

        // Sécurité : un étudiant ne doit pas pouvoir modifier son matricule, faculté, promotion, etc. même en manipulant la requête
        $filtered = collect($data)->only(['nom', 'prenom', 'postnom', 'telephone', 'photo_path'])->toArray();

        // Si l'utilisateur n'est pas étudiant, on peut autoriser nom/prenom/postnom/telephone
        // Mais on bloque toujours les champs académiques
        if ($request->hasFile('photo')) {
            $filtered['photo_path'] = $request->file('photo')->store('profiles', 'public');
        }

        // Retirer photo du tableau validé brut si présent
        unset($filtered['photo']);

        $request->user()->update($filtered);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function password(PasswordUpdateRequest $request)
    {
        if (!Hash::check($request->input('current_password'), $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $request->user()->update(['password' => Hash::make($request->validated()['password'])]);

        return back()->with('success', 'Mot de passe changé.');
    }
}
