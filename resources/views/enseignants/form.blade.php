@extends('layouts.app')

@section('title', $enseignant->exists ? 'Modifier l\'enseignant' : 'Ajouter un enseignant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $enseignant->exists ? 'Modifier l\'enseignant' : 'Ajouter un enseignant' }}</h1>
        <small class="text-muted">Décanat — {{ optional(auth()->user()->faculte)->nom }} — Le système attribue automatiquement les identifiants de connexion.</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('enseignants.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

@php $assignedEcs = old('ec_ids', isset($enseignant) ? $enseignant->ecs->pluck('id')->all() : []); @endphp

@if(!$enseignant->exists)
    <div class="alert alert-primary"><i class="bi bi-key"></i> <strong>Authentification :</strong> L'enseignant sera créé avec un compte actif. Ses identifiants (email + mot de passe initial) vous seront affichés après création et à communiquer à l'intéressé. Il pourra consulter son emploi du temps, ses cours, UE, EC, salles et disponibilités.</div>
@endif

<div class="surface p-4">
    <form method="POST" action="{{ $enseignant->exists ? route('enseignants.update', $enseignant) : route('enseignants.store') }}">
        @csrf
        @if($enseignant->exists)
            @method('PUT')
        @endif

        <h5 class="mb-3 border-bottom pb-2">Identité</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
                <input id="nom" type="text" name="nom" value="{{ old('nom', $enseignant->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="postnom">Postnom</label>
                <input id="postnom" type="text" name="postnom" value="{{ old('postnom', $enseignant->postnom) }}" class="form-control @error('postnom') is-invalid @enderror">
                @error('postnom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="prenom">Prénom <span class="text-danger">*</span></label>
                <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $enseignant->prenom) }}" class="form-control @error('prenom') is-invalid @enderror" required>
                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label" for="matricule">Matricule <span class="text-muted small">(auto si vide)</span></label>
                <input id="matricule" type="text" name="matricule" value="{{ old('matricule', $enseignant->matricule) }}" class="form-control @error('matricule') is-invalid @enderror" placeholder="Ex: ENS-XYZ123">
                @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="sexe">Sexe</label>
                <select id="sexe" name="sexe" class="form-select @error('sexe') is-invalid @enderror">
                    <option value="">Choisir…</option>
                    <option value="M" @selected(old('sexe', $enseignant->sexe)==='M')>M</option>
                    <option value="F" @selected(old('sexe', $enseignant->sexe)==='F')>F</option>
                    <option value="Autre" @selected(old('sexe', $enseignant->sexe)==='Autre')>Autre</option>
                </select>
                @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="telephone">Téléphone</label>
                <input id="telephone" type="text" name="telephone" value="{{ old('telephone', $enseignant->telephone) }}" class="form-control @error('telephone') is-invalid @enderror">
                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="grade">Grade</label>
                <input id="grade" type="text" name="grade" value="{{ old('grade', $enseignant->grade) }}" class="form-control @error('grade') is-invalid @enderror" placeholder="Ex: Professeur, Chef de travaux">
                @error('grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Adresse e-mail <span class="text-danger">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $enseignant->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                <div class="form-text">C'est l'identifiant de connexion de l'enseignant.</div>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="faculte_id">Faculté</label>
                <select id="faculte_id" name="faculte_id" class="form-select @error('faculte_id') is-invalid @enderror" @if(auth()->user()->isDecanat()) disabled @endif>
                    <option value="">Aucune</option>
                    @foreach($facultes as $faculte)
                        <option value="{{ $faculte->id }}" @selected((string)old('faculte_id', $enseignant->faculte_id) === (string)$faculte->id || (auth()->user()->isDecanat() && (int)$faculte->id===(int)auth()->user()->faculte_id))>{{ $faculte->nom }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->isDecanat())
                    <input type="hidden" name="faculte_id" value="{{ auth()->user()->faculte_id }}">
                @endif
                @error('faculte_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="specialite">Spécialité</label>
                <input id="specialite" type="text" name="specialite" value="{{ old('specialite', $enseignant->specialite) }}" class="form-control @error('specialite') is-invalid @enderror" placeholder="Ex: Intelligence artificielle">
                @error('specialite')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label" for="ec_ids">Éléments constitutifs assignés</label>
                <select id="ec_ids" name="ec_ids[]" multiple size="8" class="form-select @error('ec_ids') is-invalid @enderror">
                    @foreach($ecs as $id => $label)
                        <option value="{{ $id }}" @if(in_array($id, (array)$assignedEcs, true)) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">Maintenez Ctrl pour sélectionner plusieurs EC. Filtrés selon la faculté du Décanat.</div>
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
