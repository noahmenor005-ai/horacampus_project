@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $item->exists ? 'Modifier' : 'Ajouter' }} : {{ $title }}</h1>
    <form method="POST" action="{{ $item->exists ? route($route . '.update', $item) : route($route . '.store') }}">
        @csrf
        @if($item->exists) @method('PUT') @endif
        <div class="row g-3">
            @foreach($fields as $name => $rules)
                <div class="col-md-6">
                    <label class="form-label">{{ ucfirst(str_replace('_', ' ', $name)) }}</label>
                    @if(isset($selects[$name]))
                        <select name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" @if(str_contains($rules, 'required')) required @endif>
                            <option value="">Choisir...</option>
                            @foreach($selects[$name] as $id => $label)
                                <option value="{{ $id }}" @selected(old($name, $item->$name) == $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @elseif($name === 'description')
                        <textarea name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">{{ old($name, $item->$name) }}</textarea>
                    @elseif($name === 'disponibilite')
                        <select name="{{ $name }}" class="form-select">
                            <option value="1" @selected(old($name, $item->$name) == 1)>Disponible</option>
                            <option value="0" @selected(old($name, $item->$name) == 0)>Indisponible</option>
                        </select>
                    @else
                        <input name="{{ $name }}" value="{{ old($name, $item->$name) }}" type="{{ str_contains($name, 'email') ? 'email' : (str_contains($name, 'credit') || str_contains($name, 'capacite') ? 'number' : 'text') }}" class="form-control @error($name) is-invalid @enderror" @if(str_contains($rules, 'required')) required @endif>
                    @endif
                    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route($route . '.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
