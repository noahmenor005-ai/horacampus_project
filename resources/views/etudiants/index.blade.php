@extends('layouts.app')

@section('title', 'Gestion des étudiants')

@section('content')
@php
    $statusLabels = ['pending' => 'En attente', 'accepted' => 'Accepté', 'rejected' => 'Refusé'];
    $statusBadges = ['pending' => 'text-bg-warning', 'accepted' => 'text-bg-success', 'rejected' => 'text-bg-danger'];
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Gestion des étudiants — {{ optional(auth()->user()->faculte)->nom }}</h1>
        <small class="text-muted">Seul le Décanat peut enregistrer, modifier, consulter, désactiver et rechercher les étudiants de sa faculté.</small>
    </div>
    <a class="btn btn-primary" href="{{ route('etudiants.create') }}"><i class="bi bi-person-plus"></i> Ajouter un étudiant</a>
</div>

<form method="GET" action="{{ route('etudiants.index') }}" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small text-muted">Rechercher</label>
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, postnom, prénom, matricule, email…">
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Promotion</label>
            <select name="promotion_id" class="form-select">
                <option value="">Toutes</option>
                @foreach($promotions as $p)
                    <option value="{{ $p->id }}" @selected(request('promotion_id') == $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Sexe</label>
            <select name="sexe" class="form-select">
                <option value="">Tous</option>
                <option value="M" @selected(request('sexe')==='M')>M</option>
                <option value="F" @selected(request('sexe')==='F')>F</option>
                <option value="Autre" @selected(request('sexe')==='Autre')>Autre</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">Statut</label>
            <select name="is_active" class="form-select">
                <option value="">Tous</option>
                <option value="1" @selected(request('is_active')==='1')>Actif</option>
                <option value="0" @selected(request('is_active')==='0')>Désactivé</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Filtrer</button>
            @if(request()->has('q') || request()->filled('promotion_id') || request()->filled('sexe') || request()->filled('is_active'))
                <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </div>
</form>

<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th>Matricule</th>
            <th>Nom complet</th>
            <th>Sexe</th>
            <th>Téléphone</th>
            <th>Promotion</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($etudiants as $e)
            <tr>
                <td><span class="badge text-bg-dark font-monospace">{{ $e->matricule }}</span></td>
                <td>
                    <div class="fw-semibold">{{ $e->nom }} {{ $e->postnom }} {{ $e->prenom }}</div>
                    <small class="text-muted">{{ $e->email ?: '—' }}</small>
                </td>
                <td>{{ $e->sexe ?: '-' }}</td>
                <td>{{ $e->telephone ?: '-' }}</td>
                <td>
                    <div class="small">{{ optional($e->promotion)->nom ?: '-' }}</div>
                    <small class="text-muted">{{ optional(optional($e->promotion)->mention)->nom ?: '' }}</small>
                </td>
                <td>
                    @if($e->is_active)
                        <span class="badge text-bg-success">Actif</span>
                    @else
                        <span class="badge text-bg-danger">Désactivé</span>
                    @endif
                    <small class="d-block text-muted">{{ $statusLabels[$e->status] ?? $e->status }}</small>
                </td>
                <td class="text-end">
                    <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('etudiants.show', $e) }}" title="Consulter"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('etudiants.edit', $e) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                        @if($e->is_active)
                            <form method="POST" action="{{ route('etudiants.desactiver', $e) }}" class="d-inline" onsubmit="return confirm('Désactiver cet étudiant ? Il ne pourra plus se connecter.')">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning" title="Désactiver"><i class="bi bi-pause-circle"></i></button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('etudiants.reactiver', $e) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-success" title="Réactiver"><i class="bi bi-play-circle"></i></button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('etudiants.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Supprimer définitivement ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun étudiant dans votre faculté.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $etudiants->links() }}</div>
</div>
@endsection
