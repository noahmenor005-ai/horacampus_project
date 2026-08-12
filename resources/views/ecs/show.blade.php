@extends('layouts.app')
@section('title', $ec->code)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $ec->code }} — {{ $ec->nom }}</h1>
        <small class="text-muted">UE : {{ optional($ec->ue)->nom }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('decanat.ecs.edit', $ec) }}">Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('decanat.ecs.index') }}">Retour</a>
    </div>
</div>
<div class="surface p-4">
    <dl class="row mb-0">
        <dt class="col-sm-3">Heures</dt><dd class="col-sm-9">{{ $ec->volume_horaire }}</dd>
        <dt class="col-sm-3">Crédits</dt><dd class="col-sm-9">{{ $ec->credit ?: '—' }}</dd>
        <dt class="col-sm-3">Enseignant</dt><dd class="col-sm-9">{{ optional($ec->enseignant)->nom_complet ?: '—' }}</dd>
        <dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ $ec->statutLabel() }}</dd>
    </dl>
</div>
@endsection
