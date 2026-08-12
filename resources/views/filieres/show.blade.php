@extends('layouts.app')
@section('title', $filiere->nom)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $filiere->nom }}</h1>
        <small class="text-muted">{{ optional($filiere->domaine)->nom }} — {{ optional(optional($filiere->domaine)->faculte)->nom }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('decanat.filieres.edit', $filiere) }}">Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('decanat.filieres.index') }}">Retour</a>
    </div>
</div>
<div class="surface p-4">
    <p>{{ $filiere->description ?: 'Aucune description.' }}</p>
    <h2 class="h5 mt-3">Mentions</h2>
    <ul class="mb-0">
        @forelse($filiere->mentions as $mention)
            <li><a href="{{ route('decanat.mentions.show', $mention) }}">{{ $mention->nom }}</a></li>
        @empty
            <li class="text-muted">Aucune mention.</li>
        @endforelse
    </ul>
</div>
@endsection
