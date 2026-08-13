<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $settings = [
            'nom_universite' => Setting::get('nom_universite', 'HoraCampus University'),
            'email_contact' => Setting::get('email_contact', 'noahmenor005@gmail.com'),
            'telephone' => Setting::get('telephone', '+243 999 000 000'),
            'adresse' => Setting::get('adresse', 'Campus principal'),
            'heure_debut_journee' => Setting::get('heure_debut_journee', '08:00'),
            'heure_fin_journee' => Setting::get('heure_fin_journee', '18:00'),
            'message_accueil' => Setting::get('message_accueil', 'Gestion intelligente des horaires et des auditoires universitaires'),
        ];

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $data = $request->validate([
            'nom_universite' => ['required', 'string', 'max:150'],
            'email_contact' => ['required', 'email', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'heure_debut_journee' => ['required', 'date_format:H:i'],
            'heure_fin_journee' => ['required', 'date_format:H:i'],
            'message_accueil' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
