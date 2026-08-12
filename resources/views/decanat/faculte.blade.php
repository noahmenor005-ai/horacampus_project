@extends('layouts.app')
@section('title', 'Ma faculté')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $faculte->nom }}</h1>
        <small class="text-muted">Code : {{ $faculte->code }} — consultation seule (la création des facultés est réservée à l'administrateur).</small>
    </div>
</div>
<div class="surface p-4 mb-3">
    <p class="mb-0">{{ $faculte->description ?: 'Aucune description.' }}</p>
</div>
<div class="row g-3">
    <div class="col-md-3"><div class="surface p-3"><div class="text-muted small">Domaines</div><div class="h4 mb-0">{{ $faculte->domaines->count() }}</div></div></div>
    <div class="col-md-3"><div class="surface p-3"><div class="text-muted small">Filières</div><div class="h4 mb-0">{{ $faculte->domaines->sum(fn($d) => $d->filieres->count()) }}</div></div></div>
    <div class="col-md-3"><div class="surface p-3"><div class="text-muted small">Mentions</div><div class="h4 mb-0">{{ $faculte->domaines->sum(fn($d) => $d->filieres->sum(fn($f) => $f->mentions->count())) }}</div></div></div>
    <div class="col-md-3"><div class="surface p-3"><div class="text-muted small">Membres</div><div class="h4 mb-0">{{ $faculte->membres->count() }}</div></div></div>
</div>
<div class="surface p-4 mt-3">
    <h2 class="h5">Arborescence LMD</h2>
    @foreach($faculte->domaines as $domaine)
        <div class="mb-3">
            <div class="fw-semibold"><i class="bi bi-diagram-3"></i> {{ $domaine->nom }}</div>
            <ul class="mb-0">
                @foreach($domaine->filieres as $filiere)
                    <li>{{ $filiere->nom }}
                        <ul>
                            @foreach($filiere->mentions as $mention)
                                <li>{{ $mention->nom }}
                                    <small class="text-muted">({{ $mention->promotions->pluck('nom')->join(', ') }})</small>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
@endsection
