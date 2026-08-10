@extends('layouts.app')

@section('title', $cours->exists ? 'Modifier le cours' : 'Créer un cours')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $cours->exists ? 'Modifier le cours' : 'Créer un cours' }}</h1>
        <small class="text-muted">Assignation des cours par promotion</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('cours.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

<div class="surface p-4">
    <form method="POST" action="{{ $cours->exists ? route('cours.update', $cours) : route('cours.store') }}">
        @csrf
        @if($cours->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="ec_id">Élément constitutif</label>
                <select id="ec_id" name="ec_id" class="form-select @error('ec_id') is-invalid @enderror" required>
                    <option value="">Choisir un EC…</option>
                    @foreach($ecs as $id => $label)
                        <option value="{{ $id }}" @selected((string)old('ec_id', $cours->ec_id) === (string)$id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('ec_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="promotion_id">Promotion</label>
                <select id="promotion_id" name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror" required>
                    <option value="">Choisir une promotion…</option>
                    @foreach($promotions as $p)
                        <option value="{{ $p->id }}" @selected((string)old('promotion_id', $cours->promotion_id) === (string)$p->id)>{{ $p->nom }}</option>
                    @endforeach
                </select>
                @error('promotion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="enseignant_id">Enseignant</label>
                <select id="enseignant_id" name="enseignant_id" class="form-select @error('enseignant_id') is-invalid @enderror" required>
                    <option value="">Choisir un enseignant…</option>
                    @foreach($enseignants as $id => $label)
                        <option value="{{ $id }}" @selected((string)old('enseignant_id', $cours->enseignant_id) === (string)$id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('enseignant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="type">Type</label>
                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="">Choisir un type…</option>
                    @foreach(\App\Models\Cours::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $cours->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="volume_horaire">Volume horaire (heures)</label>
                <input id="volume_horaire" type="number" name="volume_horaire" min="1" value="{{ old('volume_horaire', $cours->volume_horaire) }}" class="form-control @error('volume_horaire') is-invalid @enderror" required>
                @error('volume_horaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('cours.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
