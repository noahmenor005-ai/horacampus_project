@extends('layouts.app')

@section('title', 'Rapports de gestion')

@section('content')
@php
    $labels = [
        'facultes' => ['Facultés', 'bi-building'],
        'promotions' => ['Promotions', 'bi-layers'],
        'cours' => ['Cours', 'bi-journal-bookmark'],
        'horaires' => ['Horaires programmés', 'bi-calendar3'],
        'conflits' => ['Conflits', 'bi-exclamation-triangle'],
        'enseignants' => ['Enseignants', 'bi-person-workspace'],
        'etudiants' => ['Étudiants', 'bi-people'],
        'etudiants_actifs' => ['Étudiants actifs', 'bi-person-check'],
        'auditoires' => ['Auditoires', 'bi-door-open'],
        'capacite_totale' => ['Capacité totale', 'bi-arrows-expand'],
        'salles_occupees_aujourdhui' => ['Salles occupées aujourd\'hui', 'bi-lock'],
        'batiments' => ['Bâtiments', 'bi-building-fill-gear'],
        'disponibilites' => ['Disponibilités validées', 'bi-clock-history'],
    ];
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Rapports de gestion</h1>
        <p class="text-muted mb-0">Indicateurs globaux de la plateforme HoraCampus.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('rapports.pdf') }}"><i class="bi bi-file-earmark-pdf"></i> Télécharger le rapport PDF</a>
</div>

<div class="row g-3 mb-4">
    @foreach($labels as $key => $item)
        <div class="col-md-6 col-xl-3">
            <div class="surface stat-card d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi {{ $item[1] }} fs-5"></i></div>
                <div>
                    <div class="h3 mb-0">{{ number_format($stats[$key]) }}</div>
                    <small class="text-muted">{{ $item[0] }}</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3">Demandes d'auditoire</h2>
            @foreach(['en_attente' => ['En attente', 'warning'], 'acceptee' => ['Acceptées', 'success'], 'refusee' => ['Refusées', 'danger']] as $key => $demande)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span class="badge text-bg-{{ $demande[1] }}">{{ $demande[0] }}</span>
                    <span class="fw-semibold">{{ number_format($stats['demandes'][$key] ?? 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-lg-8">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3">Occupation hebdomadaire par bâtiment</h2>
            <table class="table align-middle mb-0">
                <thead>
                <tr><th>Bâtiment</th><th>Auditoires</th><th>Utilisés</th><th>Occupation</th></tr>
                </thead>
                <tbody>
                @forelse($stats['occupation_par_batiment'] as $batiment => $occupation)
                    @php
                        $pct = $occupation['auditoires'] > 0 ? round($occupation['utilises'] / $occupation['auditoires'] * 100) : 0;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $batiment }}</td>
                        <td>{{ $occupation['auditoires'] }}</td>
                        <td>{{ $occupation['utilises'] }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ $pct }}%</small>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun bâtiment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
