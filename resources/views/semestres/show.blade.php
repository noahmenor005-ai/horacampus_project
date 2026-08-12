@extends('layouts.app')
@section('title', $semestre->libelle)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ $semestre->libelle }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.semestres.index') }}">Retour</a>
</div>
<div class="surface p-4">
    <p>Année : {{ optional($semestre->anneeAcademique)->libelle }}</p>
    <h2 class="h5">UE</h2>
    <ul class="mb-0">
        @forelse($semestre->ues as $ue)
            <li><a href="{{ route('decanat.ues.show', $ue) }}">{{ $ue->code }} — {{ $ue->nom }}</a></li>
        @empty
            <li class="text-muted">Aucune UE.</li>
        @endforelse
    </ul>
</div>
@endsection
