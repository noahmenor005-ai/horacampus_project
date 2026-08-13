<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoraCampus — Gestion intelligente des horaires et des auditoires universitaires</title>
    <meta name="description" content="HoraCampus orchestre facultés, décanats, emplois du temps et attribution des auditoires dans une plateforme universitaire sécurisée.">
    <link rel="icon" href="/images/logo.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/horacampus.css" rel="stylesheet">
    <style>
        .lp-hero { --lp-hero: url('/images/hero-campus.jpg'); }
    </style>
</head>
<body class="lp">
<nav class="lp-nav navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="/images/logo.svg" width="40" height="40" alt="HoraCampus">
            <span>
                <strong class="d-block" style="font-family:Fraunces,serif;letter-spacing:.06em">HORACAMPUS</strong>
                <small class="d-none d-md-block" style="font-size:.68rem;color:#c9d6e3">Horaires & auditoires</small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lpNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="lpNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="#avantages">Avantages</a></li>
                <li class="nav-item"><a class="nav-link" href="#fonctionnement">Fonctionnement</a></li>
                <li class="nav-item"><a class="nav-link" href="#fonctionnalites">Fonctionnalités</a></li>
                <li class="nav-item"><a class="nav-link" href="#espaces">Espaces</a></li>
                <li class="nav-item"><a class="btn btn-gold px-4" href="{{ route('login') }}">Connexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="lp-hero">
    <div class="container">
        <div class="row align-items-end g-5">
            <div class="col-lg-7">
                <div class="lp-kicker"><i class="bi bi-mortarboard-fill"></i> Plateforme universitaire de production</div>
                <h1 class="lp-title display-font mt-4">HORACAMPUS</h1>
                <p class="lead lp-subtitle mt-3">Gestion intelligente des horaires et des auditoires universitaires.</p>
                <p class="lp-subtitle">Pilotez l’organisation LMD, les disponibilités des enseignants, les demandes de salles et l’attribution des auditoires depuis une seule plateforme sécurisée.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('login') }}" class="btn btn-gold btn-lg px-4">Se connecter</a>
                    <a href="#fonctionnement" class="btn btn-outline-light btn-lg px-4">Voir le fonctionnement</a>
                </div>
                <p class="small text-white-50 mt-3 mb-0">Aucune inscription publique. Les comptes étudiants et enseignants sont créés exclusivement par le Décanat.</p>
            </div>
            <div class="col-lg-5">
                <div class="lp-card p-4">
                    <h2 class="h5 mb-3">Quatre espaces, une gouvernance</h2>
                    <div class="d-flex justify-content-between py-2 border-bottom border-secondary"><span>Administrateur</span><span class="text-warning">Supervision globale</span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom border-secondary"><span>Décanat</span><span class="text-warning">Cœur académique</span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom border-secondary"><span>Enseignant</span><span class="text-warning">Cours & disponibilités</span></div>
                    <div class="d-flex justify-content-between py-2"><span>Étudiant</span><span class="text-warning">Consultation seule</span></div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="lp-section" style="background:#0b1826">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-lg-3"><div class="display-6 fw-bold text-warning">4</div><div class="text-white-50">rôles sécurisés</div></div>
            <div class="col-6 col-lg-3"><div class="display-6 fw-bold text-warning">LMD</div><div class="text-white-50">faculté → EC</div></div>
            <div class="col-6 col-lg-3"><div class="display-6 fw-bold text-warning">6</div><div class="text-white-50">contrôles de conflit</div></div>
            <div class="col-6 col-lg-3"><div class="display-6 fw-bold text-warning">24/7</div><div class="text-white-50">consultation des horaires</div></div>
        </div>
    </div>
</section>

