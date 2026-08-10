@extends('layouts.app')

@section('title', 'Tableau de bord enseignant')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Bonjour, {{ auth()->user()->prenom }} — <span class="badge text-bg-dark">{{ auth()->user()->matricule ?: '' }}</span></h1>
        <p class="text-muted mb-0">Votre planning de la semaine. Consultez votre emploi du temps, vos cours, UE, EC, salles et disponibilités.</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('disponibilites.create') }}"><i class="bi bi-plus-lg"></i> Déclarer une disponibilité</a>
</div>

@if($prochain)
    <div class="surface p-4 mb-4 border-start border-primary border-4">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge text-bg-primary">Prochain cours</span>
            <span class="text-muted">{{ $prochain->date?->format('d/m/Y') }}</span>
        </div>
        <h2 class="h4 mb-2">{{ optional($prochain->cours)->intitule }}</h2>
        <p class="mb-1"><i class="bi bi-clock"></i> {{ substr($prochain->heure_debut, 0, 5) }} - {{ substr($prochain->heure_fin, 0, 5) }}</p>
        <p class="mb-0 text-muted"><i class="bi bi-door-open"></i> {{ optional($prochain->auditoire)->nom }} — {{ optional(optional($prochain->auditoire)->batiment)->nom }} <span class="mx-1">·</span> <i class="bi bi-layers"></i> {{ optional($prochain->promotion)->nom }}</p>
    </div>
@else
    <div class="surface p-4 mb-4 text-muted">Aucun cours à venir.</div>
@endif

<div class="surface p-3 mb-4 table-responsive" id="emploi">
    <h2 class="h5 mb-3"><i class="bi bi-calendar-week"></i> Mon emploi du temps</h2>
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Jour</th><th>Date</th><th>Heure</th><th>Cours</th><th>Salle</th><th>Promotion</th><th>Statut</th></tr>
        </thead>
        <tbody>
        @forelse($horairesSemaine as $h)
            <tr>
                <td><span class="badge text-bg-light">{{ $h->jour }}</span></td>
                <td>{{ $h->date?->format('d/m/Y') }}</td>
                <td>{{ substr($h->heure_debut, 0, 5) }} - {{ substr($h->heure_fin, 0, 5) }}</td>
                <td>{{ optional($h->cours)->intitule }}</td>
                <td>{{ optional($h->auditoire)->nom }}</td>
                <td>{{ optional($h->promotion)->nom }}</td>
                <td><span class="badge text-bg-{{ $h->badgeClass() }}">{{ $h->statutLabel() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucun cours programmé cette semaine.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="row g-3" id="cours">
    <div class="col-lg-6">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3"><i class="bi bi-clock-history"></i> Mes disponibilités</h2>
            <ul class="list-group list-group-flush">
                @forelse($disponibilites as $d)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $d->jour }}</div>
                            <small class="text-muted">{{ substr($d->heure_debut, 0, 5) }} - {{ substr($d->heure_fin, 0, 5) }}</small>
                        </div>
                        <span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucune disponibilité déclarée.</li>
                @endforelse
            </ul>
            <a class="btn btn-outline-primary btn-sm mt-3" href="{{ route('disponibilites.create') }}"><i class="bi bi-plus-lg"></i> Ajouter une disponibilité</a>
            <a href="{{ route('disponibilites.index') }}" class="btn btn-link btn-sm">Voir tout</a>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="surface p-3 table-responsive h-100" id="ecs">
            <h2 class="h5 mb-3"><i class="bi bi-journal"></i> Mes UE / EC & Salles</h2>
            <table class="table align-middle mb-0">
                <thead>
                <tr><th>Code</th><th>EC</th><th>UE</th><th>Coeff.</th><th>Volume</th></tr>
                </thead>
                <tbody>
                @forelse($ecs as $ec)
                    <tr>
                        <td><span class="badge text-bg-light">{{ $ec->code }}</span></td>
                        <td>{{ $ec->nom }}</td>
                        <td>{{ optional($ec->ue)->nom }}</td>
                        <td>{{ $ec->coefficient }}</td>
                        <td>{{ $ec->volume_horaire }} h</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun EC assigné.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="mt-3 small text-muted">
                <strong>Salles attribuées récemment :</strong>
                @php $salles = $horairesSemaine->pluck('auditoire')->filter()->unique('id'); @endphp
                @if($salles->isEmpty())
                    Aucune salle pour cette semaine.
                @else
                    @foreach($salles as $s)
                        <span class="badge text-bg-light">{{ $s->nom }} ({{ optional($s->batiment)->nom }})</span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
