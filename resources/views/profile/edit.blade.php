@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<h1 class="h3 mb-3">Mon profil</h1>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="surface p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                @if(auth()->user()->photo_path)
                    <img src="{{ asset('storage/' . auth()->user()->photo_path) }}" alt="Photo de profil" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                @endif
                <div>
                    <div class="fw-semibold">{{ auth()->user()->nom_complet }}</div>
                    <div class="text-muted small">{{ auth()->user()->email }}</div>
                    <span class="badge text-bg-light">{{ auth()->user()->roleLabel() }}</span>
                </div>
            </div>

            <h2 class="h5 mb-3">Informations personnelles</h2>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input name="nom" value="{{ old('nom', auth()->user()->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input name="prenom" value="{{ old('prenom', auth()->user()->prenom) }}" class="form-control @error('prenom') is-invalid @enderror" required>
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input name="telephone" value="{{ old('telephone', auth()->user()->telephone) }}" class="form-control @error('telephone') is-invalid @enderror">
                    @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Photo de profil</label>
                    <input type="file" name="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="surface p-4">
            <h2 class="h5 mb-3">Changer le mot de passe</h2>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmation du nouveau mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button class="btn btn-primary"><i class="bi bi-key"></i> Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
@endsection
