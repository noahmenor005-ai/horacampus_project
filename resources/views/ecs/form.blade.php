@extends('layouts.app')
@section('title', $ec->exists ? 'Modifier l\'EC' : 'Ajouter un EC')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ $ec->exists ? 'Modifier' : 'Ajouter' }} un élément constitutif</h1>
    <a class="btn btn-outline-secondary" href="{{ route('decanat.ecs.index') }}">Retour</a>
</div>
<div class="surface p-4">
    @include('partials.form-errors')
    <form method="POST" action="{{ $ec->exists ? route('decanat.ecs.update', $ec) : route('decanat.ecs.store') }}">
        @csrf
        @if($ec->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">UE <span class="text-danger">*</span></label>
                <select name="ue_id" class="form-select @error('ue_id') is-invalid @enderror" required>
                    <option value="">Choisir une UE…</option>
                    @foreach($ues as $ue)
                        <option value="{{ $ue->id }}" @selected((string)old('ue_id', $ec->ue_id ?? request('ue_id'))===(string)$ue->id)>{{ $ue->code }} — {{ $ue->nom }} ({{ optional($ue->promotion)->nom }})</option>
                    @endforeach
                </select>
                @error('ue_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input name="code" value="{{ old('code', $ec->code) }}" class="form-control @error('code') is-invalid @enderror" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Heures <span class="text-danger">*</span></label>
                <input type="number" min="1" name="volume_horaire" value="{{ old('volume_horaire', $ec->volume_horaire) }}" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                <input name="nom" value="{{ old('nom', $ec->nom) }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Crédits</label>
                <input type="number" min="0" name="credit" value="{{ old('credit', $ec->credit) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Coefficient</label>
                <input type="number" min="0" name="coefficient" value="{{ old('coefficient', $ec->coefficient ?? 1) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Enseignant responsable</label>
                <select name="enseignant_id" class="form-select">
                    <option value="">Aucun</option>
                    @foreach($enseignants as $enseignant)
                        <option value="{{ $enseignant->id }}" @selected((string)old('enseignant_id', $ec->enseignant_id)===(string)$enseignant->id)>{{ $enseignant->nom_complet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    @foreach(\App\Models\Ec::STATUTS as $value => $label)
                        <option value="{{ $value }}" @selected(old('statut', $ec->statut ?? 'actif')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('decanat.ecs.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
