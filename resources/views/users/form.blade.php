@extends('layouts.app')

@section('title', $user->exists ? 'Modifier l\'utilisateur' : 'Créer un utilisateur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $user->exists ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}</h1>
        <small class="text-muted">Gestion des comptes de la plateforme</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('users.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

@php
    $roleLabels = ['admin' => 'Administrateur', 'decanat' => 'Décanat', 'enseignant' => 'Enseignant', 'etudiant' => 'Étudiant'];
    $statusLabels = ['pending' => 'En attente', 'accepted' => 'Accepté', 'rejected' => 'Refusé'];
@endphp

<div class="surface p-4">
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if($user->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nom">Nom</label>
                <input id="nom" type="text" name="nom" value="{{ old('nom', $user->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="prenom">Prénom</label>
                <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="form-control @error('prenom') is-invalid @enderror" required>
                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telephone">Téléphone</label>
                <input id="telephone" type="text" name="telephone" value="{{ old('telephone', $user->telephone) }}" class="form-control @error('telephone') is-invalid @enderror">
                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="postnom">Postnom</label>
                <input id="postnom" type="text" name="postnom" value="{{ old('postnom', $user->postnom) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Mot de passe {{ $user->exists ? '(laisser vide pour conserver)' : '' }}</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" @unless($user->exists) required @endunless>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="role">Rôle</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">Choisir un rôle…</option>
                    @foreach(['admin' => 'Administrateur', 'decanat' => 'Décanat'] as $role => $label)
                        <option value="{{ $role }}" @selected((string)old('role', $user->role ?: request('role')) === $role)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">Les étudiants et enseignants sont créés uniquement par le Décanat.</div>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="status">Statut</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="">Choisir un statut…</option>
                    @foreach(\App\Models\User::STATUSES as $status)
                        <option value="{{ $status }}" @selected((string)old('status', $user->status) === $status)>{{ $statusLabels[$status] ?? $status }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="faculte_id">Faculté</label>
                <select id="faculte_id" name="faculte_id" class="form-select @error('faculte_id') is-invalid @enderror">
                    <option value="">Aucune faculté</option>
                    @foreach($facultes as $faculte)
                        <option value="{{ $faculte->id }}" @selected((string)old('faculte_id', $user->faculte_id) === (string)$faculte->id)>{{ $faculte->nom }}</option>
                    @endforeach
                </select>
                @error('faculte_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @isset($promotions)
                <div class="col-md-6">
                    <label class="form-label" for="promotion_id">Promotion</label>
                    <select id="promotion_id" name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror">
                        <option value="">Aucune promotion</option>
                        @foreach($promotions as $promotion)
                            <option value="{{ $promotion->id }}" @selected((string)old('promotion_id', $user->promotion_id) === (string)$promotion->id)>{{ $promotion->nom }}</option>
                        @endforeach
                    </select>
                    @error('promotion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endisset
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
