@extends('layouts.app')

@section('title', $horaire->exists ? 'Modifier un horaire' : 'Programmer un horaire')

@php
    $isDecanat = request()->routeIs('decanat.*') || (auth()->user() && auth()->user()->isDecanat());
    $storeRoute = $horaire->exists
        ? ($isDecanat ? route('decanat.horaires.update', $horaire) : route('horaires.update', $horaire))
        : ($isDecanat ? route('decanat.horaires.store') : route('horaires.store'));
    $backRoute = $isDecanat ? route('decanat.horaires.index') : route('horaires.index');
@endphp

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">{{ $horaire->exists ? 'Modifier' : 'Programmer' }} un horaire</h1>
    @include('partials.form-errors')

    <form method="POST" action="{{ $storeRoute }}">
        @csrf
        @if($horaire->exists) @method('PUT') @endif

        <h5 class="border-bottom pb-2 mb-3">Hiérarchie académique</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Année académique</label>
                <select id="annee_academique_id" name="annee_academique_id" class="form-select">
                    <option value="">Choisir…</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" @selected((string)old('annee_academique_id', $horaire->annee_academique_id)===(string)$annee->id)>{{ $annee->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Semestre</label>
                <select id="semestre_id" name="semestre_id" class="form-select">
                    <option value="">Choisir…</option>
                    @foreach($semestres as $semestre)
                        <option value="{{ $semestre->id }}" data-annee="{{ $semestre->annee_academique_id }}" @selected((string)old('semestre_id', $horaire->semestre_id)===(string)$semestre->id)>{{ $semestre->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Domaine</label>
                <select id="domaine_id" name="domaine_id" class="form-select">
                    <option value="">Choisir…</option>
                    @foreach($domaines as $domaine)
                        <option value="{{ $domaine->id }}" @selected((string)old('domaine_id', $horaire->domaine_id)===(string)$domaine->id)>{{ $domaine->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filière</label>
                <select id="filiere_id" name="filiere_id" class="form-select">
                    <option value="">Choisir…</option>
                    @foreach($filieres as $filiere)
                        <option value="{{ $filiere->id }}" data-parent="{{ $filiere->domaine_id }}" @selected((string)old('filiere_id', $horaire->filiere_id)===(string)$filiere->id)>{{ $filiere->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mention</label>
                <select id="mention_id" name="mention_id" class="form-select">
                    <option value="">Choisir…</option>
                    @foreach($mentions as $mention)
                        <option value="{{ $mention->id }}" data-parent="{{ $mention->filiere_id }}" @selected((string)old('mention_id', $horaire->mention_id)===(string)$mention->id)>{{ $mention->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Promotion <span class="text-danger">*</span></label>
                <select id="promotion_id" name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach($promotions as $promotion)
                        <option value="{{ $promotion->id }}" data-parent="{{ $promotion->mention_id }}" data-effectif="{{ $promotion->effectif }}" @selected((string)old('promotion_id', $horaire->promotion_id)===(string)$promotion->id)>{{ $promotion->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">UE</label>
                <select id="ue_id" name="ue_id" class="form-select">
                    <option value="">Choisir…</option>
                    @foreach($ues as $ue)
                        <option value="{{ $ue->id }}" data-parent="{{ $ue->promotion_id }}" @selected((string)old('ue_id', $horaire->ue_id)===(string)$ue->id)>{{ $ue->code }} — {{ $ue->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">EC <span class="text-danger">*</span></label>
                <select id="ec_id" name="ec_id" class="form-select @error('ec_id') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    @foreach($ecs as $ec)
                        <option value="{{ $ec->id }}" data-parent="{{ $ec->ue_id }}" data-enseignant="{{ $ec->enseignant_id }}" @selected((string)old('ec_id', $horaire->ec_id)===(string)$ec->id)>{{ $ec->code }} — {{ $ec->nom }}</option>
                    @endforeach
                </select>
                @error('ec_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Enseignant <span class="text-danger">*</span></label>
                <select id="enseignant_id" name="enseignant_id" class="form-select" required>
                    <option value="">Choisir…</option>
                    @foreach($enseignants as $enseignant)
                        <option value="{{ $enseignant->id }}" @selected((string)old('enseignant_id', $horaire->enseignant_id)===(string)$enseignant->id)>{{ $enseignant->nom_complet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Auditoire <span class="text-muted">(optionnel)</span></label>
                <select name="auditoire_id" class="form-select">
                    <option value="">Demander une salle après enregistrement</option>
                    @foreach($auditoires as $id => $label)
                        <option value="{{ $id }}" @selected(old('auditoire_id', $horaire->hasSalle() ? $horaire->auditoire_id : null)==$id)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <h5 class="border-bottom pb-2 mb-3 mt-4">Créneau</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Jour</label>
                <select id="jour" name="jour" class="form-select">
                    <option value="">—</option>
                    @foreach($jours as $jour)
                        <option value="{{ $jour }}" @selected(old('jour', $horaire->jour)===$jour)>{{ $jour }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input id="date" type="date" name="date" value="{{ old('date', $horaire->date?->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Début</label>
                <input type="time" name="heure_debut" value="{{ old('heure_debut', $horaire->heure_debut ? substr($horaire->heure_debut,0,5) : null) }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fin</label>
                <input type="time" name="heure_fin" value="{{ old('heure_fin', $horaire->heure_fin ? substr($horaire->heure_fin,0,5) : null) }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Effectif</label>
                <input id="effectif_attendu" type="number" min="1" name="effectif_attendu" value="{{ old('effectif_attendu', $horaire->effectif_attendu) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select" required>
                    @foreach(\App\Models\Horaire::STATUTS as $value => $label)
                        <option value="{{ $value }}" @selected(old('statut', $horaire->statut ?? 'valide')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="alert alert-info mt-4 mb-0">
            <i class="bi bi-shield-check"></i> Avant l'enregistrement, le système vérifie la disponibilité de l'enseignant, les conflits d'enseignant, de promotion et d'EC, ainsi que la validité des heures.
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    const dateInput = document.getElementById('date');
    const jourInput = document.getElementById('jour');
    dateInput?.addEventListener('change', function () {
        if (!this.value) return;
        const d = new Date(this.value + 'T00:00:00');
        jourInput.value = jours[d.getDay()] || '';
    });

    function filterByParent(child, parentValue, attr = 'data-parent') {
        if (!child) return;
        Array.from(child.options).forEach((opt, i) => {
            if (i === 0) return;
            const parent = opt.getAttribute(attr);
            opt.hidden = parentValue && parent && String(parent) !== String(parentValue);
        });
        if (child.selectedOptions[0] && child.selectedOptions[0].hidden) child.value = '';
    }

    const domaine = document.getElementById('domaine_id');
    const filiere = document.getElementById('filiere_id');
    const mention = document.getElementById('mention_id');
    const promotion = document.getElementById('promotion_id');
    const ue = document.getElementById('ue_id');
    const ec = document.getElementById('ec_id');
    const enseignant = document.getElementById('enseignant_id');
    const effectif = document.getElementById('effectif_attendu');

    domaine?.addEventListener('change', () => { filterByParent(filiere, domaine.value); filiere.dispatchEvent(new Event('change')); });
    filiere?.addEventListener('change', () => { filterByParent(mention, filiere.value); mention.dispatchEvent(new Event('change')); });
    mention?.addEventListener('change', () => { filterByParent(promotion, mention.value); promotion.dispatchEvent(new Event('change')); });
    promotion?.addEventListener('change', () => {
        filterByParent(ue, promotion.value);
        const selected = promotion.selectedOptions[0];
        if (selected && selected.dataset.effectif && !effectif.value) effectif.value = selected.dataset.effectif;
        ue.dispatchEvent(new Event('change'));
    });
    ue?.addEventListener('change', () => { filterByParent(ec, ue.value); ec.dispatchEvent(new Event('change')); });
    ec?.addEventListener('change', () => {
        const selected = ec.selectedOptions[0];
        if (selected && selected.dataset.enseignant && enseignant && !enseignant.value) {
            enseignant.value = selected.dataset.enseignant;
        }
    });
});
</script>
@endpush
