@extends('layouts.app')

@section('title', 'Tableau de bord décanat — ' . optional(auth()->user()->faculte)->nom)

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Tableau de bord — Décanat {{ optional(auth()->user()->faculte)->nom }}</h1>
        <p class="text-muted mb-0">Responsable de l'enregistrement des étudiants et enseignants de votre faculté.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary btn-sm" href="{{ route('etudiants.create') }}"><i class="bi bi-person-plus"></i> Ajouter étudiant</a>
        <a class="btn btn-primary btn-sm" href="{{ route('enseignants.create') }}"><i class="bi bi-person-plus-fill"></i> Ajouter enseignant</a>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('demandes.create') }}"><i class="bi bi-send"></i> Demande salle</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($stats as $stat)
        <div class="col-md-6 col-xl-2">
            <div class="surface stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi {{ $stat['icon'] }} fs-5"></i></div>
                <div>
                    <div class="h3 mb-0">{{ number_format($stat['total']) }}</div>
                    <small class="text-muted">{{ $stat['label'] }}</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-3"><a href="{{ route('etudiants.index') }}" class="surface p-3 d-block text-decoration-none text-dark"><i class="bi bi-people fs-3 text-primary"></i><div class="fw-semibold">Gestion des étudiants</div><small class="text-muted">Ajouter, modifier, consulter, désactiver, rechercher</small></a></div>
    <div class="col-lg-3"><a href="{{ route('enseignants.index') }}" class="surface p-3 d-block text-decoration-none text-dark"><i class="bi bi-person-workspace fs-3 text-success"></i><div class="fw-semibold">Gestion des enseignants</div><small class="text-muted">Ajouter, modifier, consulter, désactiver, rechercher</small></a></div>
    <div class="col-lg-3"><a href="{{ route('lmd.index', 'domaines') }}" class="surface p-3 d-block text-decoration-none text-dark"><i class="bi bi-diagram-3 fs-3 text-warning"></i><div class="fw-semibold">Domaines</div><small class="text-muted">Organisation LMD</small></a></div>
    <div class="col-lg-3"><a href="{{ route('horaires.index') }}" class="surface p-3 d-block text-decoration-none text-dark"><i class="bi bi-calendar-week fs-3 text-info"></i><div class="fw-semibold">Horaires</div><small class="text-muted">Planification</small></a></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="surface p-3 table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Demandes envoyées</h2>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('demandes.index') }}"><i class="bi bi-arrow-right"></i> Voir tout</a>
            </div>
            <table class="table align-middle mb-0">
                <thead>
                <tr><th>Date</th><th>Cours</th><th>Promotion</th><th>Enseignant</th><th>Statut</th><th class="text-end">Détail</th></tr>
                </thead>
                <tbody>
                @forelse($demandes as $d)
                    <tr>
                        <td>{{ $d->date?->format('d/m/Y') }}</td>
                        <td>{{ optional($d->cours)->intitule }}</td>
                        <td>{{ optional($d->promotion)->nom }}</td>
                        <td>{{ optional($d->enseignant)->nom_complet }}</td>
                        <td><span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('demandes.show', $d) }}"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande envoyée.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Enseignants de la faculté</h2>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('enseignants.index') }}"><i class="bi bi-arrow-right"></i> Voir tout</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($enseignants as $e)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('enseignants.edit', $e) }}" class="text-decoration-none fw-semibold">{{ $e->nom_complet }}</a>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-secondary">{{ $e->disponibilites_count }} disponible(s)</span>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('disponibilites.index') }}" title="Disponibilités"><i class="bi bi-clock-history"></i></a>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucun enseignant dans cette faculté.</li>
                @endforelse
            </ul>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('lmd.index', 'mentions') }}" class="btn btn-outline-secondary btn-sm flex-fill">Mentions</a>
                <a href="{{ route('lmd.index', 'promotions') }}" class="btn btn-outline-secondary btn-sm flex-fill">Promotions</a>
                <a href="{{ route('lmd.index', 'annees') }}" class="btn btn-outline-secondary btn-sm flex-fill">Années acad.</a>
            </div>
        </div>
    </div>
</div>
@endsection
