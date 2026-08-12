@extends('layouts.app')
@section('title', $filiere->exists ? 'Modifier la filière' : 'Ajouter une filière')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $filiere->exists ? 'Modifier la filière' : 'Ajouter une filière' }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.filieres.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $filiere->exists ? route('decanat.filieres.update', $filiere) : route('decanat.filieres.store') }}">
        @csrf
        @if($filiere->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Domaine <span class="text-danger">*</span></label>
                <select name="domaine_id" class="form-select @error('domaine_id') is-invalid @enderror" required>
                    <option value="">Choisir un domaine de votre faculté…</option>
                    @foreach($domaines as $domaine)
                        <option value="{{ $domaine->id }}" @selected((string)old('domaine_id', $filiere->domaine_id)===(string)$domaine->id)>{{ $domaine->nom }}</option>
                    @endforeach
                </select>
                @error('domaine_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input name="nom" value="{{ old('nom', $filiere->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $filiere->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="actif" class="form-select">
                    <option value="1" @selected(old('actif', $filiere->actif ?? true))>Actif</option>
                    <option value="0" @selected(!old('actif', $filiere->actif ?? true))>Inactif</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.filieres.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
