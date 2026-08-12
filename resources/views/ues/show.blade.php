@extends('layouts.app')
@section('title', $ue->code)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $ue->code }} — {{ $ue->nom }}</h1>
        <small class="text-muted">{{ optional($ue->promotion)->nom }} — {{ optional($ue->semestre)->libelle }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('decanat.ues.edit', $ue) }}">Modifier</a>
        <a class="btn btn-primary" href="{{ route('decanat.ecs.create', ['ue_id' => $ue->id]) }}">Ajouter un EC</a>
        <a class="btn btn-outline-secondary" href="{{ route('decanat.ues.index') }}">Retour</a>
    </div>
</div>
<div class="surface p-4 mb-3">
    <p>{{ $ue->description ?: 'Aucune description.' }}</p>
    <div class="d-flex gap-3">
        <span class="badge text-bg-light">{{ $ue->credit }} crédits</span>
        <span class="badge text-bg-{{ $ue->estActif() ? 'success' : 'secondary' }}">{{ $ue->statutLabel() }}</span>
    </div>
</div>
<div class="surface p-4">
    <h2 class="h5">Éléments constitutifs</h2>
    <ul class="mb-0">
        @forelse($ue->ecs as $ec)
            <li><a href="{{ route('decanat.ecs.show', $ec) }}">{{ $ec->code }} — {{ $ec->nom }}</a> ({{ $ec->volume_horaire }} h)</li>
        @empty
            <li class="text-muted">Aucun EC. Ajoutez-en un avant de supprimer cette UE.</li>
        @endforelse
    </ul>
</div>
@endsection
