@extends('layouts.app')
@section('title', $annee->libelle)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ $annee->libelle }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.annees-academiques.index') }}">Retour</a>
</div>
<div class="surface p-4">
    <p>{{ $annee->date_debut?->format('d/m/Y') }} — {{ $annee->date_fin?->format('d/m/Y') }}</p>
    <span class="badge text-bg-{{ $annee->active ? 'success' : 'secondary' }}">{{ $annee->statutLabel() }}</span>
    <h2 class="h5 mt-4">Semestres</h2>
    <ul class="mb-0">
        @forelse($annee->semestres as $semestre)
            <li>{{ $semestre->libelle }}</li>
        @empty
            <li class="text-muted">Aucun semestre.</li>
        @endforelse
    </ul>
</div>
@endsection
