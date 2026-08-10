<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Inscription | HoraCampus</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: #eef4fb;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .register-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2.5rem 1rem;
        }

        .register-card {
            width: 100%;
            max-width: 680px;
            border: 0;
            border-radius: 1.4rem;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(15, 54, 94, .12);
            background: #fff;
        }

        .register-card .card-header {
            background: #0b3d71;
            color: #fff;
            padding: 2rem 2rem 1.75rem;
        }

        .register-card .card-header h1 {
            font-size: 1.8rem;
            margin-bottom: .5rem;
        }

        .register-card .card-body {
            padding: 2rem;
        }

        .form-control, .form-select {
            border-radius: .9rem;
            padding: .85rem .95rem;
        }

        .btn-primary {
            border-radius: 999px;
            padding: .95rem 1.1rem;
            background: #0b3d71;
            border-color: #0b3d71;
        }

        .role-pill {
            cursor: pointer;
            border: 2px solid #d5deeb;
            border-radius: 1rem;
            padding: .9rem 1rem;
            text-align: center;
            transition: border-color .15s ease, background .15s ease;
        }

        .role-pill.active {
            border-color: #0b3d71;
            background: #eef4fb;
        }

        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #62718b;
        }

        .register-footer a {
            color: #0b3d71;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="register-shell">
        <div class="card register-card shadow-sm">
            <div class="card-header text-center">
                <h1>Créer un compte</h1>
                <p class="mb-0 opacity-75">Rejoignez HoraCampus pour consulter vos horaires et ressources académiques.</p>
            </div>
            <div class="card-body">
                <form method="POST">
                    @csrf
                    <input type="hidden" name="role" id="role" value="{{ old('role', 'etudiant') }}">

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="role-pill" data-role="etudiant" id="pill-etudiant">
                                <i class="bi bi-person"></i> Étudiant
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="role-pill" data-role="enseignant" id="pill-enseignant">
                                <i class="bi bi-person-workspace"></i> Enseignant
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input name="nom" value="{{ old('nom') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom</label>
                            <input name="prenom" value="{{ old('prenom') }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Adresse e-mail</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input name="telephone" value="{{ old('telephone') }}" class="form-control">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mot de passe</label>
                            <input name="password" type="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmer le mot de passe</label>
                            <input name="password_confirmation" type="password" class="form-control" required>
                        </div>
                    </div>

                    <div id="academicFields">
                        <div class="mb-3">
                            <label class="form-label">Faculté</label>
                            <select name="faculte_id" id="faculte" class="form-select" required>
                                <option value="">Sélectionner</option>
                                @foreach($facultes as $f)
                                    <option value="{{ $f->id }}" @selected(old('faculte_id') == $f->id)>{{ $f->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="etudiantFields">
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Domaine</label>
                                    <select name="domaine_id" id="domaine" class="form-select">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Filière</label>
                                    <select name="filiere_id" id="filiere" class="form-select">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Mention</label>
                                    <select name="mention_id" id="mention" class="form-select">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Promotion</label>
                                    <select name="promotion_id" id="promotion" class="form-select">
                                        <option value="">Sélectionner</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100">Créer mon compte</button>
                </form>
                <p class="register-footer">Déjà membre ? <a href="{{ route('login') }}">Retour à la connexion</a></p>
            </div>
        </div>
    </div>

    <script>
        const roleInput = document.getElementById('role');
        const etudiantFields = document.getElementById('etudiantFields');

        function selectRole(role) {
            roleInput.value = role;
            document.querySelectorAll('.role-pill').forEach(p => p.classList.toggle('active', p.dataset.role === role));
            etudiantFields.style.display = role === 'etudiant' ? '' : 'none';
        }

        document.querySelectorAll('.role-pill').forEach(p => p.addEventListener('click', () => selectRole(p.dataset.role)));
        selectRole(roleInput.value);

        function populate(selectId, url) {
            fetch(url)
                .then(r => r.json())
                .then(items => {
                    const sel = document.getElementById(selectId);
                    sel.innerHTML = '<option value="">Sélectionner</option>';
                    items.forEach(i => {
                        const o = document.createElement('option');
                        o.value = i.id;
                        o.textContent = i.nom + (i.effectif !== undefined ? ' (' + i.effectif + ' étudiants)' : '');
                        sel.appendChild(o);
                    });
                    if (selectId === 'domaine') document.getElementById('filiere').innerHTML = '<option value="">Sélectionner</option>';
                    if (selectId === 'filiere') document.getElementById('mention').innerHTML = '<option value="">Sélectionner</option>';
                    if (selectId === 'mention') document.getElementById('promotion').innerHTML = '<option value="">Sélectionner</option>';
                });
        }

        document.getElementById('faculte').addEventListener('change', e => {
            if (e.target.value) populate('domaine', '{{ route('register.domaines', '') }}/' + e.target.value);
        });
        document.getElementById('domaine').addEventListener('change', e => {
            if (e.target.value) populate('filiere', '{{ route('register.filieres', '') }}/' + e.target.value);
        });
        document.getElementById('filiere').addEventListener('change', e => {
            if (e.target.value) populate('mention', '{{ route('register.mentions', '') }}/' + e.target.value);
        });
        document.getElementById('mention').addEventListener('change', e => {
            if (e.target.value) populate('promotion', '{{ route('register.promotions', '') }}/' + e.target.value);
        });

        @if(old('faculte_id'))
            populate('domaine', '{{ route('register.domaines', '') }}/' + '{{ old('faculte_id') }}');
        @endif
    </script>
</body>
</html>
