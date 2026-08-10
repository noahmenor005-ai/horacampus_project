@extends('layouts.app')

@section('title', 'Tableau de bord étudiant')

@section('content')
<div class="surface p-4 mb-4">
    <h1 class="h3 mb-1">Bienvenue sur HoraCampus</h1>
    <p class="text-muted mb-0">
        Promotion :
        <span class="badge text-bg-primary">{{ optional(auth()->user()->promotion)->nom }}</span>
    </p>
</div>

@if($prochain)
    <div class="surface p-4 mb-4 border-start border-primary border-4">
        <span class="badge text-bg-primary mb-2">Prochain cours</span>
        <h2 class="h4 mb-2">{{ optional($prochain->cours)->intitule }}</h2>
        <div class="text-muted mb-1">
            {{ $prochain->date?->format('d/m/Y') }} · {{ substr($prochain->heure_debut, 0, 5) }} - {{ substr($prochain->heure_fin, 0, 5) }}
        </div>
        <div class="text-muted">
            <i class="bi bi-door-open"></i> {{ optional($prochain->auditoire)->nom }}
            <span class="mx-1">·</span>
            <i class="bi bi-person-workspace"></i> {{ optional($prochain->enseignant)->nom_complet }}
        </div>
    </div>
@else
    <div class="surface p-4 mb-4 text-muted">Aucun cours à venir.</div>
@endif

<div class="surface p-3 mb-4 table-responsive">
    <h2 class="h5 mb-3">Cours de la semaine</h2>
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Jour</th><th>Date</th><th>Heure</th><th>Cours</th><th>Enseignant</th><th>Salle</th></tr>
        </thead>
        <tbody>
        @forelse($horairesSemaine as $h)
            <tr>
                <td><span class="badge text-bg-light">{{ $h->jour }}</span></td>
                <td>{{ $h->date?->format('d/m/Y') }}</td>
                <td>{{ substr($h->heure_debut, 0, 5) }} - {{ substr($h->heure_fin, 0, 5) }}</td>
                <td>{{ optional($h->cours)->intitule }}</td>
                <td>{{ optional($h->enseignant)->nom_complet }}</td>
                <td>{{ optional($h->auditoire)->nom }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucun cours programmé cette semaine.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="surface p-3 table-responsive">
    <h2 class="h5 mb-3">Mes cours</h2>
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Code</th><th>EC</th><th>Enseignant</th><th>Type</th><th>Volume</th></tr>
        </thead>
        <tbody>
        @forelse($cours as $c)
            <tr>
                <td><span class="badge text-bg-light">{{ optional($c->ec)->code }}</span></td>
                <td>{{ optional($c->ec)->nom }}</td>
                <td>{{ optional($c->enseignant)->nom_complet }}</td>
                <td>{{ $c->typeLabel() }}</td>
                <td>{{ $c->volume_horaire }} h</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Aucun cours.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
