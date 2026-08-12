@extends('layouts.app')
@section('title', 'Années académiques')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">Années académiques</h1><small class="text-muted">Une seule année peut être active à la fois.</small></div>
    <a class="btn btn-primary" href="{{ route('decanat.annees-academiques.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>
<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-6"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher 2025-2026…"></div>
        <div class="col-md-3">
            <select name="active" class="form-select">
                <option value="">Toutes</option>
                <option value="1" @selected(request('active')==='1')>Active</option>
                <option value="0" @selected(request('active')==='0')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill">Filtrer</button><a href="{{ route('decanat.annees-academiques.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </div>
</form>
<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Libellé</th><th>Début</th><th>Fin</th><th>Semestres</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($annees as $annee)
            <tr>
                <td class="fw-semibold">{{ $annee->libelle }}</td>
                <td>{{ $annee->date_debut?->format('d/m/Y') }}</td>
                <td>{{ $annee->date_fin?->format('d/m/Y') }}</td>
                <td>{{ $annee->semestres_count }}</td>
                <td><span class="badge text-bg-{{ $annee->active ? 'success' : 'secondary' }}">{{ $annee->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.annees-academiques.show', $annee) }}"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('decanat.annees-academiques.edit', $annee) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('decanat.annees-academiques.toggle', $annee) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning">{{ $annee->active ? 'Désactiver' : 'Activer' }}</button></form>
                    <form method="POST" action="{{ route('decanat.annees-academiques.destroy', $annee) }}" class="d-inline" onsubmit="return confirm('Supprimer cette année ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune année académique.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $annees->links() }}</div>
</div>
@endsection
