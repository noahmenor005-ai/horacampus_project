<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Mot de passe oublié | HoraCampus</title>
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 520px">
    <div class="card border-0 shadow-sm p-4">
        <h1 class="h4">Réinitialisation</h1>
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="form-label">Adresse e-mail</label>
            <input name="email" type="email" class="form-control mb-3" required>
            @error('email')<p class="text-danger small">{{ $message }}</p>@enderror
            <button class="btn btn-primary w-100">Envoyer le lien</button>
        </form>
    </div>
</main>
</body>
</html>
