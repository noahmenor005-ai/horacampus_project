@extends('layouts.app')

@section('title', $horaire->exists ? 'Modifier un horaire' : 'Programmer un horaire')

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $horaire->exists ? 'Modifier' : 'Programmer' }} un horaire</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Veuillez corriger les erreurs suivantes :</div>
            <ul class="mb-0">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $horaire->exists ? route('horaires.update', $horaire) : route('horaires.store') }}">
        @csrf
        @if($horaire->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="cours_id">Cours</label>
                <select id="cours_id" name="cours_id" class="form-select @error('cours_id') is-invalid @enderror" required>
                    <option value="">Choisir un cours...</option>
                    @foreach($cours as $id => $label)
                        <option value="{{ $id }}" @selected(old('cours_id', $horaire->cours_id ?? null) == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('cours_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="auditoire_id">Auditoire</label>
                <select id="auditoire_id" name="auditoire_id" class="form-select @error('auditoire_id') is-invalid @enderror" required>
                    <option value="">Choisir un auditoire...</option>
                    @foreach($auditoires as $id => $label)
                        <option value="{{ $id }}" @selected(old('auditoire_id', $horaire->auditoire_id ?? null) == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('auditoire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="date">Date</label>
                <input id="date" type="date" name="date" value="{{ old('date', $horaire->date?->format('Y-m-d') ?? null) }}" class="form-control @error('date') is-invalid @enderror" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="heure_debut">Heure de début</label>
                <input id="heure_debut" type="time" name="heure_debut" value="{{ old('heure_debut', $horaire->heure_debut ?? null) }}" class="form-control @error('heure_debut') is-invalid @enderror" required>
                @error('heure_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="heure_fin">Heure de fin</label>
                <input id="heure_fin" type="time" name="heure_fin" value="{{ old('heure_fin', $horaire->heure_fin ?? null) }}" class="form-control @error('heure_fin') is-invalid @enderror" required>
                @error('heure_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="semestre_id">Semestre</label>
                <select id="semestre_id" name="semestre_id" class="form-select @error('semestre_id') is-invalid @enderror">
                    <option value="">Aucun</option>
                    @foreach($semestres as $id => $label)
                        <option value="{{ $id }}" @selected(old('semestre_id', $horaire->semestre_id ?? null) == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('semestre_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="effectif_attendu">Effectif attendu</label>
                <input id="effectif_attendu" type="number" name="effectif_attendu" min="0" value="{{ old('effectif_attendu', $horaire->effectif_attendu ?? null) }}" class="form-control @error('effectif_attendu') is-invalid @enderror">
                @error('effectif_attendu')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="statut">Statut</label>
                <select id="statut" name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                    @foreach(\App\Models\Horaire::STATUTS as $value => $label)
                        <option value="{{ $value }}" @selected(old('statut', $horaire->statut ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('horaires.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
