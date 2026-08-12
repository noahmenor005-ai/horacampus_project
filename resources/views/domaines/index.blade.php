@extends('layouts.app')

@section('title', 'Domaines')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Domaines</h1>
        <small class="text-muted">Organisation académique — uniquement les domaines de votre faculté.</small>
    </div>
    <a class="btn btn-primary" href="{{ route('decanat.domaines.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>

<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Rechercher</label>
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom du domaine…">
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="actif" class="form-select">
                <option value="">Tous</option>
                <option value="1" @selected(request('actif')==='1')>Actif</option>
                <option value="0" @selected(request('actif')==='0')>Inactif</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> Rechercher</button>
            <a href="{{ route('decanat.domaines.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th>Nom</th>
            <th>Faculté</th>
            <th>Filières</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($domaines as $domaine)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $domaine->nom }}</div>
                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($domaine->description, 80) }}</small>
                </td>
                <td>{{ optional($domaine->faculte)->nom }}</td>
                <td>{{ $domaine->filieres_count }}</td>
                <td><span class="badge text-bg-{{ $domaine->actif ? 'success' : 'secondary' }}">{{ $domaine->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.domaines.show', $domaine) }}" title="Voir"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('decanat.domaines.edit', $domaine) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('decanat.domaines.toggle', $domaine) }}" class="d-inline">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-warning" title="Activer/Désactiver"><i class="bi bi-toggle2-on"></i></button>
                    </form>
                    <form method="POST" action="{{ route('decanat.domaines.destroy', $domaine) }}" class="d-inline" onsubmit="return confirm('Supprimer ce domaine ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Aucun domaine dans votre faculté.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $domaines->links() }}</div>
</div>
@endsection
