<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoraCampus — Gestion universitaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #0c3862 0%, #146c94 100%);
            color: #fff;
        }

        .hero {
            padding: 4rem 0;
        }

        .hero-card {
            background: rgba(255, 255, 255, .96);
            border-radius: 1.4rem;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .14);
            color: #102a43;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(255,255,255,.12);
            padding: .65rem 1rem;
            border-radius: 999px;
            margin-top: 1.5rem;
            font-size: .92rem;
        }

        .hero-title {
            font-size: clamp(2.4rem, 4vw, 3.5rem);
            line-height: 1.03;
            letter-spacing: -0.04em;
        }

        .hero-subtitle {
            max-width: 40rem;
            color: rgba(255,255,255,.88);
        }

        .hero-btns .btn {
            min-width: 170px;
            border-radius: 999px;
        }

        .hero-aside {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 1.3rem;
            padding: 2rem;
            box-shadow: 0 30px 60px rgba(2,10,26,.18);
        }

        .hero-aside .feature-item {
            display: flex;
            align-items: center;
            gap: .9rem;
            margin-bottom: 1rem;
            color: #eef4fb;
        }

        .hero-aside .feature-item:last-child {
            margin-bottom: 0;
        }

        .hero-aside i {
            font-size: 1.25rem;
            color: #8be2ff;
        }

        @media (max-width: 991px) {
            .hero {
                padding: 3rem 0;
            }
        }
    </style>
</head>
<body>
    <main class="container hero">
        <div class="row align-items-center g-5">
            <section class="col-lg-7">
                <div class="feature-pill">
                    <i class="bi bi-mortarboard-fill"></i>
                    Plateforme académique — gestion des emplois du temps et des ressources
                </div>
                <h1 class="hero-title fw-bold mt-4">HoraCampus — un tableau de bord plus clair pour vos plannings.</h1>
                <p class="lead hero-subtitle mt-4">Centralisez les demandes d’auditoires, suivez les horaires et pilotez votre organisation LMD depuis une interface moderne et intuitive.</p>
                <div class="d-flex flex-wrap gap-3 mt-5 hero-btns">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg shadow-sm">Se connecter</a>
                </div>
            </section>

            <section class="col-lg-5">
                <div class="hero-aside">
                    <h3 class="h5 text-white fw-semibold mb-4">Ce que vous pouvez faire</h3>
                    <div class="feature-item"><i class="bi bi-clock-fill"></i><span>Visualiser les créneaux en temps réel</span></div>
                    <div class="feature-item"><i class="bi bi-building"></i><span>Suivre les demandes d’auditoires facilement</span></div>
                    <div class="feature-item"><i class="bi bi-people-fill"></i><span>Gérer les ressources pédagogiques et les promotions</span></div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
