@extends('layouts.app')

@section('title', 'Bâtiments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Bâtiments</h1>
        <small class="text-muted">Infrastructure du campus</small>
    </div>
    <a class="btn btn-primary" href="{{ route('batiments.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>

<form class="surface p-3 mb-3" method="GET">
    <div class="row g-2 align-items-end">
        <div class="col-md-6"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Code, nom, localisation…"></div>
        <div class="col-md-3">
            <select name="statut" class="form-select">
                <option value="">Tous les statuts</option>
                @foreach(\App\Models\Batiment::STATUTS as $k => $v)
                    <option value="{{ $k }}" @selected(request('statut')===$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100">Rechercher</button></div>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Code</th><th>Nom</th><th>Localisation</th><th>Étages</th><th>Auditoires</th><th>Statut</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td><span class="badge text-bg-light">{{ $item->code }}</span></td>
                <td>
                    <div class="fw-semibold">{{ $item->nom }}</div>
                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($item->description, 70) }}</small>
                </td>
                <td>{{ $item->localisationLabel() }}</td>
                <td>{{ $item->nombre_etages ?? '—' }}</td>
                <td>{{ $item->auditoires_count }}</td>
                <td><span class="badge text-bg-{{ ($item->statut ?? 'actif') === 'actif' ? 'success' : 'secondary' }}">{{ $item->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('batiments.edit', $item) }}"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('batiments.destroy', $item) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun bâtiment.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
