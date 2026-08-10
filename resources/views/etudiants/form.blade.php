@extends('layouts.app')

@section('title', $etudiant->exists ? 'Modifier l\'étudiant' : 'Enregistrer un étudiant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $etudiant->exists ? 'Modifier l\'étudiant' : 'Enregistrer un étudiant' }}</h1>
        <small class="text-muted">Décanat — {{ optional(auth()->user()->faculte)->nom }} — Les champs Faculté / Domaine / Filière / Mention / Promotion sont liés et filtrés automatiquement.</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('etudiants.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

@if(!$etudiant->exists)
    <div class="alert alert-primary"><i class="bi bi-shield-lock"></i> <strong>Sécurité :</strong> Le compte étudiant sera automatiquement créé. Le matricule est unique et servira d'identifiant de connexion (Nom + Matricule). Le mot de passe initial sera le matricule ; l'étudiant pourra le changer dans son profil.</div>
@endif

<div class="surface p-4">
    <form method="POST" action="{{ $etudiant->exists ? route('etudiants.update', $etudiant) : route('etudiants.store') }}">
        @csrf
        @if($etudiant->exists)
            @method('PUT')
        @endif

        <h5 class="mb-3 border-bottom pb-2">Identité</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
                <input id="nom" type="text" name="nom" value="{{ old('nom', $etudiant->nom) }}" class="form-control @error('nom') is-invalid @enderror" placeholder="Ex: MENOR" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="postnom">Postnom</label>
                <input id="postnom" type="text" name="postnom" value="{{ old('postnom', $etudiant->postnom) }}" class="form-control @error('postnom') is-invalid @enderror" placeholder="Postnom (facultatif)">
                @error('postnom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="prenom">Prénom <span class="text-danger">*</span></label>
                <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $etudiant->prenom) }}" class="form-control @error('prenom') is-invalid @enderror" placeholder="Ex: Noah" required>
                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="matricule">Matricule <span class="text-danger">*</span></label>
                <input id="matricule" type="text" name="matricule" value="{{ old('matricule', $etudiant->matricule) }}" class="form-control @error('matricule') is-invalid @enderror" placeholder="Ex: 24XYZ123" required>
                <div class="form-text">Unique. C'est l'identifiant principal de connexion.</div>
                @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="sexe">Sexe <span class="text-danger">*</span></label>
                <select id="sexe" name="sexe" class="form-select @error('sexe') is-invalid @enderror" required>
                    <option value="">Choisir…</option>
                    <option value="M" @selected(old('sexe', $etudiant->sexe)==='M')>Masculin (M)</option>
                    <option value="F" @selected(old('sexe', $etudiant->sexe)==='F')>Féminin (F)</option>
                    <option value="Autre" @selected(old('sexe', $etudiant->sexe)==='Autre')>Autre</option>
                </select>
                @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="telephone">Téléphone</label>
                <input id="telephone" type="text" name="telephone" value="{{ old('telephone', $etudiant->telephone) }}" class="form-control @error('telephone') is-invalid @enderror" placeholder="Ex: 0991234567">
                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Email <span class="text-muted small">(facultatif)</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $etudiant->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="etudiant@exemple.cd">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label" for="statut">Statut</label>
                <select id="statut" name="statut" class="form-select @error('statut') is-invalid @enderror">
                    <option value="actif" @selected(old('statut', $etudiant->statut_inscription ?? 'actif')==='actif')>Actif</option>
                    <option value="inactif" @selected(old('statut', $etudiant->statut_inscription ?? 'actif')==='inactif')>Inactif</option>
                </select>
                @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <h5 class="mt-4 mb-3 border-bottom pb-2">Parcours académique (faculté du Décanat : {{ optional(auth()->user()->faculte)->nom }})</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="faculte_id">Faculté</label>
                <select id="faculte_id" name="faculte_id" class="form-select @error('faculte_id') is-invalid @enderror" @if(auth()->user()->isDecanat()) readonly disabled @endif>
                    @foreach($facultes as $f)
                        <option value="{{ $f->id }}" @selected((string)old('faculte_id', $etudiant->faculte_id) === (string)$f->id || (auth()->user()->isDecanat() && (int)$f->id===(int)auth()->user()->faculte_id))>{{ $f->nom }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->isDecanat())
                    <input type="hidden" name="faculte_id" value="{{ auth()->user()->faculte_id }}">
                @endif
                <div class="form-text">Automatiquement filtrée selon le Décanat connecté.</div>
                @error('faculte_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="domaine_id">Domaine <span class="text-danger">*</span></label>
                <select id="domaine_id" name="domaine_id" class="form-select @error('domaine_id') is-invalid @enderror" required>
                    <option value="">Choisir un domaine…</option>
                    @foreach(($domaines ?? collect()) as $d)
                        <option value="{{ $d->id }}" @selected((string)old('domaine_id', $etudiant->domaine_id) === (string)$d->id)>{{ $d->nom }}</option>
                    @endforeach
                </select>
                @error('domaine_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filiere_id">Filière <span class="text-danger">*</span></label>
                <select id="filiere_id" name="filiere_id" class="form-select @error('filiere_id') is-invalid @enderror" required>
                    <option value="">Choisir une filière…</option>
                    @foreach(($filieres ?? collect()) as $filiere)
                        <option value="{{ $filiere->id }}" @selected((string)old('filiere_id', $etudiant->filiere_id) === (string)$filiere->id)>{{ $filiere->nom }}</option>
                    @endforeach
                </select>
                @error('filiere_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label" for="mention_id">Mention <span class="text-danger">*</span></label>
                <select id="mention_id" name="mention_id" class="form-select @error('mention_id') is-invalid @enderror" required>
                    <option value="">Choisir une mention…</option>
                    @foreach(($mentions ?? collect()) as $m)
                        <option value="{{ $m->id }}" @selected((string)old('mention_id', $etudiant->mention_id) === (string)$m->id)>{{ $m->nom }}</option>
                    @endforeach
                </select>
                @error('mention_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="promotion_id">Promotion <span class="text-danger">*</span></label>
                <select id="promotion_id" name="promotion_id" class="form-select @error('promotion_id') is-invalid @enderror" required>
                    <option value="">Choisir une promotion…</option>
                    @foreach(($promotions ?? collect()) as $p)
                        <option value="{{ $p->id }}" @selected((string)old('promotion_id', $etudiant->promotion_id) === (string)$p->id)>{{ $p->nom }}</option>
                    @endforeach
                </select>
                @error('promotion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="annee_academique_id">Année académique</label>
                <select id="annee_academique_id" name="annee_academique_id" class="form-select @error('annee_academique_id') is-invalid @enderror">
                    <option value="">Sélectionner…</option>
                    @foreach(($annees ?? collect()) as $a)
                        <option value="{{ $a->id }}" @selected((string)old('annee_academique_id', $etudiant->annee_academique_id) === (string)$a->id)>{{ $a->libelle }}</option>
                    @endforeach
                </select>
                @error('annee_academique_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> {{ $etudiant->exists ? 'Mettre à jour' : 'Enregistrer l\'étudiant' }}</button>
            <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faculteSel = document.getElementById('faculte_id');
    const domaineSel = document.getElementById('domaine_id');
    const filiereSel = document.getElementById('filiere_id');
    const mentionSel = document.getElementById('mention_id');
    const promotionSel = document.getElementById('promotion_id');

    function fetchJson(url) { return fetch(url, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()); }

    function clearSelect(sel, placeholder) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
    }

    function populate(sel, items, placeholder) {
        clearSelect(sel, placeholder);
        items.forEach(i => {
            const o = document.createElement('option');
            o.value = i.id;
            o.textContent = i.nom + (i.effectif !== undefined ? ' (' + i.effectif + ' étudiants)' : '');
            sel.appendChild(o);
        });
    }

    // Fac -> Domaines
    faculteSel?.addEventListener('change', function() {
        if (!this.value) { clearSelect(domaineSel,'Choisir un domaine…'); clearSelect(filiereSel,'Choisir une filière…'); clearSelect(mentionSel,'Choisir une mention…'); clearSelect(promotionSel,'Choisir une promotion…'); return; }
        fetchJson('/api/domaines/' + this.value).then(items => {
            populate(domaineSel, items, 'Choisir un domaine…');
            clearSelect(filiereSel,'Choisir une filière…');
            clearSelect(mentionSel,'Choisir une mention…');
            clearSelect(promotionSel,'Choisir une promotion…');
        });
    });
    domaineSel?.addEventListener('change', function() {
        if (!this.value) { clearSelect(filiereSel,'Choisir une filière…'); return; }
        fetchJson('/api/filieres/' + this.value).then(items => {
            populate(filiereSel, items, 'Choisir une filière…');
            clearSelect(mentionSel,'Choisir une mention…');
            clearSelect(promotionSel,'Choisir une promotion…');
        });
    });
    filiereSel?.addEventListener('change', function() {
        if (!this.value) { clearSelect(mentionSel,'Choisir une mention…'); return; }
        fetchJson('/api/mentions/' + this.value).then(items => {
            populate(mentionSel, items, 'Choisir une mention…');
            clearSelect(promotionSel,'Choisir une promotion…');
        });
    });
    mentionSel?.addEventListener('change', function() {
        if (!this.value) { clearSelect(promotionSel,'Choisir une promotion…'); return; }
        fetchJson('/api/promotions/' + this.value).then(items => {
            populate(promotionSel, items, 'Choisir une promotion…');
        });
    });
});
</script>
@endpush
