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
        .auth-card { max-width: 980px; width: 100%; border: 0; border-radius: .75rem; overflow: hidden; box-shadow: 0 35px 80px rgba(2,10,26,.28); }
        .auth-side { background: #0f172a; color: #fff; padding: 3rem; }
        .auth-form { background: #fff; padding: 3rem; }
        .seal { align-items: center; border: 1px solid rgba(255,255,255,.25); border-radius: .5rem; display: inline-flex; font-size: 2rem; height: 76px; justify-content: center; margin-bottom: 1.5rem; width: 76px; }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card row g-0">
        <div class="col-lg-5 auth-side d-flex flex-column justify-content-center">
            <div class="seal"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>HoraCampus</h1>
            <p class="text-white-50">Portail académique pour planifier les cours, les salles et les emplois du temps avec contrôle automatique des conflits.</p>
        </div>
        <div class="col-lg-7 auth-form">
            <h2 class="h3 mb-2">Connexion</h2>
            <p class="text-muted mb-4">Accédez à votre espace universitaire.</p>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <form method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Adresse e-mail</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input name="password" type="password" class="form-control form-control-lg" required>
                </div>
                @error('email')<p class="text-danger small">{{ $message }}</p>@enderror
                <button class="btn btn-primary btn-lg w-100">Se connecter</button>
            </form>
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('register') }}">Créer un compte</a>
                <a href="{{ route('password.request') }}">Mot de passe oublié</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
