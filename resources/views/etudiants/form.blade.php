@extends('layouts.app')

@section('title', $etudiant->exists ? 'Modifier l\'étudiant' : 'Enregistrer un étudiant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">{{ $etudiant->exists ? 'Modifier l\'étudiant' : 'Enregistrer un étudiant' }}</h1>
        <small class="text-muted">Corps étudiant de la plateforme</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('etudiants.index') }}"><i class="bi bi-arrow-left"></i> Retour</a>
</div>

@php
    $lmdFields = [
        'domaine_id' => ['label' => 'Domaine', 'options' => $domaines ?? collect(), 'parent' => 'faculte_id'],
        'filiere_id' => ['label' => 'Filière', 'options' => $filieres ?? collect(), 'parent' => 'domaine_id'],
        'mention_id' => ['label' => 'Mention', 'options' => $mentions ?? collect(), 'parent' => 'filiere_id'],
        'promotion_id' => ['label' => 'Promotion', 'options' => $promotions ?? collect(), 'parent' => 'mention_id'],
    ];
    $lmdParentColumn = [
        'domaine_id' => 'faculte_id',
        'filiere_id' => 'domaine_id',
        'mention_id' => 'filiere_id',
        'promotion_id' => 'mention_id',
    ];
@endphp

@if(!$etudiant->exists)
    <div class="alert alert-info"><i class="bi bi-info-circle"></i> Le compte sera créé en attente de validation par l'administration.</div>
@endif

<div class="surface p-4">
    <form method="POST" action="{{ $etudiant->exists ? route('etudiants.update', $etudiant) : route('etudiants.store') }}">
        @csrf
        @if($etudiant->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nom">Nom</label>
                <input id="nom" type="text" name="nom" value="{{ old('nom', $etudiant->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="prenom">Prénom</label>
                <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $etudiant->prenom) }}" class="form-control @error('prenom') is-invalid @enderror" required>
                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $etudiant->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="telephone">Téléphone</label>
                <input id="telephone" type="text" name="telephone" value="{{ old('telephone', $etudiant->telephone) }}" class="form-control @error('telephone') is-invalid @enderror">
                @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="faculte_id">Faculté</label>
                <select id="faculte_id" name="faculte_id" class="form-select @error('faculte_id') is-invalid @enderror" required>
                    <option value="">Choisir une faculté…</option>
                    @foreach($facultes as $f)
                        <option value="{{ $f->id }}" @selected((string)old('faculte_id', $etudiant->faculte_id) === (string)$f->id)>{{ $f->nom }}</option>
                    @endforeach
                </select>
                @error('faculte_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($lmdFields as $field => $cfg)
                <div class="col-md-6">
                    <label class="form-label" for="{{ $field }}">{{ $cfg['label'] }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" class="form-select @error($field) is-invalid @enderror" required>
                        <option value="">Choisir {{ mb_strtolower($cfg['label']) }}…</option>
                        @foreach($cfg['options'] as $opt)
                            <option value="{{ $opt->id }}" data-parent="{{ $opt->{$lmdParentColumn[$field]} }}" @selected((string)old($field, $etudiant->$field) === (string)$opt->id)>{{ $opt->nom }}</option>
                        @endforeach
                    </select>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach

            @if(!$etudiant->exists)
                <div class="col-md-6">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirmation du mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                </div>
            @endif
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            <a href="{{ route('etudiants.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const chain = [
            { id: 'domaine_id', parent: 'faculte_id' },
            { id: 'filiere_id', parent: 'domaine_id' },
            { id: 'mention_id', parent: 'filiere_id' },
            { id: 'promotion_id', parent: 'mention_id' },
        ];
        const faculte = document.getElementById('faculte_id');
        const hasCascadeOptions = chain.some(c => {
            const sel = document.getElementById(c.id);
            return sel && Array.from(sel.options).some(o => o.dataset.parent);
        });
        if (!faculte || !hasCascadeOptions) return;

        function refresh() {
            chain.forEach(c => {
                const sel = document.getElementById(c.id);
                if (!sel) return;
                const parentSel = document.getElementById(c.parent);
                const parentValue = parentSel ? parentSel.value : '';
                let firstVisible = null;
                Array.from(sel.options).forEach(opt => {
                    const ok = !opt.dataset.parent || opt.dataset.parent === parentValue;
                    opt.hidden = !ok;
                    if (ok && firstVisible === null) firstVisible = opt;
                });
                const selectedVisible = Array.from(sel.options).some(o => o.selected && !o.hidden);
                if (!selectedVisible) sel.value = firstVisible ? firstVisible.value : '';
            });
        }

        chain.forEach(c => {
            const sel = document.getElementById(c.id);
            if (sel) sel.addEventListener('change', refresh);
        });
        faculte.addEventListener('change', refresh);
        refresh();
    })();
</script>
@endpush