<section id="avantages" class="lp-section">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <img src="/images/feature-planning.jpg" class="img-fluid rounded-4 shadow" alt="Grille horaire HoraCampus">
            </div>
            <div class="col-lg-6">
                <div class="lp-kicker">Pourquoi HoraCampus</div>
                <h2 class="display-font display-6 mt-3">Une plateforme claire, pas un tableur universitaire.</h2>
                <ul class="list-unstyled mt-4">
                    <li class="d-flex gap-3 mb-3"><i class="bi bi-shield-check text-warning fs-4"></i><span>Permissions strictes côté serveur : chaque rôle ne voit que ce qui le concerne.</span></li>
                    <li class="d-flex gap-3 mb-3"><i class="bi bi-calendar2-week text-warning fs-4"></i><span>Grille hebdomadaire professionnelle et détection automatique des conflits.</span></li>
                    <li class="d-flex gap-3 mb-3"><i class="bi bi-building text-warning fs-4"></i><span>Demandes d’auditoires, suggestions de salles et attribution par l’administration.</span></li>
                    <li class="d-flex gap-3"><i class="bi bi-diagram-3 text-warning fs-4"></i><span>Organisation LMD complète : faculté, domaine, filière, mention, promotion, UE, EC.</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section id="fonctionnement" class="lp-section" style="background:#0b1826">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-kicker">Fonctionnement</div>
            <h2 class="display-font display-6 mt-3">Du Décanat à la consultation, sans inscription publique.</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['Administrateur', 'Crée les facultés, les comptes Décanat, les bâtiments et les auditoires.'],
                ['Décanats', 'Structurent le LMD, enregistrent étudiants et enseignants, construisent les horaires.'],
                ['Étudiants + Enseignants', 'Se connectent avec les identifiants fournis. Aucune auto-inscription.'],
                ['Horaires', 'Le Décanat programme les cours. Les conflits sont bloqués avant enregistrement.'],
                ['Demandes d’auditoires', 'Après l’horaire, le Décanat demande une salle à l’administration.'],
                ['Attribution des salles', 'L’administrateur accepte, refuse ou attribue une salle compatible.'],
                ['Consultation', 'Étudiants et enseignants consultent uniquement leur emploi du temps.'],
            ] as $i => $step)
                <div class="col-md-6 col-xl-3">
                    <div class="lp-card h-100 p-4">
                        <div class="flow-num mb-3">{{ $i + 1 }}</div>
                        <h3 class="h5">{{ $step[0] }}</h3>
                        <p class="text-white-50 mb-0">{{ $step[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="fonctionnalites" class="lp-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-kicker">Fonctionnalités principales</div>
            <h2 class="display-font display-6 mt-3">Tout le cycle académique, réellement opérationnel.</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['bi-diagram-3', 'Organisation LMD', 'Domaines, filières, mentions, promotions L1 à M2, années, semestres, UE et EC.'],
                ['bi-people', 'Ressources humaines', 'Création des enseignants et étudiants par le Décanat, matricule unique, comptes automatiques.'],
                ['bi-clock-history', 'Disponibilités', 'Créneaux enseignants, prévention des chevauchements, contrôle avant planification.'],
                ['bi-calendar-week', 'Grille horaire', 'Lundi à vendredi, 08h00–18h00, avec EC, enseignant, promotion et salle.'],
                ['bi-exclamation-octagon', 'Anti-conflits', 'Enseignant occupé, promotion occupée, EC déjà programmé, salle prise, horaire invalide.'],
                ['bi-door-open', 'Salles intelligentes', 'Suggestions limitées aux salles libres, assez grandes et compatibles.'],
            ] as $f)
                <div class="col-md-6 col-lg-4">
                    <div class="lp-card h-100 p-4">
                        <i class="bi {{ $f[0] }} fs-2 text-warning"></i>
                        <h3 class="h5 mt-3">{{ $f[1] }}</h3>
                        <p class="text-white-50 mb-0">{{ $f[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="espaces" class="lp-section" style="background:#0b1826">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-kicker">Espaces</div>
            <h2 class="display-font display-6 mt-3">Chaque rôle possède son tableau de bord.</h2>
        </div>
        <div class="row g-4">
            @foreach([
                ['Administrateur', 'images/space-admin.jpg', 'Supervision des facultés, décanats, bâtiments, demandes et attributions.'],
                ['Décanat', 'images/space-decanat.jpg', 'Cœur de la gestion académique : LMD, étudiants, enseignants, horaires.'],
                ['Enseignant', 'images/space-teacher.jpg', 'Emploi du temps, cours, salles et disponibilités. Aucune modification officielle.'],
                ['Étudiant', 'images/space-student.jpg', 'Profil, emploi du temps de sa promotion, cours, enseignants et salles.'],
            ] as $space)
                <div class="col-md-6 col-xl-3">
                    <article class="lp-card h-100">
                        <img src="/{{ $space[1] }}" class="w-100" style="height:180px;object-fit:cover" alt="{{ $space[0] }}">
                        <div class="p-4">
                            <h3 class="h5">{{ $space[0] }}</h3>
                            <p class="text-white-50 mb-0">{{ $space[2] }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('login') }}" class="btn btn-gold btn-lg px-5">Accéder à la connexion</a>
        </div>
    </div>
</section>

<footer class="py-5 border-top border-secondary">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <strong>HORACAMPUS</strong>
            <div class="small text-white-50">Gestion intelligente des horaires et des auditoires universitaires</div>
        </div>
        <div class="small text-white-50">© {{ date('Y') }} HoraCampus — Accès réservé aux comptes institutionnels.</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
