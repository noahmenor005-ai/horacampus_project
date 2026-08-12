@extends('layouts.app')
@section('title', $ue->exists ? 'Modifier l\'UE' : 'Ajouter une UE')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ $ue->exists ? 'Modifier' : 'Ajouter' }} une unité d'enseignement</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.ues.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $ue->exists ? route('decanat.ues.update', $ue) : route('decanat.ues.store') }}">
        @csrf
        @if($ue->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input name="code" value="{{ old('code', $ue->code) }}" class="form-control @error('code') is-invalid @enderror" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                <input name="nom" value="{{ old('nom', $ue->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $ue->description) }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Crédits <span class="text-danger">*</span></label>
                <input type="number" min="0" max="30" name="credit" value="{{ old('credit', $ue->credit) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mention / formation</label>
                <select name="mention_id" class="form-select">
                    <option value="">—</option>
                    @foreach($mentions as $mention)
                        <option value="{{ $mention->id }}" @selected((string)old('mention_id', $ue->mention_id)===(string)$mention->id)>{{ $mention->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Promotion <span class="text-danger">*</span></label>
                <select name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach($promotions as $promotion)
                        <option value="{{ $promotion->id }}" @selected((string)old('promotion_id', $ue->promotion_id)===(string)$promotion->id)>{{ $promotion->nom }}</option>
                    @endforeach
                </select>
                @error('promotion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Semestre <span class="text-danger">*</span></label>
                <select name="semestre_id" class="form-select" required>
                    <option value="">Choisir…</option>
                    @foreach($semestres as $semestre)
                        <option value="{{ $semestre->id }}" @selected((string)old('semestre_id', $ue->semestre_id)===(string)$semestre->id)>{{ $semestre->libelle }} @if($semestre->anneeAcademique)— {{ $semestre->anneeAcademique->libelle }}@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Année académique</label>
                <select name="annee_academique_id" class="form-select">
                    <option value="">—</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" @selected((string)old('annee_academique_id', $ue->annee_academique_id)===(string)$annee->id)>{{ $annee->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    @foreach(\App\Models\Ue::STATUTS as $value => $label)
                        <option value="{{ $value }}" @selected(old('statut', $ue->statut ?? 'actif')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.ues.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
