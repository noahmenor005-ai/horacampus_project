<?php

namespace App\Http\Controllers;

use App\Services\HoraireService;

class DecanatDashboardController extends DashboardController
{
    public function index(HoraireService $horaireService)
    {
        abort_unless(auth()->check() && auth()->user()->isDecanat(), 403, 'Accès réservé au Décanat.');

        return parent::index($horaireService);
    }

    public function faculte()
    {
        abort_unless(auth()->check() && auth()->user()->isDecanat(), 403);

        $faculte = auth()->user()->faculte()->with([
            'domaines.filieres.mentions.promotions',
            'membres' => fn ($q) => $q->whereIn('role', ['decanat', 'enseignant', 'etudiant']),
        ])->first();

        abort_unless($faculte, 404, 'Aucune faculté n\'est associée à ce compte Décanat.');

        return view('decanat.faculte', compact('faculte'));
    }
}
