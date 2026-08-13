@extends('layouts.app')

@section('title', 'Tableau de bord étudiant')

@section('content')
<div class="surface p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-1">Bienvenue, {{ auth()->user()->prenom }} {{ auth()->user()->nom }}</h1>
            <p class="text-muted mb-2">
                Matricule : <span class="badge text-bg-dark font-monospace">{{ auth()->user()->matricule }}</span>
                Promotion : <span class="badge text-bg-primary">{{ optional(auth()->user()->promotion)->nom }}</span>
            </p>
            <p class="small text-muted mb-0">{{ optional(auth()->user()->faculte)->nom }} · {{ optional(auth()->user()->domaine)->nom }} · {{ optional(auth()->user()->filiere)->nom }} · {{ optional(auth()->user()->mention)->nom }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-circle"></i> Mon profil</a>
            <a href="{{ route('horaires.index') }}" class="btn btn-primary btn-sm"><i class="bi bi-calendar-week"></i> Emploi du temps</a>
        </div>
    </div>
</div>

@if($prochain)
    <div class="surface p-4 mb-4 border-start border-warning border-4">
        <span class="badge text-bg-primary mb-2">Prochain cours</span>
        <h2 class="h4 mb-2">{{ optional($prochain->ec)->nom ?: optional($prochain->cours)->intitule }}</h2>
        <div class="text-muted">{{ $prochain->date?->format('d/m/Y') }} · {{ substr($prochain->heure_debut, 0, 5) }} - {{ substr($prochain->heure_fin, 0, 5) }} · {{ optional($prochain->auditoire)->nom }} · {{ optional($prochain->enseignant)->nom_complet }}</div>
    </div>
@endif

<div class="surface p-3 mb-4" id="emploi">
    <h2 class="h5 mb-3"><i class="bi bi-calendar-week"></i> Mon emploi du temps</h2>
    <p class="small text-muted">Vous ne voyez que les cours de votre promotion.</p>
    @include('partials.timetable-grid', ['grille' => $grille])
</div>

<div class="row g-4 mb-4">
    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi'] as $jour)
        <div class="col-md-6 col-xl-4">
            <div class="surface p-3 h-100">
                <h3 class="h6 text-uppercase text-muted">{{ $jour }}</h3>
                @forelse($parJour[$jour] ?? [] as $h)
                    <div class="border-start border-3 border-primary ps-3 mb-3">
                        <div class="fw-bold">{{ substr($h->heure_debut,0,5) }} – {{ substr($h->heure_fin,0,5) }}</div>
                        <div>{{ optional($h->ec)->nom ?: optional($h->cours)->intitule }}</div>
                        <small class="text-muted">Enseignant : {{ optional($h->enseignant)->nom_complet }}</small><br>
                        <small class="text-muted">Salle : {{ optional($h->auditoire)->nom === 'EN-ATTENTE' ? 'En attente' : optional($h->auditoire)->nom }}</small>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Aucun cours.</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6" id="cours">
        <div class="surface p-3 table-responsive">
            <h2 class="h5 mb-3">Mes cours</h2>
            <table class="table align-middle mb-0">
                <thead><tr><th>Code</th><th>EC</th><th>Enseignant</th><th>Type</th></tr></thead>
                <tbody>
                @forelse($cours as $c)
                    <tr>
                        <td><span class="badge text-bg-light">{{ optional($c->ec)->code }}</span></td>
                        <td>{{ optional($c->ec)->nom }}</td>
                        <td>{{ optional($c->enseignant)->nom_complet }}</td>
                        <td>{{ $c->typeLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun cours.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6" id="enseignants">
        <div class="surface p-3" id="salles">
            <h2 class="h5 mb-3">Mes enseignants & salles</h2>
            @php $enseignantsPromotion = $cours->pluck('enseignant')->filter()->unique('id'); @endphp
            <ul class="list-group list-group-flush mb-3">
                @forelse($enseignantsPromotion as $ens)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="fw-semibold">{{ $ens->nom_complet }}</span>
                        <span class="small text-muted">{{ $ens->email }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted px-0">Aucun enseignant assigné.</li>
                @endforelse
            </ul>
            @php $salles = $horaires->pluck('auditoire')->filter()->unique('id')->reject(fn($s) => $s->nom === 'EN-ATTENTE'); @endphp
            <div class="small">
                <strong>Salles attribuées :</strong>
                @forelse($salles as $s)
                    <span class="badge text-bg-light">{{ $s->nom }}</span>
                @empty
                    <span class="text-muted">Aucune salle confirmée.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
