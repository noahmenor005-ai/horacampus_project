@extends('layouts.app')
@section('title', $annee->exists ? 'Modifier l\'année académique' : 'Ajouter une année académique')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ $annee->exists ? 'Modifier' : 'Ajouter' }} une année académique</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.annees-academiques.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $annee->exists ? route('decanat.annees-academiques.update', $annee) : route('decanat.annees-academiques.store') }}">
        @csrf
        @if($annee->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input name="libelle" value="{{ old('libelle', $annee->libelle) }}" class="form-control @error('libelle') is-invalid @enderror" placeholder="2025-2026" required>
                @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de début</label>
                <input type="date" name="date_debut" value="{{ old('date_debut', optional($annee->date_debut)->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de fin</label>
                <input type="date" name="date_fin" value="{{ old('date_fin', optional($annee->date_fin)->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Active</label>
                <select name="active" class="form-select">
                    <option value="1" @selected(old('active', $annee->active))>Oui</option>
                    <option value="0" @selected(!old('active', $annee->active))>Non</option>
                </select>
                <div class="form-text">Activer cette année désactive automatiquement les autres.</div>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.annees-academiques.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
