@extends('layouts.app')

@section('title', 'Tableau de bord étudiant')

@section('content')
<div class="surface p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-1">Bienvenue, {{ auth()->user()->prenom }} {{ auth()->user()->nom }}</h1>
            <p class="text-muted mb-2">Matricule : <span class="badge text-bg-dark font-monospace">{{ auth()->user()->matricule }}</span> &nbsp; Promotion : <span class="badge text-bg-primary">{{ optional(auth()->user()->promotion)->nom }}</span></p>
            <p class="small text-muted mb-0">Faculté : {{ optional(auth()->user()->faculte)->nom }} — Domaine : {{ optional(auth()->user()->domaine)->nom }} — Filière : {{ optional(auth()->user()->filiere)->nom }} — Mention : {{ optional(auth()->user()->mention)->nom }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-circle"></i> Mon profil</a>
            <a href="{{ route('horaires.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-calendar-week"></i> Emploi du temps</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="surface p-3 text-center">
            <div class="stat-icon mx-auto mb-2"><i class="bi bi-people"></i></div>
            <div class="fw-bold">{{ optional(auth()->user()->promotion)->nom ?: '-' }}</div>
            <small class="text-muted">Ma promotion</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="surface p-3 text-center">
            <div class="stat-icon mx-auto mb-2"><i class="bi bi-building"></i></div>
            <div class="fw-bold">{{ optional(auth()->user()->faculte)->nom ?: '-' }}</div>
            <small class="text-muted">Ma faculté</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="surface p-3 text-center">
            <div class="stat-icon mx-auto mb-2"><i class="bi bi-door-open"></i></div>
            <div class="fw-bold">{{ \App\Models\Auditoire::whereHas('horaires', fn($q)=>$q->where('promotion_id', auth()->user()->promotion_id))->count() }}</div>
            <small class="text-muted">Salles attribuées</small>
        </div>
    </div>
</div>

@if($prochain)
    <div class="surface p-4 mb-4 border-start border-primary border-4">
        <span class="badge text-bg-primary mb-2">Prochain cours</span>
        <h2 class="h4 mb-2">{{ optional($prochain->cours)->intitule }}</h2>
        <div class="text-muted mb-1">
            {{ $prochain->date?->format('d/m/Y') }} · {{ substr($prochain->heure_debut, 0, 5) }} - {{ substr($prochain->heure_fin, 0, 5) }}
        </div>
        <div class="text-muted">
            <i class="bi bi-door-open"></i> {{ optional($prochain->auditoire)->nom }} ({{ optional(optional($prochain->auditoire)->batiment)->nom }})
            <span class="mx-1">·</span>
            <i class="bi bi-person-workspace"></i> {{ optional($prochain->enseignant)->nom_complet }}
        </div>
    </div>
@else
    <div class="surface p-4 mb-4 text-muted">Aucun cours à venir.</div>
@endif

<div class="surface p-3 mb-4 table-responsive" id="emploi">
    <h2 class="h5 mb-3"><i class="bi bi-calendar-week"></i> Mon emploi du temps (semaine)</h2>
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
                <td>{{ optional($h->auditoire)->nom }} <small class="text-muted">({{ optional(optional($h->auditoire)->batiment)->nom }})</small></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucun cours programmé cette semaine.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6" id="cours">
        <div class="surface p-3 h-100 table-responsive">
            <h2 class="h5 mb-3"><i class="bi bi-journal-bookmark"></i> Mes cours</h2>
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
    </div>
    <div class="col-lg-6" id="enseignants">
        <div class="surface p-3 h-100">
            <h2 class="h5 mb-3"><i class="bi bi-person-workspace"></i> Mes enseignants</h2>
            @php
                $enseignants = \App\Models\User::where('role', \App\Models\User::ROLE_ENSEIGNANT)->where('faculte_id', auth()->user()->faculte_id)->with('ecs')->get();
                // filtrer ceux qui enseignent à la promotion
                $enseignantsPromotion = $cours->pluck('enseignant')->filter()->unique('id');
            @endphp
            @if($enseignantsPromotion->isEmpty())
                <p class="text-muted small">Aucun enseignant assigné à vos cours pour l'instant.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($enseignantsPromotion as $ens)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ $ens->nom_complet }}</span>
                            <span class="badge text-bg-light">{{ $ens->email }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="mt-3 p-2 bg-light rounded small text-muted">
                <i class="bi bi-info-circle"></i> Vous ne pouvez modifier aucune donnée académique (promotion, domaine, filière, mention, matricule). Contactez le Décanat pour toute correction.
            </div>
        </div>
    </div>
</div>

<div class="surface p-3 table-responsive" id="promotion">
    <h2 class="h5 mb-3"><i class="bi bi-layers"></i> Ma promotion & salles attribuées</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="p-3 border rounded">
                <div class="fw-semibold">{{ optional(auth()->user()->promotion)->nom }}</div>
                <div class="small text-muted">Mention : {{ optional(optional(auth()->user()->promotion)->mention)->nom }}</div>
                <div class="small text-muted">Effectif : {{ optional(auth()->user()->promotion)->effectif }} étudiants</div>
                <div class="small text-muted">Année académique : {{ optional(auth()->user()->anneeAcademique)->libelle }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 border rounded">
                <div class="fw-semibold">Salles attribuées</div>
                @php $salles = \App\Models\Horaire::with('auditoire.batiment')->where('promotion_id', auth()->user()->promotion_id)->get()->pluck('auditoire')->filter()->unique('id'); @endphp
                @if($salles->isEmpty())
                    <div class="small text-muted">Aucune salle attribuée pour le moment.</div>
                @else
                    <ul class="small mb-0">
                        @foreach($salles as $s)
                            <li>{{ $s->nom }} — {{ optional($s->batiment)->nom }} ({{ $s->capacite }} places)</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
