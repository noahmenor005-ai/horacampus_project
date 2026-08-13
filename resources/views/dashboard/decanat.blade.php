@extends('layouts.app')

@section('title', 'Tableau de bord décanat — ' . optional(auth()->user()->faculte)->nom)

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Décanat {{ optional(auth()->user()->faculte)->nom }}</h1>
        <p class="text-muted mb-0">Cœur de la gestion académique — toutes les actions sont opérationnelles.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary btn-sm" href="{{ route('decanat.etudiants.create') }}"><i class="bi bi-person-plus"></i> Étudiant</a>
        <a class="btn btn-primary btn-sm" href="{{ route('decanat.enseignants.create') }}"><i class="bi bi-person-plus-fill"></i> Enseignant</a>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('decanat.horaires.create') }}"><i class="bi bi-calendar-plus"></i> Horaire</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($stats as $stat)
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ $stat['url'] ?? '#' }}" class="surface stat-card d-flex align-items-center gap-3 text-decoration-none text-dark h-100">
                <div class="stat-icon"><i class="bi {{ $stat['icon'] }} fs-5"></i></div>
                <div>
                    <div class="h4 mb-0">{{ number_format($stat['total']) }}</div>
                    <small class="text-muted">{{ $stat['label'] }}</small>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="surface p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Grille horaire de la faculté</h2>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('decanat.horaires.index') }}">Ouvrir le module</a>
    </div>
    @include('partials.timetable-grid', ['grille' => $grille])
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="surface p-3 table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Demandes de salles</h2>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('decanat.demandes-salles.index') }}">Voir tout</a>
            </div>
            <table class="table align-middle mb-0">
                <thead>
                <tr><th>Date</th><th>Cours</th><th>Promotion</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($demandes as $d)
                    <tr>
                        <td>{{ $d->date?->format('d/m/Y') }}</td>
                        <td>{{ $d->cours?->intitule }}</td>
                        <td>{{ optional($d->promotion)->nom }}</td>
                        <td><span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('decanat.demandes-salles.show', $d) }}"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune demande envoyée.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Enseignants</h2>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('decanat.enseignants.index') }}">Voir tout</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($enseignants as $e)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="{{ route('decanat.enseignants.show', $e) }}" class="text-decoration-none fw-semibold">{{ $e->nom_complet }}</a>
                        <span class="badge text-bg-secondary">{{ $e->disponibilites_count }} dispo.</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucun enseignant.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
