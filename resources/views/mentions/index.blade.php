@extends('layouts.app')
@section('title', 'Mentions')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">Mentions</h1><small class="text-muted">Une mention est liée à une filière de votre faculté.</small></div>
    <a class="btn btn-primary" href="{{ route('decanat.mentions.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>
<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5"><label class="form-label">Rechercher</label><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom de la mention…"></div>
        <div class="col-md-3">
            <label class="form-label">Filière</label>
            <select name="filiere_id" class="form-select">
                <option value="">Toutes</option>
                @foreach($filieres as $filiere)
                    <option value="{{ $filiere->id }}" @selected(request('filiere_id')==$filiere->id)>{{ $filiere->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Statut</label>
            <select name="actif" class="form-select">
                <option value="">Tous</option>
                <option value="1" @selected(request('actif')==='1')>Actif</option>
                <option value="0" @selected(request('actif')==='0')>Inactif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button><a href="{{ route('decanat.mentions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a></div>
    </div>
</form>
<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Mention</th><th>Filière</th><th>Promotions</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($mentions as $mention)
            <tr>
                <td class="fw-semibold">{{ $mention->nom }}</td>
                <td>{{ optional($mention->filiere)->nom }}</td>
                <td>{{ $mention->promotions_count }}</td>
                <td><span class="badge text-bg-{{ $mention->actif ? 'success' : 'secondary' }}">{{ $mention->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.mentions.show', $mention) }}"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('decanat.mentions.edit', $mention) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('decanat.mentions.toggle', $mention) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-toggle2-on"></i></button></form>
                    <form method="POST" action="{{ route('decanat.mentions.destroy', $mention) }}" class="d-inline" onsubmit="return confirm('Supprimer cette mention ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Aucune mention.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $mentions->links() }}</div>
</div>
@endsection
