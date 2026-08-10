@extends('layouts.app')

@section('title', 'Disponibilités')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
    <h1 class="h3 mb-0">Disponibilités</h1>
    @can('create', \App\Models\Disponibilite::class)
        <a class="btn btn-primary" href="{{ route('disponibilites.create') }}"><i class="bi bi-plus-lg"></i> Ajouter une disponibilité</a>
    @endcan
</div>

<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-select">
                <option value="">Tous</option>
                @foreach(\App\Models\Disponibilite::STATUTS as $value => $label)
                    <option value="{{ $value }}" @selected(request('statut') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->isDecanat())
            <div class="col-md-3">
                <label class="form-label">Enseignant</label>
                <select name="enseignant_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($enseignants as $id => $label)
                        <option value="{{ $id }}" @selected(request('enseignant_id') == $id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-3">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
        </div>
    </div>
</form>

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Enseignant</th><th>Jour</th><th>Horaire</th><th>Semestre</th><th>Statut</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
        @forelse($disponibilites as $d)
            <tr>
                <td>{{ $d->user?->nom_complet }}</td>
                <td>{{ $d->jour }}</td>
                <td>{{ substr($d->heure_debut, 0, 5) }} - {{ substr($d->heure_fin, 0, 5) }}</td>
                <td>{{ optional($d->semestre)->libelle ?: '—' }}</td>
                <td><span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    @can('update', $d)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('disponibilites.edit', $d) }}"><i class="bi bi-pencil"></i></a>
                    @endcan
                    @can('delete', $d)
                        <form method="POST" action="{{ route('disponibilites.destroy', $d) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette disponibilité ?')"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                    @if((auth()->user()->isAdmin() || auth()->user()->isDecanat()) && $d->statut === \App\Models\Disponibilite::STATUT_EN_ATTENTE)
                        <form method="POST" action="{{ route('disponibilites.status', $d) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut" value="validee">
                            <button class="btn btn-sm btn-success">Valider</button>
                        </form>
                        <form method="POST" action="{{ route('disponibilites.status', $d) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut" value="refusee">
                            <button class="btn btn-sm btn-outline-danger">Refuser</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune disponibilité trouvée.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $disponibilites->links() }}</div>
</div>
@endsection
