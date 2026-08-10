@extends('layouts.app')

@section('title', 'Enseignants')

@section('content')
@php $facultes = \App\Models\Faculte::orderBy('nom')->get(); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Enseignants</h1>
        <small class="text-muted">Corps enseignant de la plateforme</small>
    </div>
    <a class="btn btn-primary" href="{{ route('enseignants.create') }}"><i class="bi bi-plus-lg"></i> Ajouter un enseignant</a>
</div>

<form method="GET" action="{{ route('enseignants.index') }}" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher par nom, prénom ou e-mail…">
        </div>
        <div class="col-md-4">
            <select name="faculte_id" class="form-select">
                <option value="">Toutes les facultés</option>
                @foreach($facultes as $faculte)
                    <option value="{{ $faculte->id }}" @selected(request('faculte_id') == $faculte->id)>{{ $faculte->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel"></i></button>
            @if(request()->has('q') || request()->filled('faculte_id'))
                <a href="{{ route('enseignants.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </div>
</form>

@php $showCours = $enseignants->isNotEmpty() && isset($enseignants->first()->coursEnseignes_count); @endphp

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Faculté</th>
            @if($showCours)<th>Cours enseignés</th>@endif
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($enseignants as $e)
            <tr>
                <td class="fw-semibold">{{ $e->nom_complet }}</td>
                <td>{{ $e->email }}</td>
                <td>{{ $e->telephone ?: '-' }}</td>
                <td>{{ optional($e->faculte)->nom ?: '-' }}</td>
                @if($showCours)<td>{{ $e->coursEnseignes_count }}</td>@endif
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('enseignants.edit', $e) }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('enseignants.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Supprimer cet enseignant ?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ $showCours ? 6 : 5 }}" class="text-center text-muted py-4">Aucun enseignant trouvé.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $enseignants->links() }}</div>
</div>
@endsection
