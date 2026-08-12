@extends('layouts.app')
@section('title', $mention->nom)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $mention->nom }}</h1>
        <small class="text-muted">{{ optional($mention->filiere)->nom }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('decanat.mentions.edit', $mention) }}">Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('decanat.mentions.index') }}">Retour</a>
    </div>
</div>
<div class="surface p-4">
    <p>{{ $mention->description ?: 'Aucune description.' }}</p>
    <h2 class="h5">Promotions</h2>
    <ul class="mb-0">
        @forelse($mention->promotions as $promotion)
            <li><a href="{{ route('decanat.promotions.show', $promotion) }}">{{ $promotion->nom }}</a> — {{ optional($promotion->anneeAcademique)->libelle }}</li>
        @empty
            <li class="text-muted">Aucune promotion.</li>
        @endforelse
    </ul>
</div>
@endsection
