<?php

namespace App\Http\Controllers;

use App\Models\DemandeAuditoire;
use App\Services\SalleService;
use Illuminate\Http\Request;

class AttributionController extends Controller
{
    public function index(Request $request, SalleService $salles)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = DemandeAuditoire::with([
            'cours.ec',
            'ec',
            'enseignant',
            'promotion.mention.filiere.domaine.faculte',
            'auditoire.batiment',
            'createur.faculte',
        ])->latest();

        if ($request->filled('statut')) {
            $statut = $request->input('statut');
            if (in_array($statut, [DemandeAuditoire::STATUT_PENDING, DemandeAuditoire::STATUT_EN_ATTENTE], true)) {
                $query->enAttente();
            } else {
                $query->where('statut', $statut);
            }
        }

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('enseignant', fn ($e) => $e->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))
                    ->orWhereHas('promotion', fn ($p) => $p->where('nom', 'like', $term))
                    ->orWhereHas('createur', fn ($u) => $u->where('nom', 'like', $term))
                    ->orWhereHas('cours.ec', fn ($ec) => $ec->where('nom', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $demandes = $query->paginate(15)->withQueryString();

        $stats = [
            'pending' => DemandeAuditoire::enAttente()->count(),
            'accepted' => DemandeAuditoire::where('statut', DemandeAuditoire::STATUT_ACCEPTEE)->count(),
            'rejected' => DemandeAuditoire::where('statut', DemandeAuditoire::STATUT_REFUSEE)->count(),
        ];

        return view('attributions.index', compact('demandes', 'stats'));
    }
}
