@extends('layouts.app')

@section('title', $demande->exists ? 'Modifier la demande' : 'Nouvelle demande')

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $demande->exists ? 'Modifier' : 'Nouvelle' }} demande de salle</h1>
    <form method="POST" action="{{ $demande->exists ? route('demandes.update', $demande) : route('demandes.store') }}">
        @csrf
        @if($demande->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Cours</label>
                <select name="cours_id" class="form-select @error('cours_id') is-invalid @enderror" required>
                    <option value="">Choisir un cours...</option>
                    @foreach($cours as $id => $label)
                        <option value="{{ $id }}" @selected(old('cours_id', $demande->cours_id) == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('cours_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Semestre</label>
                <select name="semestre_id" class="form-select @error('semestre_id') is-invalid @enderror">
                    <option value="">Non précisé</option>
                    @foreach($semestres as $semestre)
                        <option value="{{ $semestre->id }}" @selected(old('semestre_id', $demande->semestre_id) == $semestre->id)>{{ $semestre->libelle }}</option>
                    @endforeach
                </select>
                @error('semestre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="date" value="{{ old('date', optional($demande->date)->format('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="form-control @error('date') is-invalid @enderror" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Heure de début</label>
                <input type="time" name="heure_debut" value="{{ old('heure_debut', $demande->heure_debut) }}" class="form-control @error('heure_debut') is-invalid @enderror" required>
                @error('heure_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Heure de fin</label>
                <input type="time" name="heure_fin" value="{{ old('heure_fin', $demande->heure_fin) }}" class="form-control @error('heure_fin') is-invalid @enderror" required>
                @error('heure_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Effectif attendu</label>
                <input type="number" name="effectif_attendu" value="{{ old('effectif_attendu', $demande->effectif_attendu) }}" min="1" class="form-control @error('effectif_attendu') is-invalid @enderror" required>
                @error('effectif_attendu')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if($demande->horaire_id)
                <input type="hidden" name="horaire_id" value="{{ $demande->horaire_id }}">
            @endif
            <div class="col-12">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" rows="3" class="form-control @error('commentaire') is-invalid @enderror">{{ old('commentaire', $demande->commentaire ?? $demande->note) }}</textarea>
                @error('commentaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="alert alert-info mt-4 mb-0">
            <i class="bi bi-info-circle"></i> La demande sera transmise à l'administration pour attribution de salle.
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('demandes.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
