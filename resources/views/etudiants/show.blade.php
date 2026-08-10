@extends('layouts.app')

@section('title', 'Consulter l\'étudiant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Fiche étudiant</h1>
        <small class="text-muted">Consultation seule — aucune donnée académique ne peut être modifiée par l'étudiant lui-même.</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('etudiants.edit', $etudiant) }}"><i class="bi bi-pencil"></i> Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('etudiants.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="surface p-4 text-center">
            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                <i class="bi bi-person fs-1 text-primary"></i>
            </div>
            <h3 class="h5 mb-1">{{ $etudiant->nom }} {{ $etudiant->postnom }} {{ $etudiant->prenom }}</h3>
            <div class="mb-2"><span class="badge text-bg-dark font-monospace">{{ $etudiant->matricule }}</span></div>
            <div class="mb-2">
                @if($etudiant->is_active)
                    <span class="badge text-bg-success">Actif</span>
                @else
                    <span class="badge text-bg-danger">Désactivé</span>
                @endif
                <span class="badge text-bg-light">{{ $etudiant->sexe }}</span>
            </div>
            <div class="text-muted small">{{ $etudiant->email ?: 'Email non renseigné' }}</div>
            <div class="text-muted small">{{ $etudiant->telephone ?: 'Téléphone non renseigné' }}</div>
            <hr>
            <div class="small text-muted">Dernière connexion : {{ $etudiant->last_login_at?->format('d/m/Y H:i') ?: 'Jamais' }}</div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="surface p-4">
            <h5 class="border-bottom pb-2 mb-3">Parcours académique</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Faculté :</strong> {{ optional($etudiant->faculte)->nom ?: '-' }}</div>
                <div class="col-md-6"><strong>Domaine :</strong> {{ optional($etudiant->domaine)->nom ?: '-' }}</div>
                <div class="col-md-6"><strong>Filière :</strong> {{ optional($etudiant->filiere)->nom ?: '-' }}</div>
                <div class="col-md-6"><strong>Mention :</strong> {{ optional($etudiant->mention)->nom ?: '-' }}</div>
                <div class="col-md-6"><strong>Promotion :</strong> {{ optional($etudiant->promotion)->nom ?: '-' }}</div>
                <div class="col-md-6"><strong>Année académique :</strong> {{ optional($etudiant->anneeAcademique)->libelle ?: '-' }}</div>
                <div class="col-md-6"><strong>Statut inscription :</strong> {{ $etudiant->statut_inscription ?? '-' }}</div>
                <div class="col-md-6"><strong>Statut compte :</strong> {{ $etudiant->statusLabel() }}</div>
            </div>
            <hr>
            <h6>Emploi du temps (aperçu)</h6>
            @php
                $horaires = \App\Models\Horaire::with(['cours.ec','auditoire.batiment'])->where('promotion_id', $etudiant->promotion_id)->orderBy('date')->take(5)->get();
            @endphp
            @if($horaires->isEmpty())
                <p class="text-muted small">Aucun horaire programmé pour cette promotion.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($horaires as $h)
                        <li class="list-group-item d-flex justify-content-between small">
                            <span>{{ $h->date?->format('d/m/Y') }} {{ substr($h->heure_debut,0,5) }}-{{ substr($h->heure_fin,0,5) }} — {{ optional($h->cours)->intitule }}</span>
                            <span class="text-muted">{{ optional($h->auditoire)->nom }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
