@extends('layouts.app')
@section('title', 'Promotions')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">Promotions</h1><small class="text-muted">L1, L2, L3, M1, M2 — liées à une mention et une année académique.</small></div>
    <a class="btn btn-primary" href="{{ route(($routePrefix ?? 'decanat.promotions').'.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>
<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">Rechercher</label><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom…"></div>
        <div class="col-md-3">
            <label class="form-label">Mention</label>
            <select name="mention_id" class="form-select">
                <option value="">Toutes</option>
                @foreach($mentions as $mention)
                    <option value="{{ $mention->id }}" @selected(request('mention_id')==$mention->id)>{{ $mention->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Année</label>
            <select name="annee_academique_id" class="form-select">
                <option value="">Toutes</option>
                @foreach($annees as $annee)
                    <option value="{{ $annee->id }}" @selected(request('annee_academique_id')==$annee->id)>{{ $annee->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Niveau</label>
            <select name="niveau" class="form-select">
                <option value="">Tous</option>
                @foreach(\App\Models\Promotion::NIVEAUX as $niveau)
                    <option value="{{ $niveau }}" @selected(request('niveau')===$niveau)>{{ $niveau }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i></button><a href="{{ route(($routePrefix ?? 'decanat.promotions').'.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a></div>
    </div>
</form>
<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Promotion</th><th>Niveau</th><th>Mention</th><th>Année</th><th>Effectif</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($promotions as $promotion)
            <tr>
                <td class="fw-semibold">{{ $promotion->nom }}</td>
                <td><span class="badge text-bg-light">{{ $promotion->niveau }}</span></td>
                <td>{{ optional($promotion->mention)->nom }}</td>
                <td>{{ optional($promotion->anneeAcademique)->libelle }}</td>
                <td>{{ $promotion->effectif }}</td>
                <td><span class="badge text-bg-{{ $promotion->actif ? 'success' : 'secondary' }}">{{ $promotion->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    @if(($routePrefix ?? '') === 'decanat.promotions')
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.promotions.show', $promotion) }}"><i class="bi bi-eye"></i></a>
                    @endif
                    <a class="btn btn-sm btn-outline-primary" href="{{ route(($routePrefix ?? 'decanat.promotions').'.edit', $promotion) }}"><i class="bi bi-pencil"></i></a>
                    @if(($routePrefix ?? '') === 'decanat.promotions')
                        <form method="POST" action="{{ route('decanat.promotions.toggle', $promotion) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-toggle2-on"></i></button></form>
                    @endif
                    <form method="POST" action="{{ route(($routePrefix ?? 'decanat.promotions').'.destroy', $promotion) }}" class="d-inline" onsubmit="return confirm('Supprimer cette promotion ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucune promotion.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $promotions->links() }}</div>
</div>
@endsection
