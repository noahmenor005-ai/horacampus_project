@extends('layouts.app')
@section('title', 'Semestres')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">Semestres</h1><small class="text-muted">Semestre 1 et Semestre 2 liés à une année académique.</small></div>
    <a class="btn btn-primary" href="{{ route('decanat.semestres.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>
<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher…"></div>
        <div class="col-md-4">
            <select name="annee_academique_id" class="form-select">
                <option value="">Toutes les années</option>
                @foreach($annees as $annee)
                    <option value="{{ $annee->id }}" @selected(request('annee_academique_id')==$annee->id)>{{ $annee->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary flex-fill">Filtrer</button><a href="{{ route('decanat.semestres.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </div>
</form>
<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Semestre</th><th>Année</th><th>Période</th><th>UE</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($semestres as $semestre)
            <tr>
                <td class="fw-semibold">{{ $semestre->libelle }}</td>
                <td>{{ optional($semestre->anneeAcademique)->libelle }}</td>
                <td>{{ $semestre->date_debut?->format('d/m/Y') }} — {{ $semestre->date_fin?->format('d/m/Y') }}</td>
                <td>{{ $semestre->ues_count }}</td>
                <td><span class="badge text-bg-{{ $semestre->actif ? 'success' : 'secondary' }}">{{ $semestre->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.semestres.show', $semestre) }}"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('decanat.semestres.edit', $semestre) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('decanat.semestres.toggle', $semestre) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-toggle2-on"></i></button></form>
                    <form method="POST" action="{{ route('decanat.semestres.destroy', $semestre) }}" class="d-inline" onsubmit="return confirm('Supprimer ce semestre ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucun semestre.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $semestres->links() }}</div>
</div>
@endsection
