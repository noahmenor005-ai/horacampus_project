@extends('layouts.app')
@section('title', $promotion->exists ? 'Modifier la promotion' : 'Ajouter une promotion')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $promotion->exists ? 'Modifier la promotion' : 'Ajouter une promotion' }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route(($routePrefix ?? 'decanat.promotions').'.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $promotion->exists ? route(($routePrefix ?? 'decanat.promotions').'.update', $promotion) : route(($routePrefix ?? 'decanat.promotions').'.store') }}">
        @csrf
        @if($promotion->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Mention <span class="text-danger">*</span></label>
                <select name="mention_id" class="form-select @error('mention_id') is-invalid @enderror" required>
                    <option value="">Choisir une mention…</option>
                    @foreach($mentions as $mention)
                        <option value="{{ $mention->id }}" @selected((string)old('mention_id', $promotion->mention_id)===(string)$mention->id)>{{ $mention->nom }} — {{ optional($mention->filiere)->nom }}</option>
                    @endforeach
                </select>
                @error('mention_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Année académique <span class="text-danger">*</span></label>
                <select name="annee_academique_id" class="form-select @error('annee_academique_id') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" @selected((string)old('annee_academique_id', $promotion->annee_academique_id)===(string)$annee->id)>{{ $annee->libelle }}</option>
                    @endforeach
                </select>
                @error('annee_academique_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input name="nom" value="{{ old('nom', $promotion->nom) }}" class="form-control @error('nom') is-invalid @enderror" placeholder="Ex: L1 Informatique" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Niveau / classe <span class="text-danger">*</span></label>
                <select name="niveau" class="form-select" required>
                    @foreach(\App\Models\Promotion::NIVEAUX as $niveau)
                        <option value="{{ $niveau }}" @selected(old('niveau', $promotion->niveau)===$niveau)>{{ $niveau }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Effectif</label>
                <input type="number" min="0" name="effectif" value="{{ old('effectif', $promotion->effectif) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="actif" class="form-select">
                    <option value="1" @selected(old('actif', $promotion->actif ?? true))>Actif</option>
                    <option value="0" @selected(!old('actif', $promotion->actif ?? true))>Inactif</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.promotions.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
