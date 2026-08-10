@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $title }}s</h1>
    <a class="btn btn-primary" href="{{ route($route . '.create') }}"><i class="bi bi-plus-lg"></i> Ajouter</a>
</div>

<form class="surface p-3 mb-3">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher...">
        <button class="btn btn-outline-primary">Filtrer</button>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr>
            @foreach(array_keys($fields) as $field)<th>{{ ucfirst(str_replace('_', ' ', $field)) }}</th>@endforeach
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                @foreach(array_keys($fields) as $field)
                    <td>{{ is_bool($item->$field) ? ($item->$field ? 'Oui' : 'Non') : $item->$field }}</td>
                @endforeach
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route($route . '.edit', $item) }}"><i class="bi bi-pencil"></i></a>
                    <form class="d-inline" method="POST" action="{{ route($route . '.destroy', $item) }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet élément ?')"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="99" class="text-center text-muted py-4">Aucune donnée.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection
