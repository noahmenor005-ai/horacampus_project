@extends('layouts.app')

@section('title', $domaine->nom)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $domaine->nom }}</h1>
        <small class="text-muted">{{ optional($domaine->faculte)->nom }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('decanat.domaines.edit', $domaine) }}"><i class="bi bi-pencil"></i> Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('decanat.domaines.index') }}">Retour</a>
    </div>
</div>

<div class="surface p-4 mb-3">
    <p class="mb-2">{{ $domaine->description ?: 'Aucune description.' }}</p>
    <span class="badge text-bg-{{ $domaine->actif ? 'success' : 'secondary' }}">{{ $domaine->statutLabel() }}</span>
</div>

<div class="surface p-4">
    <h2 class="h5 mb-3">Filières ({{ $domaine->filieres->count() }})</h2>
    <ul class="list-group list-group-flush">
        @forelse($domaine->filieres as $filiere)
            <li class="list-group-item d-flex justify-content-between">
                <a href="{{ route('decanat.filieres.show', $filiere) }}">{{ $filiere->nom }}</a>
                <span class="text-muted">{{ $filiere->mentions->count() }} mention(s)</span>
            </li>
        @empty
            <li class="list-group-item text-muted">Aucune filière.</li>
        @endforelse
    </ul>
</div>
@endsection
