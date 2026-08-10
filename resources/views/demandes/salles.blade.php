@extends('layouts.app')

@section('title', 'Salles disponibles')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Salles disponibles</h1>
        <small class="text-muted">{{ $demande->cours?->intitule }} — {{ $demande->date?->format('d/m/Y') }} — {{ substr($demande->heure_debut, 0, 5) }} - {{ substr($demande->heure_fin, 0, 5) }} — {{ $demande->effectif_attendu }} personnes</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('demandes.show', $demande) }}"><i class="bi bi-arrow-left"></i> Retour à la demande</a>
</div>

<div class="row g-3">
    @forelse($salles as $salle)
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h5 mb-1">{{ $salle->nom }}</h2>
                            <div class="text-muted small mb-2">
                                @if($salle->batiment)<i class="bi bi-building"></i> {{ $salle->batiment->nom }} — @endif
                                <span class="badge text-bg-light">{{ $salle->typeLabel() }}</span>
                                <span class="badge text-bg-light">{{ $salle->capacite }} places</span>
                            </div>
                            @if($salle->equipements)
                                <p class="small mb-0"><i class="bi bi-tools"></i> {{ $salle->equipements }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('demandes.status', $demande) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut" value="acceptee">
                            <input type="hidden" name="auditoire_id" value="{{ $salle->id }}">
                            <button class="btn btn-sm btn-success"><i class="bi bi-check2-circle"></i> Attribuer cette salle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> Aucune salle disponible sur ce créneau.
                @if(auth()->user()->isAdmin())
                    <a class="alert-link" href="{{ route('auditoires.index') }}">Gérer les auditoires</a>
                @endif
            </div>
        </div>
    @endforelse
</div>
@endsection
