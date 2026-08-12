@extends('layouts.app')
@section('title', 'Unités d\'enseignement')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">Unités d'enseignement</h1><small class="text-muted">Code, intitulé, crédits, mention, semestre.</small></div>
    <a class="btn btn-primary" href="{{ route('decanat.ues.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>
<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Code ou intitulé…"></div>
        <div class="col-md-3">
            <select name="promotion_id" class="form-select">
                <option value="">Toutes les promotions</option>
                @foreach($promotions as $promotion)
                    <option value="{{ $promotion->id }}" @selected(request('promotion_id')==$promotion->id)>{{ $promotion->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="semestre_id" class="form-select">
                <option value="">Tous les semestres</option>
                @foreach($semestres as $semestre)
                    <option value="{{ $semestre->id }}" @selected(request('semestre_id')==$semestre->id)>{{ $semestre->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill">Filtrer</button><a href="{{ route('decanat.ues.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a></div>
    </div>
</form>
<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Code</th><th>Intitulé</th><th>Crédits</th><th>Promotion</th><th>Semestre</th><th>EC</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($ues as $ue)
            <tr>
                <td><span class="badge text-bg-dark font-monospace">{{ $ue->code }}</span></td>
                <td class="fw-semibold">{{ $ue->nom }}</td>
                <td>{{ $ue->credit }}</td>
                <td>{{ optional($ue->promotion)->nom }}</td>
                <td>{{ optional($ue->semestre)->libelle }}</td>
                <td>{{ $ue->ecs_count }}</td>
                <td><span class="badge text-bg-{{ $ue->estActif() ? 'success' : 'secondary' }}">{{ $ue->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.ues.show', $ue) }}"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('decanat.ues.edit', $ue) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('decanat.ues.toggle', $ue) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-toggle2-on"></i></button></form>
                    <form method="POST" action="{{ route('decanat.ues.destroy', $ue) }}" class="d-inline" onsubmit="return confirm('Supprimer cette UE ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucune UE.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $ues->links() }}</div>
</div>
@endsection
