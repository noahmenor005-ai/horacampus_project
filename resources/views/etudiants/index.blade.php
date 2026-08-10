@extends('layouts.app')

@section('title', 'Étudiants')

@section('content')
@php
    $statusLabels = ['pending' => 'En attente', 'accepted' => 'Accepté', 'rejected' => 'Refusé'];
    $statusBadges = ['pending' => 'text-bg-warning', 'accepted' => 'text-bg-success', 'rejected' => 'text-bg-danger'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Étudiants</h1>
        <small class="text-muted">Corps étudiant de la plateforme</small>
    </div>
    <a class="btn btn-primary" href="{{ route('etudiants.create') }}"><i class="bi bi-plus-lg"></i> Enregistrer un étudiant</a>
</div>

<form method="GET" action="{{ route('etudiants.index') }}" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher par nom, prénom ou e-mail…">
        </div>
        <div class="col-md-2">
            <select name="faculte_id" class="form-select">
                <option value="">Toutes les facultés</option>
                @foreach($facultes as $f)
                    <option value="{{ $f->id }}" @selected(request('faculte_id') == $f->id)>{{ $f->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="promotion_id" class="form-select">
                <option value="">Toutes les promotions</option>
                @foreach($promotions as $p)
                    <option value="{{ $p->id }}" @selected(request('promotion_id') == $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i></button>
            @if(request()->has('q') || request()->filled('faculte_id') || request()->filled('promotion_id') || request()->filled('status'))
                <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Faculté</th>
            <th>Filière</th>
            <th>Mention</th>
            <th>Promotion</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($etudiants as $e)
            <tr>
                <td class="fw-semibold">{{ $e->nom_complet }}</td>
                <td>{{ $e->email }}</td>
                <td>{{ optional($e->faculte)->nom ?: '-' }}</td>
                <td>{{ optional(optional(optional($e->promotion)->mention)->filiere)->nom ?: '-' }}</td>
                <td>{{ optional(optional($e->promotion)->mention)->nom ?: '-' }}</td>
                <td>{{ optional($e->promotion)->nom ?: '-' }}</td>
                <td><span class="badge {{ $statusBadges[$e->status] ?? 'text-bg-secondary' }}">{{ $e->statusLabel() }}</span></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('etudiants.edit', $e) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('etudiants.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Supprimer cet étudiant ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucun étudiant trouvé.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $etudiants->links() }}</div>
</div>
@endsection
