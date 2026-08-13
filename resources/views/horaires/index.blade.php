@extends('layouts.app')

@section('title', 'Horaires')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
    <h1 class="h3 mb-0">Horaires</h1>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary {{ ($vue ?? 'grille') === 'grille' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['vue' => 'grille']) }}"><i class="bi bi-grid-3x3"></i> Grille</a>
        <a class="btn btn-outline-secondary {{ ($vue ?? '') === 'liste' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['vue' => 'liste']) }}"><i class="bi bi-list-ul"></i> Liste</a>
        <a class="btn btn-outline-secondary" target="_blank" href="{{ route('horaires.print', request()->query()) }}"><i class="bi bi-printer"></i> Imprimer</a>
        <a class="btn btn-outline-success" href="{{ route('horaires.export', request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
        @can('create', \App\Models\Horaire::class)
            <a class="btn btn-primary" href="{{ auth()->user()->isDecanat() ? route('decanat.horaires.create') : route('horaires.create') }}"><i class="bi bi-plus-lg"></i> Programmer</a>
        @endcan
    </div>
</div>

<form class="surface p-3 mb-3" method="GET" action="{{ auth()->user()->isDecanat() ? route('decanat.horaires.index') : route('horaires.index') }}" id="filterForm">
    <input type="hidden" name="vue" value="{{ $vue ?? 'grille' }}">
    <div class="row g-2 align-items-end">
        <div class="col-lg-3">
            <label class="form-label" for="q">Recherche</label>
            <input id="q" type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cours, enseignant, salle, promotion...">
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label" for="jour">Jour</label>
            <select id="jour" name="jour" class="form-select">
                <option value="">Tous</option>
                @foreach($jours as $jour)<option value="{{ $jour }}" @selected(request('jour') === $jour)>{{ $jour }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label" for="promotion_id">Promotion</label>
            <select id="promotion_id" name="promotion_id" class="form-select">
                <option value="">Toutes</option>
                @foreach($promotions as $id => $label)
                    @php $pid = is_object($label) ? $label->id : $id; $plabel = is_object($label) ? $label->nom : $label; @endphp
                    <option value="{{ $pid }}" @selected(request('promotion_id') == $pid)>{{ $plabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label" for="enseignant_id">Enseignant</label>
            <select id="enseignant_id" name="enseignant_id" class="form-select">
                <option value="">Tous</option>
                @foreach($enseignants as $id => $label)
                    @php $eid = is_object($label) ? $label->id : $id; $elabel = is_object($label) ? $label->nom_complet : $label; @endphp
                    <option value="{{ $eid }}" @selected(request('enseignant_id') == $eid)>{{ $elabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2">
            <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
        </div>
    </div>
</form>

@if(($vue ?? 'grille') !== 'liste')
    <div class="surface p-3 mb-3">
        @include('partials.timetable-grid', ['grille' => $grille])
    </div>
@endif

<div class="surface p-3 table-responsive">
    <table class="table align-middle mb-0">
        <thead>
        <tr><th>Jour</th><th>Date</th><th>Heure</th><th>EC</th><th>Salle</th><th>Enseignant</th><th>Promotion</th><th>Statut</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
        @forelse($horaires as $h)
            <tr>
                <td><span class="badge text-bg-light">{{ $h->jour }}</span></td>
                <td>{{ $h->date?->format('d/m/Y') }}</td>
                <td>{{ substr($h->heure_debut, 0, 5) }} - {{ substr($h->heure_fin, 0, 5) }}</td>
                <td>{{ optional($h->ec)->nom ?: optional($h->cours)->intitule }}</td>
                <td>{{ optional($h->auditoire)->nom === 'EN-ATTENTE' ? 'En attente' : optional($h->auditoire)->nom }}</td>
                <td>{{ optional($h->enseignant)->nom_complet }}</td>
                <td>{{ optional($h->promotion)->nom }}</td>
                <td><span class="badge text-bg-{{ $h->badgeClass() }}">{{ $h->statutLabel() }}</span></td>
                <td class="text-end text-nowrap">
                    @php
                        $showRoute = auth()->user()->isDecanat() ? route('decanat.horaires.show', $h) : route('horaires.show', $h);
                        $editRoute = auth()->user()->isDecanat() ? route('decanat.horaires.edit', $h) : route('horaires.edit', $h);
                        $destroyRoute = auth()->user()->isDecanat() ? route('decanat.horaires.destroy', $h) : route('horaires.destroy', $h);
                        $askRoute = auth()->user()->isDecanat() ? route('decanat.horaires.demander-salle', $h) : route('horaires.demander-salle', $h);
                    @endphp
                    <a class="btn btn-sm btn-outline-secondary" href="{{ $showRoute }}" title="Voir"><i class="bi bi-eye"></i></a>
                    @can('update', $h)
                        <a class="btn btn-sm btn-outline-primary" href="{{ $editRoute }}" title="Modifier"><i class="bi bi-pencil"></i></a>
                    @endcan
                    @can('create', \App\Models\DemandeAuditoire::class)
                        @if(!$h->hasSalle() && !$h->demandeEnAttente())
                            <form method="POST" action="{{ $askRoute }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-success" title="Demander une salle"><i class="bi bi-door-open"></i></button>
                            </form>
                        @endif
                    @endcan
                    @can('delete', $h)
                        <form method="POST" action="{{ $destroyRoute }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet horaire ?')"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Aucun horaire trouvé.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $horaires->links() }}</div>
</div>
@endsection
