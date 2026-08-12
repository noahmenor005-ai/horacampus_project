@extends('layouts.app')
@section('title', $mention->exists ? 'Modifier la mention' : 'Ajouter une mention')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $mention->exists ? 'Modifier la mention' : 'Ajouter une mention' }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.mentions.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $mention->exists ? route('decanat.mentions.update', $mention) : route('decanat.mentions.store') }}">
        @csrf
        @if($mention->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Filière <span class="text-danger">*</span></label>
                <select name="filiere_id" class="form-select @error('filiere_id') is-invalid @enderror" required>
                    <option value="">Choisir une filière de votre faculté…</option>
                    @foreach($filieres as $filiere)
                        <option value="{{ $filiere->id }}" @selected((string)old('filiere_id', $mention->filiere_id)===(string)$filiere->id)>{{ $filiere->nom }} ({{ optional($filiere->domaine)->nom }})</option>
                    @endforeach
                </select>
                @error('filiere_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input name="nom" value="{{ old('nom', $mention->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $mention->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="actif" class="form-select">
                    <option value="1" @selected(old('actif', $mention->actif ?? true))>Actif</option>
                    <option value="0" @selected(!old('actif', $mention->actif ?? true))>Inactif</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.mentions.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
