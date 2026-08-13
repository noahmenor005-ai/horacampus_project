@extends('layouts.app')

@section('title', 'Attribution des salles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Attribution des salles</h1>
        <small class="text-muted">Accepter, refuser ou attribuer une salle compatible</small>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="surface stat-card"><div class="text-muted small">En attente</div><div class="h3 mb-0">{{ $stats['pending'] }}</div></div></div>
    <div class="col-md-4"><div class="surface stat-card"><div class="text-muted small">Acceptées</div><div class="h3 mb-0">{{ $stats['accepted'] }}</div></div></div>
    <div class="col-md-4"><div class="surface stat-card"><div class="text-muted small">Refusées</div><div class="h3 mb-0">{{ $stats['rejected'] }}</div></div></div>
</div>

<form class="surface p-3 mb-3" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Décanat, cours, enseignant, promotion…"></div>
        <div class="col-md-4">
            <select name="statut" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="pending" @selected(request('statut')==='pending')>En attente</option>
                <option value="acceptee" @selected(request('statut')==='acceptee')>Acceptée</option>
                <option value="refusee" @selected(request('statut')==='refusee')>Refusée</option>
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100">Filtrer</button></div>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th>Décanat</th><th>Faculté</th><th>Cours</th><th>Enseignant</th><th>Promotion</th>
            <th>Date</th><th>Heure</th><th>Effectif</th><th>Salle</th><th>Statut</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($demandes as $d)
            @php
                $faculte = optional(optional(optional(optional($d->promotion)->mention)->filiere)->domaine)->faculte
                    ?: optional($d->createur)->faculte;
            @endphp
            <tr>
                <td>{{ optional($d->createur)->nom_complet }}</td>
                <td>{{ optional($faculte)->code }}</td>
                <td>{{ $d->cours?->intitule ?: optional($d->ec)->nom }}</td>
                <td>{{ optional($d->enseignant)->nom_complet }}</td>
                <td>{{ optional($d->promotion)->nom }}</td>
                <td>{{ $d->date?->format('d/m/Y') }}</td>
                <td>{{ substr($d->heure_debut,0,5) }}–{{ substr($d->heure_fin,0,5) }}</td>
                <td>{{ $d->effectif_attendu }}</td>
                <td>{{ optional($d->auditoire)->nom ?: '—' }}</td>
                <td><span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></td>
                <td class="text-end"><a class="btn btn-sm btn-primary" href="{{ route('demandes.show', $d) }}">Traiter</a></td>
            </tr>
        @empty
            <tr><td colspan="11" class="text-center text-muted py-4">Aucune demande.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $demandes->links() }}</div>
</div>
@endsection
