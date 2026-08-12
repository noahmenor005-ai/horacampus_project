@extends('layouts.app')

@section('title', 'Demandes de salle')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
    <h1 class="h3 mb-0">Demandes de salle</h1>
    @can('create', \App\Models\DemandeAuditoire::class)
        <a class="btn btn-primary" href="{{ route('demandes.create') }}"><i class="bi bi-plus-lg"></i> Nouvelle demande</a>
    @endcan
</div>

<form method="GET" class="surface p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Rechercher</label>
            <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cours, enseignant, promotion…">
        </div>
        <div class="col-md-3">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-select">
                <option value="">Tous</option>
                @foreach(\App\Models\DemandeAuditoire::STATUTS as $value => $label)
                    <option value="{{ $value }}" @selected(request('statut') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Promotion</label>
            <select name="promotion_id" class="form-select">
                <option value="">Toutes</option>
                @foreach($promotions as $promotion)
                    <option value="{{ $promotion->id }}" @selected(request('promotion_id') == $promotion->id)>{{ $promotion->nom }}</option>
                @endforeach
            </select>
        </div>
        @if(isset($enseignants))
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
        <tr><th>Cours</th><th>Date</th><th>Horaire</th><th>Promotion</th><th>Enseignant</th><th>Salle</th><th>Statut</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
        @forelse($demandes as $d)
            <tr>
                <td>{{ $d->cours?->intitule }}</td>
                <td>{{ $d->date?->format('d/m/Y') }}</td>
                <td>{{ substr($d->heure_debut, 0, 5) }} - {{ substr($d->heure_fin, 0, 5) }}</td>
                <td>{{ optional($d->promotion)->nom }}</td>
                <td>{{ optional($d->enseignant)->nom_complet }}</td>
                <td>{{ optional($d->auditoire)->nom ?: '—' }}</td>
                <td><span class="badge text-bg-{{ $d->badgeClass() }}">{{ $d->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('demandes.show', $d) }}"><i class="bi bi-eye"></i></a>
                    @can('update', $d)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('demandes.edit', $d) }}"><i class="bi bi-pencil"></i></a>
                    @endcan
                    @can('delete', $d)
                        <form method="POST" action="{{ route('demandes.destroy', $d) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette demande ?')"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Aucune demande trouvée.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $demandes->links() }}</div>
</div>
@endsection
