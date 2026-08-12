@extends('layouts.app')
@section('title', $promotion->nom)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $promotion->nom }}</h1>
        <small class="text-muted">{{ $promotion->niveau }} — {{ optional($promotion->mention)->nom }} — {{ optional($promotion->anneeAcademique)->libelle }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('decanat.promotions.edit', $promotion) }}">Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('decanat.promotions.index') }}">Retour</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4"><div class="surface p-3"><div class="text-muted small">Effectif</div><div class="h4 mb-0">{{ $promotion->effectif }}</div></div></div>
    <div class="col-md-4"><div class="surface p-3"><div class="text-muted small">Étudiants</div><div class="h4 mb-0">{{ $promotion->etudiants->count() }}</div></div></div>
    <div class="col-md-4"><div class="surface p-3"><div class="text-muted small">UE</div><div class="h4 mb-0">{{ $promotion->ues->count() }}</div></div></div>
</div>
@endsection
