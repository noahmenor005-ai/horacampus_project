@extends('layouts.app')

@section('title', 'Demande de salle')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
    <h1 class="h3 mb-0">Demande de salle</h1>
    <div class="d-flex gap-2">
        @can('update', $demande)
            <a class="btn btn-outline-primary" href="{{ route('demandes.edit', $demande) }}"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
        <a class="btn btn-outline-secondary" href="{{ route('demandes.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>
</div>

@if($demande->motif_refus)
    <div class="alert alert-danger">
        <i class="bi bi-x-circle"></i> <strong>Demande refusée.</strong> Motif : {{ $demande->motif_refus }}
    </div>
@endif

<div class="surface p-4 mb-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h2 class="h5 mb-0">{{ $demande->cours?->intitule }}</h2>
        <span class="badge text-bg-{{ $demande->badgeClass() }}">{{ $demande->statutLabel() }}</span>
    </div>
    <dl class="row mb-0">
        <dt class="col-sm-3">EC</dt>
        <dd class="col-sm-9">{{ $demande->cours?->ec?->nom ?: '—' }}</dd>

        <dt class="col-sm-3">Type</dt>
        <dd class="col-sm-9">{{ $demande->cours?->typeLabel() ?: '—' }}</dd>

        <dt class="col-sm-3">Promotion</dt>
        <dd class="col-sm-9">{{ $demande->promotion?->nom ?: '—' }}</dd>

        <dt class="col-sm-3">Enseignant</dt>
        <dd class="col-sm-9">{{ $demande->enseignant?->nom_complet ?: '—' }}</dd>

        <dt class="col-sm-3">Date</dt>
        <dd class="col-sm-9">{{ $demande->date?->format('d/m/Y') ?: '—' }}</dd>

        <dt class="col-sm-3">Horaire</dt>
        <dd class="col-sm-9">{{ substr($demande->heure_debut, 0, 5) }} - {{ substr($demande->heure_fin, 0, 5) }}</dd>

        <dt class="col-sm-3">Effectif attendu</dt>
        <dd class="col-sm-9">{{ $demande->effectif_attendu }}</dd>

        <dt class="col-sm-3">Semestre</dt>
        <dd class="col-sm-9">{{ $demande->semestre?->libelle ?: '—' }}</dd>

        <dt class="col-sm-3">Créée par</dt>
        <dd class="col-sm-9">{{ $demande->createur?->nom_complet ?: '—' }}</dd>

        @if($demande->note)
            <dt class="col-sm-3">Note</dt>
            <dd class="col-sm-9">{{ $demande->note }}</dd>
        @endif
    </dl>

    @if($demande->auditoire)
        <div class="alert alert-success mt-3 mb-0">
            <i class="bi bi-door-open"></i> Salle attribuée :
            <strong>{{ $demande->auditoire->nom }}</strong>
            @if($demande->auditoire->batiment)
                ({{ $demande->auditoire->batiment->nom }})
            @endif
            — capacité {{ $demande->auditoire->capacite }} places.
        </div>
    @endif
</div>

@if(auth()->user()->isAdmin())
    <div class="surface p-4">
        <h2 class="h5 mb-3">Actions administratives</h2>
        <a class="btn btn-outline-primary mb-3" href="{{ route('demandes.salles', $demande) }}"><i class="bi bi-door-open"></i> Salles disponibles</a>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100">
                    <h3 class="h6">Accepter et programmer</h3>
                    <form method="POST" action="{{ route('demandes.status', $demande) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="statut" value="acceptee">
                        <label class="form-label">Salle</label>
                        <select name="auditoire_id" class="form-select mb-3" required>
                            <option value="">Choisir une salle...</option>
                            @foreach($sallesDisponibles as $salle)
                                <option value="{{ $salle->id }}" @selected(old('auditoire_id', $demande->auditoire_id) == $salle->id)>{{ $salle->nom }} — {{ optional($salle->batiment)->nom }} ({{ $salle->capacite }} places)</option>
                            @endforeach
                        </select>
                        <button class="btn btn-success"><i class="bi bi-check2-circle"></i> Accepter et programmer</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100">
                    <h3 class="h6">Refuser</h3>
                    <form method="POST" action="{{ route('demandes.status', $demande) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="statut" value="refusee">
                        <label class="form-label">Motif du refus</label>
                        <textarea name="motif_refus" rows="2" class="form-control mb-3" placeholder="Motif..." required></textarea>
                        <button class="btn btn-danger"><i class="bi bi-x-circle"></i> Refuser</button>
                    </form>
                </div>
            </div>
        </div>

        @if($demande->statut !== \App\Models\DemandeAuditoire::STATUT_EN_ATTENTE)
            <div class="mt-3">
                <form method="POST" action="{{ route('demandes.status', $demande) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="statut" value="en_attente">
                    <button class="btn btn-outline-warning"><i class="bi bi-arrow-counterclockwise"></i> Remettre en attente</button>
                </form>
            </div>
        @endif
    </div>
@endif
@endsection
