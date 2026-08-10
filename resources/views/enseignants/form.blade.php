@extends('layouts.app')

@section('title', $enseignant->exists ? 'Modifier l\'enseignant' : 'Ajouter un enseignant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $enseignant->exists ? 'Modifier l\'enseignant' : 'Ajouter un enseignant' }}</h1>
        <small class="text-muted">Corps enseignant de la plateforme</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('enseignants.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

@php $assignedEcs = old('ec_ids', $enseignant->ecs->pluck('id')->all()); @endphp

<div class="surface p-4">
    <form method="POST" action="{{ $enseignant->exists ? route('enseignants.update', $enseignant) : route('enseignants.store') }}">
        @csrf
        @if($enseignant->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nom">Nom</label>
                <input id="nom" type="text" name="nom" value="{{ old('nom', $enseignant->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="prenom">Prénom</label>
                <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $enseignant->prenom) }}" class="form-control @error('prenom') is-invalid @enderror" required>
                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $enseignant->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telephone">Téléphone</label>
                <input id="telephone" type="text" name="telephone" value="{{ old('telephone', $enseignant->telephone) }}" class="form-control @error('telephone') is-invalid @enderror">
                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="faculte_id">Faculté</label>
                <select id="faculte_id" name="faculte_id" class="form-select @error('faculte_id') is-invalid @enderror">
                    <option value="">Aucune faculté</option>
                    @foreach($facultes as $faculte)
                        <option value="{{ $faculte->id }}" @selected((string)old('faculte_id', $enseignant->faculte_id) === (string)$faculte->id)>{{ $faculte->nom }}</option>
                    @endforeach
                </select>
                @error('faculte_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if(!$enseignant->exists)
                <div class="col-md-6">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirmation du mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                </div>
            @endif

            <div class="col-12">
                <label class="form-label" for="ec_ids">Éléments constitutifs assignés</label>
                <select id="ec_ids" name="ec_ids[]" multiple size="8" class="form-select @error('ec_ids') is-invalid @enderror">
                    @foreach($ecs as $id => $label)
                        <option value="{{ $id }}" @if(in_array($id, $assignedEcs, true)) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">Maintenez la touche Ctrl pour sélectionner plusieurs éléments.</div>
                @error('ec_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
