@extends('layouts.app')

@section('title', $item->exists ? 'Modifier le bâtiment' : 'Ajouter un bâtiment')

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $item->exists ? 'Modifier' : 'Ajouter' }} un bâtiment</h1>
    @include('partials.form-errors')
    <form method="POST" action="{{ $item->exists ? route('batiments.update', $item) : route('batiments.store') }}">
        @csrf
        @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input name="code" value="{{ old('code', $item->code) }}" class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nom</label>
                <input name="nom" value="{{ old('nom', $item->nom) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Localisation</label>
                <input name="localisation" value="{{ old('localisation', $item->localisation ?: $item->adresse) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombre d’étages</label>
                <input type="number" min="0" name="nombre_etages" value="{{ old('nombre_etages', $item->nombre_etages) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select" required>
                    @foreach(\App\Models\Batiment::STATUTS as $k => $v)
                        <option value="{{ $k }}" @selected(old('statut', $item->statut ?? 'actif')===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('batiments.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
