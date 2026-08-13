@extends('layouts.app')

@section('title', 'Tableau de bord administrateur')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Bonjour, {{ auth()->user()->prenom }} {{ auth()->user()->nom }}</h1>
        <p class="text-muted mb-0">Supervision générale de HoraCampus — facultés, salles et attributions.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('decanats.index') }}"><i class="bi bi-briefcase"></i> Décanats</a>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('demandes.index') }}"><i class="bi bi-inbox"></i> Demandes</a>
        <a class="btn btn-primary btn-sm" href="{{ route('attributions.index') }}"><i class="bi bi-check2-square"></i> Attributions</a>
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

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3">Statistiques des horaires</h2>
            <div style="height: 300px;"><canvas id="coursParJourChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3">Auditoires par bâtiment</h2>
            <div style="height: 300px;"><canvas id="sallesParBatimentChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="surface p-3 table-responsive">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Dernières demandes</h2>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('demandes.index') }}">Voir tout</a>
            </div>
            <table class="table align-middle mb-0">
                <thead>
                <tr><th>Date</th><th>Décanat</th><th>Cours</th><th>Promotion</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($demandes as $d)
                    <tr>
                        <td>{{ $d->date?->format('d/m/Y') }}</td>
                        <td>{{ optional(optional($d->createur)->faculte)->code ?: optional($d->createur)->nom }}</td>
                        <td>{{ $d->cours?->intitule }}</td>
                        <td>{{ optional($d->promotion)->nom }}</td>
                        <td><span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('demandes.show', $d) }}"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface p-3">
            <h2 class="h5 mb-3">Horaires récents</h2>
            <ul class="list-group list-group-flush">
                @forelse($horairesRecents as $h)
                    <li class="list-group-item px-0">
                        <div class="fw-semibold">{{ optional($h->ec)->nom ?: optional($h->cours)->intitule }}</div>
                        <small class="text-muted">{{ $h->jour }} {{ substr($h->heure_debut,0,5) }}–{{ substr($h->heure_fin,0,5) }} · {{ optional($h->promotion)->nom }} · {{ optional($h->auditoire)->nom }}</small>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucun horaire.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const coursParJour = @json($coursParJour);
        new Chart(document.getElementById('coursParJourChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(coursParJour).map((d) => {
                    const p = d.split('-');
                    return p.length === 3 ? `${p[2]}/${p[1]}` : d;
                }),
                datasets: [{ label: 'Cours', data: Object.values(coursParJour), backgroundColor: 'rgba(11,59,106,.65)', borderRadius: 6 }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
        const sallesParBatiment = @json($sallesParBatiment);
        new Chart(document.getElementById('sallesParBatimentChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(sallesParBatiment), datasets: [{ data: Object.values(sallesParBatiment), backgroundColor: ['#0b3b6a','#d4a017','#0e8a8a','#b42318','#7c3aed','#64748b'] }] },
            options: { responsive: true, maintainAspectRatio: false },
        });
    });
</script>
@endpush
