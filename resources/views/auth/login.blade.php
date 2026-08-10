<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <title>Connexion | HoraCampus</title>
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #111827, #1d4ed8); font-family: Inter, system-ui, sans-serif; }
        .auth-shell { min-height: 100vh; display: grid; place-items: center; padding: 2rem 1rem; }
        .auth-card { max-width: 1050px; width: 100%; border: 0; border-radius: .75rem; overflow: hidden; box-shadow: 0 35px 80px rgba(2,10,26,.28); }
        .auth-side { background: #0f172a; color: #fff; padding: 3rem; }
        .auth-form { background: #fff; padding: 2.5rem; }
        .seal { align-items: center; border: 1px solid rgba(255,255,255,.25); border-radius: .5rem; display: inline-flex; font-size: 2rem; height: 76px; justify-content: center; margin-bottom: 1.5rem; width: 76px; }
        .login-tabs .nav-link { border-radius: .6rem; font-weight: 600; }
        .login-tabs .nav-link.active { background: #0f172a; color:#fff; }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card row g-0">
        <div class="col-lg-5 auth-side d-flex flex-column justify-content-center">
            <div class="seal"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>HoraCampus</h1>
            <p class="text-white-50">Portail académique pour planifier les cours, les salles et les emplois du temps avec contrôle automatique des conflits.</p>
            <div class="mt-4 p-3 rounded" style="background: rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12)">
                <div class="small text-white-50 mb-1">Accès par rôle</div>
                <div class="small"><i class="bi bi-shield-lock"></i> <strong>Personnel</strong> : Email + Mot de passe (Admin, Décanat, Enseignant)</div>
                <div class="small mt-1"><i class="bi bi-person-badge"></i> <strong>Étudiant</strong> : Nom + Matricule (fournis par le Décanat)</div>
                <div class="small text-white-50 mt-2">Les comptes étudiants/enseignants sont créés exclusivement par le Décanat de votre faculté.</div>
            </div>
        </div>
        <div class="col-lg-7 auth-form">
            <h2 class="h3 mb-1">Connexion</h2>
            <p class="text-muted mb-3">Choisissez votre mode de connexion.</p>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <ul class="nav nav-pills login-tabs mb-4 gap-2" role="tablist">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="pill" data-bs-target="#tab-personnel" type="button"><i class="bi bi-briefcase"></i> Personnel</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#tab-etudiant" type="button"><i class="bi bi-mortarboard"></i> Étudiant</button></li>
            </ul>

            <div class="tab-content">
                <!-- Personnel : Admin / Décanat / Enseignant -->
                <div class="tab-pane fade show active" id="tab-personnel">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Adresse e-mail</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="form-control form-control-lg" placeholder="ex: decanat@fst.cd" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input name="password" type="password" class="form-control form-control-lg" placeholder="Votre mot de passe" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember-personnel">
                            <label class="form-check-label" for="remember-personnel">Se souvenir de moi</label>
                        </div>
                        <button class="btn btn-primary btn-lg w-100"><i class="bi bi-box-arrow-in-right"></i> Se connecter</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}" class="small text-muted">Mot de passe oublié ?</a>
                    </div>
                </div>

                <!-- Étudiant : Nom + Matricule -->
                <div class="tab-pane fade" id="tab-etudiant">
                    <div class="alert alert-info small"><i class="bi bi-info-circle"></i> Vos identifiants vous sont communiqués par le Décanat de votre faculté. Exemple : Nom = <strong>MENOR</strong>, Matricule = <strong>24XYZ123</strong></div>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nom (en majuscules)</label>
                            <input name="nom" type="text" value="{{ old('nom') }}" class="form-control form-control-lg" placeholder="Ex: MENOR" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matricule</label>
                            <input name="matricule" type="text" value="{{ old('matricule') }}" class="form-control form-control-lg" placeholder="Ex: 24XYZ123" required>
                        </div>
                        <div class="mb-2 small text-muted">Si un mot de passe vous a été fourni, saisissez-le ci-dessous (sinon laissez vide, le matricule fait office de mot de passe initial).</div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe <span class="text-muted small">(optionnel)</span></label>
                            <input name="password" type="password" class="form-control" placeholder="Laissez vide si non communiqué">
                        </div>
                        <button class="btn btn-primary btn-lg w-100"><i class="bi bi-mortarboard"></i> Connexion étudiant</button>
                    </form>
                </div>
            </div>

            <div class="mt-4 text-center small text-muted">
                Aucune inscription publique. Les étudiants et enseignants sont enregistrés par le Décanat.<br>
                <a href="{{ route('password.request') }}">Mot de passe oublié</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
