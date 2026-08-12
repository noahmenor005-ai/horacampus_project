@extends('layouts.app')
@section('title', 'Éléments constitutifs')
@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div><h1 class="h3 mb-0">Éléments constitutifs</h1><small class="text-muted">Un EC appartient obligatoirement à une UE.</small></div>
    <a class="btn btn-primary" href="{{ route('decanat.ecs.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>
<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Code ou intitulé…"></div>
        <div class="col-md-5">
            <select name="ue_id" class="form-select">
                <option value="">Toutes les UE</option>
                @foreach($ues as $ue)
                    <option value="{{ $ue->id }}" @selected(request('ue_id')==$ue->id)>{{ $ue->code }} — {{ $ue->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill">Filtrer</button><a href="{{ route('decanat.ecs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a></div>
    </div>
</form>
<div class="surface p-0 table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>Code</th><th>Intitulé</th><th>Heures</th><th>UE</th><th>Enseignant</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($ecs as $ec)
            <tr>
                <td><span class="badge text-bg-dark font-monospace">{{ $ec->code }}</span></td>
                <td class="fw-semibold">{{ $ec->nom }}</td>
                <td>{{ $ec->volume_horaire }} h</td>
                <td>{{ optional($ec->ue)->code }}</td>
                <td>{{ optional($ec->enseignant)->nom_complet ?: '—' }}</td>
                <td><span class="badge text-bg-{{ $ec->estActif() ? 'success' : 'secondary' }}">{{ $ec->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.ecs.show', $ec) }}"><i class="bi bi-eye"></i></a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('decanat.ecs.edit', $ec) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('decanat.ecs.toggle', $ec) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-toggle2-on"></i></button></form>
                    <form method="POST" action="{{ route('decanat.ecs.destroy', $ec) }}" class="d-inline" onsubmit="return confirm('Supprimer cet EC ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun EC.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $ecs->links() }}</div>
</div>
@endsection
