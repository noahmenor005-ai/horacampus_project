@extends('layouts.app')

@section('title', 'Détail de l\'horaire')

@php
    $isDecanat = auth()->user()->isDecanat();
    $back = $isDecanat ? route('decanat.horaires.index') : route('horaires.index');
    $edit = $isDecanat ? route('decanat.horaires.edit', $horaire) : route('horaires.edit', $horaire);
    $ask = $isDecanat ? route('decanat.horaires.demander-salle', $horaire) : route('horaires.demander-salle', $horaire);
@endphp

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ optional($horaire->ec)->nom ?: optional($horaire->cours)->intitule }}</h1>
        <small class="text-muted">{{ $horaire->jour }} {{ $horaire->date?->format('d/m/Y') }} · {{ substr($horaire->heure_debut,0,5) }}-{{ substr($horaire->heure_fin,0,5) }}</small>
    </div>
    <div class="d-flex gap-2">
        @can('update', $horaire)
            <a class="btn btn-outline-primary" href="{{ $edit }}"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
        <a class="btn btn-outline-secondary" href="{{ $back }}">Retour</a>
    </div>
</div>

<div class="surface p-4 mb-3">
    <dl class="row mb-0">
        <dt class="col-sm-3">Promotion</dt><dd class="col-sm-9">{{ optional($horaire->promotion)->nom }}</dd>
        <dt class="col-sm-3">UE / EC</dt><dd class="col-sm-9">{{ optional($horaire->ue)->nom ?: '—' }} / {{ optional($horaire->ec)->nom ?: optional(optional($horaire->cours)->ec)->nom }}</dd>
        <dt class="col-sm-3">Enseignant</dt><dd class="col-sm-9">{{ optional($horaire->enseignant)->nom_complet }}</dd>
        <dt class="col-sm-3">Semestre</dt><dd class="col-sm-9">{{ optional($horaire->semestre)->libelle ?: '—' }}</dd>
        <dt class="col-sm-3">Effectif</dt><dd class="col-sm-9">{{ $horaire->effectif_attendu ?: optional($horaire->promotion)->effectif }}</dd>
        <dt class="col-sm-3">Salle</dt><dd class="col-sm-9">{{ $horaire->hasSalle() ? optional($horaire->auditoire)->nom : 'Non attribuée' }}</dd>
        <dt class="col-sm-3">Statut</dt><dd class="col-sm-9"><span class="badge text-bg-{{ $horaire->badgeClass() }}">{{ $horaire->statutLabel() }}</span></dd>
    </dl>
</div>

@can('create', \App\Models\DemandeAuditoire::class)
    @if(!$horaire->hasSalle())
        <div class="surface p-4">
            <h2 class="h5">Demander une salle</h2>
            @if($horaire->demandeEnAttente())
                <div class="alert alert-warning mb-0">Une demande est déjà en attente pour cet horaire.</div>
            @else
                <form method="POST" action="{{ $ask }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentaire" rows="3" class="form-control" placeholder="Précisions pour l'administration…"></textarea>
                    </div>
                    <input type="hidden" name="effectif_attendu" value="{{ $horaire->effectif_attendu ?: optional($horaire->promotion)->effectif }}">
                    <button class="btn btn-success"><i class="bi bi-door-open"></i> Demander une salle</button>
                </form>
            @endif
        </div>
    @endif
@endcan
@endsection
