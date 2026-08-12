@extends('layouts.app')

@section('title', $domaine->exists ? 'Modifier le domaine' : 'Ajouter un domaine')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $domaine->exists ? 'Modifier le domaine' : 'Ajouter un domaine' }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.domaines.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $domaine->exists ? route('decanat.domaines.update', $domaine) : route('decanat.domaines.store') }}">
        @csrf
        @if($domaine->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
                <input id="nom" name="nom" value="{{ old('nom', $domaine->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="faculte_id">Faculté</label>
                <select id="faculte_id" name="faculte_id" class="form-select" @if(auth()->user()->isDecanat()) disabled @endif>
                    @foreach($facultes as $faculte)
                        <option value="{{ $faculte->id }}" @selected((string)old('faculte_id', $domaine->faculte_id) === (string)$faculte->id)>{{ $faculte->nom }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->isDecanat())
                    <input type="hidden" name="faculte_id" value="{{ auth()->user()->faculte_id }}">
                @endif
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $domaine->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="actif">Statut</label>
                <select id="actif" name="actif" class="form-select">
                    <option value="1" @selected(old('actif', $domaine->actif ?? true))>Actif</option>
                    <option value="0" @selected(!old('actif', $domaine->actif ?? true))>Inactif</option>
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.domaines.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
