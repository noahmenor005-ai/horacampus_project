@extends('layouts.app')

@section('title', 'Tableau de bord enseignant')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Bonjour, {{ auth()->user()->prenom }}</h1>
        <p class="text-muted mb-0">Consultez votre emploi du temps, vos cours et vos disponibilités. L’horaire officiel n’est pas modifiable.</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="{{ route('disponibilites.create') }}"><i class="bi bi-plus-lg"></i> Déclarer une disponibilité</a>
</div>

@if($prochain)
    <div class="surface p-4 mb-4 border-start border-warning border-4">
        <span class="badge text-bg-primary">Prochain cours</span>
        <h2 class="h4 mt-2">{{ optional($prochain->ec)->nom ?: optional($prochain->cours)->intitule }}</h2>
        <p class="mb-0 text-muted">{{ $prochain->date?->format('d/m/Y') }} · {{ substr($prochain->heure_debut,0,5) }}–{{ substr($prochain->heure_fin,0,5) }} · {{ optional($prochain->auditoire)->nom }} · {{ optional($prochain->promotion)->nom }}</p>
    </div>
@endif

<div class="surface p-3 mb-4" id="emploi">
    <h2 class="h5 mb-3">Mon emploi du temps</h2>
    @include('partials.timetable-grid', ['grille' => $grille])
</div>

<div class="row g-3">
    <div class="col-lg-6" id="cours">
        <div class="surface p-3 table-responsive" id="ecs">
            <h2 class="h5 mb-3">Mes UE / EC</h2>
            <table class="table align-middle mb-0">
                <thead><tr><th>Code</th><th>EC</th><th>UE</th><th>Volume</th></tr></thead>
                <tbody>
                @forelse($ecs as $ec)
                    <tr>
                        <td><span class="badge text-bg-light">{{ $ec->code }}</span></td>
                        <td>{{ $ec->nom }}</td>
                        <td>{{ optional($ec->ue)->nom }}</td>
                        <td>{{ $ec->volume_horaire }} h</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun EC assigné.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6" id="salles">
        <div class="surface p-3">
            <h2 class="h5 mb-3">Disponibilités & salles</h2>
            <ul class="list-group list-group-flush mb-3">
                @forelse($disponibilites as $d)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ $d->jour }} {{ substr($d->heure_debut,0,5) }}–{{ substr($d->heure_fin,0,5) }}</span>
                        <span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted px-0">Aucune disponibilité.</li>
                @endforelse
            </ul>
            @php $salles = $horaires->pluck('auditoire')->filter()->unique('id')->reject(fn($s) => $s->nom === 'EN-ATTENTE'); @endphp
            <div class="small"><strong>Salles :</strong>
                @forelse($salles as $s)<span class="badge text-bg-light">{{ $s->nom }}</span>
                @empty<span class="text-muted">Aucune salle confirmée.</span>@endforelse
            </div>
            <a class="btn btn-outline-primary btn-sm mt-3" href="{{ route('disponibilites.index') }}">Gérer mes disponibilités</a>
        </div>
    </div>
</div>
@endsection
