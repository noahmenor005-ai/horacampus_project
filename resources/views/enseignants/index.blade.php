@extends('layouts.app')

@section('title', 'Gestion des enseignants')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Gestion des enseignants — {{ optional(auth()->user()->faculte)->nom }}</h1>
        <small class="text-muted">Seul le Décanat peut enregistrer, modifier, consulter, désactiver et rechercher les enseignants de sa faculté.</small>
    </div>
    <a class="btn btn-primary" href="{{ route('enseignants.create') }}"><i class="bi bi-person-plus"></i> Ajouter un enseignant</a>
</div>

<form method="GET" action="{{ route('enseignants.index') }}" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small text-muted">Rechercher</label>
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, prénom, matricule, email…">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted">Statut</label>
            <select name="is_active" class="form-select">
                <option value="">Tous</option>
                <option value="1" @selected(request('is_active')==='1')>Actif</option>
                <option value="0" @selected(request('is_active')==='0')>Désactivé</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Filtrer</button>
            @if(request()->has('q') || request()->filled('is_active'))
                <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
            <th>Email</th>
            <th>Téléphone</th>
            <th>Faculté</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($enseignants as $e)
            <tr>
                <td><span class="badge text-bg-light font-monospace">{{ $e->matricule ?: '-' }}</span></td>
                <td class="fw-semibold">{{ $e->nom_complet }}</td>
                <td>{{ $e->email }}</td>
                <td>{{ $e->telephone ?: '-' }}</td>
                <td>{{ optional($e->faculte)->nom ?: '-' }}</td>
                <td>
                    @if($e->is_active)
                        <span class="badge text-bg-success">Actif</span>
                    @else
                        <span class="badge text-bg-danger">Désactivé</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('enseignants.show', $e) }}" title="Consulter"><i class="bi bi-eye"></i></a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('enseignants.edit', $e) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                        @if($e->is_active)
                            <form method="POST" action="{{ route('enseignants.desactiver', $e) }}" class="d-inline" onsubmit="return confirm('Désactiver cet enseignant ?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning" title="Désactiver"><i class="bi bi-pause-circle"></i></button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('enseignants.reactiver', $e) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-success" title="Réactiver"><i class="bi bi-play-circle"></i></button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('enseignants.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Supprimer définitivement ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun enseignant dans votre faculté.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $enseignants->links() }}</div>
</div>
@endsection
