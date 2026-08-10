@extends('layouts.app')

@section('title', 'Cours')

@section('content')
@php $types = \App\Models\Cours::TYPES; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Cours</h1>
        <small class="text-muted">Assignation des cours par promotion</small>
    </div>
    <a class="btn btn-primary" href="{{ route('cours.create') }}"><i class="bi bi-plus-lg"></i> Créer un cours</a>
</div>

<form method="GET" action="{{ route('cours.index') }}" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher par EC ou enseignant…">
        </div>
        <div class="col-md-3">
            <select name="promotion_id" class="form-select">
                <option value="">Toutes les promotions</option>
                @foreach($promotions as $p)
                    <option value="{{ $p->id }}" @selected(request('promotion_id') == $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="enseignant_id" class="form-select">
                <option value="">Tous les enseignants</option>
                @foreach($enseignants as $e)
                    <option value="{{ $e->id }}" @selected(request('enseignant_id') == $e->id)>{{ $e->nom_complet }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="bi bi-funnel"></i></button>
            @if(request()->has('q') || request()->filled('promotion_id') || request()->filled('enseignant_id') || request()->filled('type'))
                <a href="{{ route('cours.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th>EC</th>
            <th>Type</th>
            <th>Promotion</th>
            <th>Enseignant</th>
            <th>Volume horaire</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($cours as $c)
            <tr>
                <td><span class="fw-semibold">{{ optional($c->ec)->code }}</span> {{ optional($c->ec)->nom }}</td>
                <td><span class="badge text-bg-light">{{ $c->typeLabel() }}</span></td>
                <td>{{ optional($c->promotion)->nom ?: '-' }}</td>
                <td>{{ optional($c->enseignant)->nom_complet ?: '-' }}</td>
                <td>{{ $c->volume_horaire }} h</td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('cours.edit', $c) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('cours.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Supprimer ce cours ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucun cours trouvé.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $cours->links() }}</div>
</div>
@endsection
