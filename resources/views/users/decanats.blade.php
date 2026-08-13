@extends('layouts.app')

@section('title', 'Décanats')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Comptes Décanat</h1>
        <small class="text-muted">Chaque Décanat appartient à une faculté</small>
    </div>
    <a class="btn btn-primary" href="{{ route('users.create', ['role' => 'decanat']) }}"><i class="bi bi-plus-lg"></i> Créer un Décanat</a>
</div>

<form class="surface p-3 mb-3" method="GET">
    <div class="input-group">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, email…">
        <button class="btn btn-primary">Rechercher</button>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Nom</th><th>Email</th><th>Faculté</th><th>Téléphone</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $u)
            <tr>
                <td class="fw-semibold">{{ $u->nom_complet }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ optional($u->faculte)->nom }}</td>
                <td>{{ $u->telephone ?: '—' }}</td>
                <td><span class="badge text-bg-{{ $u->status === 'accepted' ? 'success' : 'warning' }}">{{ $u->statusLabel() }}</span></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $u) }}"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucun Décanat.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
