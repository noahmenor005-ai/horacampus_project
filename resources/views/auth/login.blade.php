<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo.svg" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700&family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/horacampus.css" rel="stylesheet">
    <title>Connexion | HoraCampus</title>
    <style>
        body { min-height: 100vh; background: #07111c; }
        .auth-shell { min-height: 100vh; display: grid; place-items: center; padding: 2rem 1rem; }
        .auth-card { max-width: 1080px; width: 100%; border: 0; border-radius: 24px; overflow: hidden; box-shadow: 0 35px 80px rgba(2,10,26,.4); }
        .auth-side { background: linear-gradient(160deg, #082440, #0b3b6a 60%, #0e8a8a); color: #fff; padding: 3rem; }
        .auth-form { background: #fff; padding: 2.5rem; }
        .login-tabs .nav-link { border-radius: .7rem; font-weight: 700; color: #082440; }
        .login-tabs .nav-link.active { background: #082440; color:#fff; }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card row g-0">
        <div class="col-lg-5 auth-side d-flex flex-column justify-content-center">
            <a href="{{ route('home') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-4">
                <img src="{{ asset('images/logo.svg') }}" width="48" height="48" alt="HoraCampus">
                <span>
                    <strong style="font-family:Fraunces,serif;letter-spacing:.08em">HORACAMPUS</strong><br>
                    <small class="text-white-50">Retour à l’accueil</small>
                </span>
            </a>
            <h1 class="h2" style="font-family:Fraunces,serif">Portail institutionnel</h1>
            <p class="text-white-50">Gestion intelligente des horaires et des auditoires universitaires.</p>
            <div class="mt-3 p-3 rounded" style="background: rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12)">
                <div class="small text-white-50 mb-1">Accès par rôle</div>
                <div class="small"><i class="bi bi-shield-lock"></i> Personnel : email ou nom + mot de passe</div>
                <div class="small mt-1"><i class="bi bi-briefcase"></i> Décanat FSI : nom <strong>FSI</strong> / mot de passe <strong>098765</strong></div>
                <div class="small mt-1"><i class="bi bi-person-badge"></i> Étudiant : nom + matricule fournis par le Décanat</div>
            </div>
        </div>
        <div class="col-lg-7 auth-form">
            <h2 class="h3 mb-1">Connexion</h2>
            <p class="text-muted mb-3">Aucune inscription publique n’est proposée.</p>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <ul class="nav nav-pills login-tabs mb-4 gap-2" role="tablist">
                <li class="nav-item flex-fill"><button class="nav-link w-100 active" data-bs-toggle="pill" data-bs-target="#tab-personnel" type="button"><i class="bi bi-briefcase"></i> Personnel</button></li>
                <li class="nav-item flex-fill"><button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#tab-etudiant" type="button"><i class="bi bi-mortarboard"></i> Étudiant</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-personnel">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Identifiant (email ou nom)</label>
                            <input name="identifiant" type="text" value="{{ old('identifiant', old('email')) }}" class="form-control form-control-lg" placeholder="ex: FSI ou noahmenor005@gmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input name="password" type="password" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember-personnel">
                            <label class="form-check-label" for="remember-personnel">Se souvenir de moi</label>
                        </div>
                        <button class="btn btn-primary btn-lg w-100"><i class="bi bi-box-arrow-in-right"></i> Se connecter</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="tab-etudiant">
                    <div class="alert alert-info small"><i class="bi bi-info-circle"></i> Identifiants communiqués par le Décanat. Exemple : Nom <strong>MUKENDI</strong>, Matricule <strong>FSI2024001</strong></div>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input name="nom" type="text" value="{{ old('nom') }}" class="form-control form-control-lg" placeholder="Ex: MUKENDI" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Matricule</label>
                            <input name="matricule" type="text" value="{{ old('matricule') }}" class="form-control form-control-lg" placeholder="Ex: FSI2024001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe <span class="text-muted small">(optionnel)</span></label>
                            <input name="password" type="password" class="form-control" placeholder="Laisser vide si le matricule sert de mot de passe">
                        </div>
                        <button class="btn btn-primary btn-lg w-100"><i class="bi bi-mortarboard"></i> Connexion étudiant</button>
                    </form>
                </div>
            </div>
            <div class="mt-4 text-center small text-muted">
                <a href="{{ route('home') }}">Retour à l’accueil</a> · <a href="{{ route('password.request') }}">Mot de passe oublié</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
