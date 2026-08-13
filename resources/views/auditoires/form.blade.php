@extends('layouts.app')

@section('title', $item->exists ? 'Modifier l\'auditoire' : 'Ajouter un auditoire')

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $item->exists ? 'Modifier' : 'Ajouter' }} un auditoire</h1>
    @include('partials.form-errors')
    @php $selectedEquip = old('equipements', $item->equipementsList()); @endphp
    <form method="POST" action="{{ $item->exists ? route('auditoires.update', $item) : route('auditoires.store') }}">
        @csrf
        @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Numéro</label>
                <input name="nom" value="{{ old('nom', $item->nom) }}" class="form-control" required>
                <input type="hidden" name="numero" value="{{ old('numero', $item->numero) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bâtiment</label>
                <select name="batiment_id" class="form-select" required>
                    <option value="">Choisir…</option>
                    @foreach($batiments as $b)
                        <option value="{{ $b->id }}" @selected(old('batiment_id', $item->batiment_id)==$b->id)>{{ $b->code }} — {{ $b->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Capacité</label>
                <input type="number" min="1" name="capacite" value="{{ old('capacite', $item->capacite) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                    @foreach(\App\Models\Auditoire::TYPES as $k => $v)
                        @if($k !== 'attente')
                            <option value="{{ $k }}" @selected(old('type', $item->type)===$k)>{{ $v }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">État</label>
                <select name="etat" class="form-select" required>
                    @foreach(\App\Models\Auditoire::ETATS as $k => $v)
                        <option value="{{ $k }}" @selected(old('etat', $item->etat)===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Disponibilité</label>
                <select name="disponibilite" class="form-select">
                    <option value="1" @selected(old('disponibilite', $item->disponibilite) == 1)>Disponible</option>
                    <option value="0" @selected(old('disponibilite', $item->disponibilite) == 0)>Indisponible</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Équipements</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach(\App\Models\Auditoire::EQUIPEMENTS as $k => $v)
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="equipements[]" value="{{ $k }}" @checked(in_array($k, $selectedEquip, true) || collect($selectedEquip)->contains(fn($e) => str_contains($e, $k)))>
                            <span class="form-check-label">{{ $v }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('auditoires.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
