@extends('layouts.app')

@section('title', 'Tableau de bord administrateur')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Tableau de bord</h1>
        <p class="text-muted mb-0">Vue d'ensemble de la plateforme HoraCampus.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('users.index') }}"><i class="bi bi-shield-lock"></i> Utilisateurs</a>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('auditoires.index') }}"><i class="bi bi-door-open"></i> Auditoires</a>
        <a class="btn btn-primary btn-sm" href="{{ route('rapports.index') }}"><i class="bi bi-bar-chart"></i> Rapports</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($stats as $stat)
        <div class="col-md-6 col-xl-3">
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
    <div class="col-lg-7">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3">Cours programmés par jour</h2>
            <div style="height: 300px;">
                <canvas id="coursParJourChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3">Auditoires par bâtiment</h2>
            <div style="height: 300px;">
                <canvas id="sallesParBatimentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="surface p-3 table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Dernières demandes d'auditoire</h2>
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
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const coursParJour = @json($coursParJour);
        const labels = Object.keys(coursParJour);
        const dates = labels.map((d) => {
            const parts = d.split('-');
            return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : d;
        });

        new Chart(document.getElementById('coursParJourChart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Cours programmés',
                    data: Object.values(coursParJour),
                    backgroundColor: 'rgba(37, 99, 235, .55)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });

        const sallesParBatiment = @json($sallesParBatiment);
        new Chart(document.getElementById('sallesParBatimentChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(sallesParBatiment),
                datasets: [{
                    data: Object.values(sallesParBatiment),
                    backgroundColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2', '#64748b'],
                }],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    });
</script>
@endpush
