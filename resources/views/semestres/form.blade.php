@extends('layouts.app')
@section('title', $semestre->exists ? 'Modifier le semestre' : 'Ajouter un semestre')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ $semestre->exists ? 'Modifier' : 'Ajouter' }} un semestre</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.semestres.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $semestre->exists ? route('decanat.semestres.update', $semestre) : route('decanat.semestres.store') }}">
        @csrf
        @if($semestre->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Année académique <span class="text-danger">*</span></label>
                <select name="annee_academique_id" class="form-select" required>
                    <option value="">Choisir…</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" @selected((string)old('annee_academique_id', $semestre->annee_academique_id)===(string)$annee->id)>{{ $annee->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <select name="libelle" class="form-select" required>
                    @foreach(\App\Models\Semestre::LIBELLES as $libelle)
                        <option value="{{ $libelle }}" @selected(old('libelle', $semestre->libelle)===$libelle)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de début</label>
                <input type="date" name="date_debut" value="{{ old('date_debut', optional($semestre->date_debut)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de fin</label>
                <input type="date" name="date_fin" value="{{ old('date_fin', optional($semestre->date_fin)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="actif" class="form-select">
                    <option value="1" @selected(old('actif', $semestre->actif ?? true))>Actif</option>
                    <option value="0" @selected(!old('actif', $semestre->actif ?? true))>Inactif</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.semestres.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
