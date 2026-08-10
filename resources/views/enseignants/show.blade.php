@extends('layouts.app')

@section('title', 'Consulter l\'enseignant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Fiche enseignant</h1>
        <small class="text-muted">Consultation — Décanat {{ optional(auth()->user()->faculte)->nom }}</small>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('enseignants.edit', $enseignant) }}"><i class="bi bi-pencil"></i> Modifier</a>
        <a class="btn btn-outline-secondary" href="{{ route('enseignants.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="surface p-4 text-center">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                <i class="bi bi-person-workspace fs-1 text-success"></i>
            </div>
            <h3 class="h5 mb-1">{{ $enseignant->nom }} {{ $enseignant->postnom }} {{ $enseignant->prenom }}</h3>
            <div class="mb-1 small text-muted">{{ $enseignant->grade ?: '' }}</div>
            <div class="mb-2"><span class="badge text-bg-dark font-monospace">{{ $enseignant->matricule ?: '—' }}</span> <span class="badge text-bg-light">{{ $enseignant->sexe ?: '-' }}</span></div>
            <div class="text-muted small">{{ $enseignant->email }}</div>
            <div class="text-muted small">{{ $enseignant->telephone ?: '' }}</div>
            <hr>
            <div class="small"><strong>Faculté :</strong> {{ optional($enseignant->faculte)->nom }}</div>
            <div class="small"><strong>Statut :</strong> @if($enseignant->is_active)<span class="badge text-bg-success">Actif</span>@else<span class="badge text-bg-danger">Désactivé</span>@endif</div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="surface p-4">
            <h5 class="border-bottom pb-2 mb-3">Charge académique</h5>
            <p class="small text-muted">EC assignés :</p>
            @if($enseignant->ecs->isEmpty())
                <p class="text-muted small">Aucun EC assigné.</p>
            @else
                <ul class="list-group list-group-flush mb-3">
                    @foreach($enseignant->ecs as $ec)
                        <li class="list-group-item d-flex justify-content-between small"><span>{{ $ec->code }} — {{ $ec->nom }}</span><span class="badge text-bg-light">{{ optional($ec->ue)->nom }}</span></li>
                    @endforeach
                </ul>
            @endif
            <h6 class="mt-3">Disponibilités</h6>
            @if($enseignant->disponibilites->isEmpty())
                <p class="text-muted small">Aucune disponibilité déclarée.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($enseignant->disponibilites->take(5) as $d)
                        <li class="list-group-item small">{{ $d->jour }} {{ substr($d->heure_debut,0,5) }}-{{ substr($d->heure_fin,0,5) }} — <span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></li>
                    @endforeach
                </ul>
            @endif
            <h6 class="mt-3">Horaires (semaine)</h6>
            @php $horaires = $enseignant->horairesEnseignes()->with(['cours.ec','auditoire','promotion'])->orderBy('date')->take(5)->get(); @endphp
            @if($horaires->isEmpty())
                <p class="text-muted small">Aucun horaire.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($horaires as $h)
                        <li class="list-group-item small d-flex justify-content-between"><span>{{ $h->date?->format('d/m/Y') }} {{ substr($h->heure_debut,0,5) }} — {{ optional($h->cours)->intitule }}</span><span class="text-muted">{{ optional($h->auditoire)->nom }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
