@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Gestion des utilisateurs</h1>
        <small class="text-muted">Comptes et statuts de la plateforme</small>
    </div>
    <a class="btn btn-primary" href="{{ route('users.create') }}"><i class="bi bi-plus-lg"></i> Créer un utilisateur</a>
</div>

@php
    $statCards = [
        ['label' => 'En attente', 'value' => $stats['en_attente'], 'icon' => 'bi-hourglass-split'],
        ['label' => 'Acceptés', 'value' => $stats['acceptes'], 'icon' => 'bi-check-circle'],
        ['label' => 'Refusés', 'value' => $stats['refuses'], 'icon' => 'bi-x-circle'],
        ['label' => 'Enseignants', 'value' => $stats['enseignants'], 'icon' => 'bi-person-workspace'],
        ['label' => 'Étudiants', 'value' => $stats['etudiants'], 'icon' => 'bi-people'],
        ['label' => 'Décanats', 'value' => $stats['decanats'], 'icon' => 'bi-buildings'],
    ];
    $roleLabels = ['admin' => 'Administrateur', 'decanat' => 'Décanat', 'enseignant' => 'Enseignant', 'etudiant' => 'Étudiant'];
    $statusLabels = ['pending' => 'En attente', 'accepted' => 'Accepté', 'rejected' => 'Refusé'];
    $statusBadges = ['pending' => 'text-bg-warning', 'accepted' => 'text-bg-success', 'rejected' => 'text-bg-danger'];
@endphp

<div class="row g-3 mb-4">
    @foreach($statCards as $card)
        <div class="col-md-6 col-xl-2">
            <div class="surface stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">{{ $card['label'] }}</div>
                        <div class="fs-4 fw-bold">{{ $card['value'] }}</div>
                    </div>
                    <div class="stat-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ route('users.index') }}" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher par nom, prénom ou e-mail…">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">Tous les rôles</option>
                @foreach($roleLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 d-flex gap-2">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
            @if(request()->has('q') || request()->filled('role') || request()->filled('status'))
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
            <th>Téléphone</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Faculté</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $u)
            <tr>
                <td class="fw-semibold">{{ $u->nom_complet }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->telephone ?: '-' }}</td>
                <td><span class="badge text-bg-light">{{ $u->roleLabel() }}</span></td>
                <td><span class="badge {{ $statusBadges[$u->status] ?? 'text-bg-secondary' }}">{{ $u->statusLabel() }}</span></td>
                <td>{{ optional($u->faculte)->nom ?: '-' }}</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $u) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                    @if($u->isPending())
                        <form method="POST" action="{{ route('users.status', $u) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button class="btn btn-sm btn-outline-success" title="Accepter"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <form method="POST" action="{{ route('users.status', $u) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button class="btn btn-sm btn-outline-danger" title="Refuser"><i class="bi bi-x-lg"></i></button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
