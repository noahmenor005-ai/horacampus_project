@extends('layouts.app')

@section('title', $disponibilite->exists ? 'Modifier la disponibilité' : 'Ajouter une disponibilité')

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $disponibilite->exists ? 'Modifier' : 'Ajouter' }} une disponibilité</h1>
    <form method="POST" action="{{ $disponibilite->exists ? route('disponibilites.update', $disponibilite) : route('disponibilites.store') }}">
        @csrf
        @if($disponibilite->exists) @method('PUT') @endif
        <div class="row g-3">
            @if(auth()->user()->isAdmin() || auth()->user()->isDecanat())
                <div class="col-md-6">
                    <label class="form-label">Enseignant</label>
                    <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                        <option value="">Choisir un enseignant...</option>
                        @foreach($enseignants as $id => $label)
                            <option value="{{ $id }}" @selected(old('user_id', $disponibilite->user_id) == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
            <div class="col-md-6">
                <label class="form-label">Semestre</label>
                <select name="semestre_id" class="form-select @error('semestre_id') is-invalid @enderror">
                    <option value="">Non précisé</option>
                    @foreach($semestres as $id => $label)
                        <option value="{{ $id }}" @selected(old('semestre_id', $disponibilite->semestre_id) == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('semestre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Jour</label>
                <select name="jour" class="form-select @error('jour') is-invalid @enderror" required>
                    <option value="">Choisir un jour...</option>
                    @foreach(\App\Models\User::JOURS as $jour)
                        <option value="{{ $jour }}" @selected(old('jour', $disponibilite->jour) === $jour)>{{ $jour }}</option>
                    @endforeach
                </select>
                @error('jour')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Heure de début</label>
                <input type="time" name="heure_debut" value="{{ old('heure_debut', $disponibilite->heure_debut) }}" class="form-control @error('heure_debut') is-invalid @enderror" required>
                @error('heure_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Heure de fin</label>
                <input type="time" name="heure_fin" value="{{ old('heure_fin', $disponibilite->heure_fin) }}" class="form-control @error('heure_fin') is-invalid @enderror" required>
                @error('heure_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isDecanat())
                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="">Choisir...</option>
                        @foreach(\App\Models\Disponibilite::STATUTS as $value => $label)
                            <option value="{{ $value }}" @selected(old('statut', $disponibilite->statut) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('disponibilites.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
