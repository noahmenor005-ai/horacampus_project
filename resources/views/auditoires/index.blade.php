@extends('layouts.app')

@section('title', 'Auditoires')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Auditoires</h1>
        <small class="text-muted">Salles, laboratoires et capacités</small>
    </div>
    <a class="btn btn-primary" href="{{ route('auditoires.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>

<form class="surface p-3 mb-3" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Numéro, bâtiment, équipement…"></div>
        <div class="col-md-3">
            <select name="batiment_id" class="form-select">
                <option value="">Tous les bâtiments</option>
                @foreach($batiments as $b)
                    <option value="{{ $b->id }}" @selected(request('batiment_id')==$b->id)>{{ $b->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                @foreach(\App\Models\Auditoire::TYPES as $k => $v)
                    @if($k !== 'attente')<option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>@endif
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="etat" class="form-select">
                <option value="">Tous les états</option>
                @foreach(\App\Models\Auditoire::ETATS as $k => $v)
                    <option value="{{ $k }}" @selected(request('etat')===$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Numéro</th><th>Bâtiment</th><th>Capacité</th><th>Type</th><th>Équipements</th><th>État</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td class="fw-semibold">{{ $item->numero ?: $item->nom }}</td>
                <td>{{ optional($item->batiment)->nom }}</td>
                <td>{{ $item->capacite }}</td>
                <td>{{ $item->typeLabel() }}</td>
                <td class="small">{{ $item->equipements ?: '—' }}</td>
                <td><span class="badge text-bg-{{ $item->etat === 'disponible' ? 'success' : ($item->etat === 'maintenance' ? 'warning' : 'secondary') }}">{{ $item->etatLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('auditoires.edit', $item) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('auditoires.destroy', $item) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun auditoire.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
