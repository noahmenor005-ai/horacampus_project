<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoraCampus | @yield('title', 'Plateforme LMD')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --hc-bg: #f5f7fb;
            --hc-sidebar: #111827;
            --hc-sidebar-soft: #1f2937;
            --hc-text: #1f2937;
            --hc-muted: #6b7280;
            --hc-card: #ffffff;
            --hc-border: #e5e7eb;
            --hc-primary: #2563eb;
            --hc-success: #059669;
        }
        [data-bs-theme="dark"] {
            --hc-bg: #0f172a;
            --hc-sidebar: #020617;
            --hc-sidebar-soft: #111827;
            --hc-text: #e5e7eb;
            --hc-muted: #94a3b8;
            --hc-card: #111827;
            --hc-border: #243244;
        }
        body {
            background: var(--hc-bg);
            color: var(--hc-text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .app-sidebar {
            background: var(--hc-sidebar);
            bottom: 0;
            color: #d1d5db;
            left: 0;
            overflow-y: auto;
            padding: 1rem;
            position: fixed;
            top: 0;
            width: 280px;
            z-index: 1030;
        }
        .app-main {
            margin-left: 280px;
            min-height: 100vh;
        }
        .brand {
            color: #fff;
            display: flex;
            font-size: 1.2rem;
            font-weight: 800;
            gap: .65rem;
            padding: .75rem;
            text-decoration: none;
        }
        .nav-label {
            color: #9ca3af;
            font-size: .72rem;
            font-weight: 700;
            margin: 1.2rem .75rem .45rem;
            text-transform: uppercase;
        }
        .sidebar-link {
            align-items: center;
            border-radius: .5rem;
            color: #d1d5db;
            display: flex;
            gap: .7rem;
            margin-bottom: .15rem;
            padding: .7rem .75rem;
            text-decoration: none;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: var(--hc-sidebar-soft);
            color: #fff;
            transform: translateX(2px);
        }
        .topbar {
            backdrop-filter: blur(14px);
            background: rgba(255,255,255,.82);
            border-bottom: 1px solid var(--hc-border);
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        [data-bs-theme="dark"] .topbar {
            background: rgba(15,23,42,.82);
        }
        .content-shell {
            padding: 1.5rem;
        }
        .card,
        .surface {
            background: var(--hc-card);
            border: 1px solid var(--hc-border);
            border-radius: .5rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
        }
        .stat-card {
            min-height: 126px;
            padding: 1rem;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .stat-card:hover {
            box-shadow: 0 18px 35px rgba(15, 23, 42, .1);
            transform: translateY(-2px);
        }
        .stat-icon {
            align-items: center;
            background: rgba(37, 99, 235, .12);
            border-radius: .5rem;
            color: var(--hc-primary);
            display: inline-flex;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .table thead th {
            color: var(--hc-muted);
            font-size: .78rem;
            text-transform: uppercase;
        }
        .loader {
            align-items: center;
            background: rgba(15,23,42,.28);
            display: none;
            inset: 0;
            justify-content: center;
            position: fixed;
            z-index: 2000;
        }
        .loader.active {
            display: flex;
        }
        @media (max-width: 991px) {
            .app-sidebar {
                position: static;
                width: 100%;
            }
            .app-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<div class="loader" id="pageLoader">
    <div class="spinner-border text-light" role="status" aria-label="Chargement"></div>
</div>

<aside class="app-sidebar">
    <a class="brand" href="{{ route('dashboard') }}"><i class="bi bi-calendar2-week"></i><span>HoraCampus</span></a>
    @unless(auth()->user() && auth()->user()->isDecanat())
        <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i>Tableau de bord</a>
    @endunless

    {{-- DECANAT --}}
    @if(auth()->user() && auth()->user()->isDecanat())
        <a class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('decanat.dashboard') ? 'active' : '' }}" href="{{ route('decanat.dashboard') }}"><i class="bi bi-grid-1x2"></i>Tableau de bord</a>
        <p class="nav-label">Organisation académique</p>
        <a class="sidebar-link {{ request()->routeIs('decanat.faculte.*') ? 'active' : '' }}" href="{{ route('decanat.faculte.show') }}"><i class="bi bi-building"></i>Faculté</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.domaines.*') ? 'active' : '' }}" href="{{ route('decanat.domaines.index') }}"><i class="bi bi-diagram-3"></i>Domaines</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.filieres.*') ? 'active' : '' }}" href="{{ route('decanat.filieres.index') }}"><i class="bi bi-share"></i>Filières</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.mentions.*') ? 'active' : '' }}" href="{{ route('decanat.mentions.index') }}"><i class="bi bi-bookmark"></i>Mentions</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.promotions.*') ? 'active' : '' }}" href="{{ route('decanat.promotions.index') }}"><i class="bi bi-layers"></i>Promotions</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.annees-academiques.*') ? 'active' : '' }}" href="{{ route('decanat.annees-academiques.index') }}"><i class="bi bi-calendar2-range"></i>Années académiques</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.semestres.*') ? 'active' : '' }}" href="{{ route('decanat.semestres.index') }}"><i class="bi bi-calendar2"></i>Semestres</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.ues.*') ? 'active' : '' }}" href="{{ route('decanat.ues.index') }}"><i class="bi bi-journal-text"></i>UE</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.ecs.*') ? 'active' : '' }}" href="{{ route('decanat.ecs.index') }}"><i class="bi bi-list-nested"></i>EC</a>
        <p class="nav-label">Ressources humaines</p>
        <a class="sidebar-link {{ request()->routeIs('decanat.enseignants.*') || request()->routeIs('enseignants.*') ? 'active' : '' }}" href="{{ route('decanat.enseignants.index') }}"><i class="bi bi-person-workspace"></i>Enseignants</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.disponibilites.*') || request()->routeIs('disponibilites.*') ? 'active' : '' }}" href="{{ route('decanat.disponibilites.index') }}"><i class="bi bi-clock-history"></i>Disponibilités des enseignants</a>
        <p class="nav-label">Étudiants</p>
        <a class="sidebar-link {{ request()->routeIs('decanat.etudiants.*') || request()->routeIs('etudiants.*') ? 'active' : '' }}" href="{{ route('decanat.etudiants.index') }}"><i class="bi bi-people"></i>Étudiants</a>
        <p class="nav-label">Planification</p>
        <a class="sidebar-link {{ request()->routeIs('decanat.horaires.*') || request()->routeIs('horaires.*') ? 'active' : '' }}" href="{{ route('decanat.horaires.index') }}"><i class="bi bi-calendar-week"></i>Horaires</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.demandes-salles.*') || request()->routeIs('demandes.*') ? 'active' : '' }}" href="{{ route('decanat.demandes-salles.index') }}"><i class="bi bi-door-open"></i>Demandes d'auditoires</a>
        <p class="nav-label">Profil</p>
        <a class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i>Mon profil</a>
        <a class="sidebar-link" href="{{ route('profile.edit') }}"><i class="bi bi-pencil-square"></i>Modifier mon profil</a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sidebar-link w-100 border-0 bg-transparent text-start"><i class="bi bi-box-arrow-right"></i>Déconnexion</button>
        </form>
    @else
        <a class="sidebar-link {{ request()->routeIs('horaires.*') ? 'active' : '' }}" href="{{ route('horaires.index') }}"><i class="bi bi-calendar3"></i>Horaires</a>
    @endif

    {{-- ENSEIGNANT --}}
    @if(auth()->user() && auth()->user()->isEnseignant())
        <p class="nav-label">Espace Enseignant</p>
        <a class="sidebar-link {{ request()->routeIs('disponibilites.*') ? 'active' : '' }}" href="{{ route('disponibilites.index') }}"><i class="bi bi-clock-history"></i>Mes disponibilités</a>
        <a class="sidebar-link {{ request()->routeIs('horaires.*') ? 'active' : '' }}" href="{{ route('horaires.index') }}"><i class="bi bi-calendar-week"></i>Mon emploi du temps</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#ecs"><i class="bi bi-journal"></i>Mes EC / UE</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#cours"><i class="bi bi-easel"></i>Mes cours</a>
    @endif

    {{-- ETUDIANT --}}
    @if(auth()->user() && auth()->user()->isEtudiant())
        <p class="nav-label">Espace Étudiant</p>
        <a class="sidebar-link" href="{{ route('horaires.index') }}"><i class="bi bi-calendar-week"></i>Mon emploi du temps</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#cours"><i class="bi bi-journal-bookmark"></i>Mes cours</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#enseignants"><i class="bi bi-person-workspace"></i>Mes enseignants</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#promotion"><i class="bi bi-layers"></i>Ma promotion</a>
        <a class="sidebar-link" href="{{ route('horaires.index') }}"><i class="bi bi-door-open"></i>Salles attribuées</a>
    @endif

    @if(auth()->user() && auth()->user()->isAdmin())
        <p class="nav-label">Infrastructure</p>
        <a class="sidebar-link {{ request()->routeIs('facultes.*') ? 'active' : '' }}" href="{{ route('facultes.index') }}"><i class="bi bi-building"></i>Facultés</a>
        <a class="sidebar-link {{ request()->routeIs('batiments.*') ? 'active' : '' }}" href="{{ route('batiments.index') }}"><i class="bi bi-building-fill-gear"></i>Bâtiments</a>
        <a class="sidebar-link {{ request()->routeIs('auditoires.*') ? 'active' : '' }}" href="{{ route('auditoires.index') }}"><i class="bi bi-door-open"></i>Auditoires</a>
        <p class="nav-label">Administration</p>
        <a class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-shield-lock"></i>Utilisateurs / Décanats</a>
        <a class="sidebar-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}" href="{{ route('rapports.index') }}"><i class="bi bi-bar-chart"></i>Rapports</a>
        <p class="nav-label">Supervision globale</p>
        <a class="sidebar-link" href="{{ route('horaires.index') }}"><i class="bi bi-calendar3"></i>Tous les horaires</a>
        <a class="sidebar-link" href="{{ route('demandes.index') }}"><i class="bi bi-send"></i>Toutes les demandes</a>
    @endif
</aside>

<main class="app-main">
    <nav class="topbar px-3 py-3 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">@yield('title', 'HoraCampus')</div>
            <small class="text-muted">
                @if(auth()->user() && auth()->user()->isDecanat())
                    Décanat — {{ optional(auth()->user()->faculte)->nom }}
                @elseif(auth()->user() && auth()->user()->isEtudiant())
                    Étudiant — {{ auth()->user()->matricule }} — {{ optional(auth()->user()->promotion)->nom }}
                @elseif(auth()->user() && auth()->user()->isEnseignant())
                    Enseignant — {{ optional(auth()->user()->faculte)->nom }}
                @else
                    Planification académique sécurisée
                @endif
            </small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" id="themeToggle" title="Mode sombre">
                <i class="bi bi-moon-stars"></i>
            </button>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i> Profil</a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn btn-primary btn-sm"><i class="bi bi-box-arrow-right"></i> Déconnexion</button>
            </form>
        </div>
    </nav>

    <div class="content-shell">
        @yield('content')
    </div>
</main>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055">
    @if(session('success'))
        <div class="toast show text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">{{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="toast show text-bg-danger border-0" role="alert">
            <div class="toast-body">{{ session('error') }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="toast show text-bg-danger border-0" role="alert">
            <div class="toast-body">{{ $errors->first() }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const html = document.documentElement;
    const savedTheme = localStorage.getItem('horacampus-theme');
    if (savedTheme) html.setAttribute('data-bs-theme', savedTheme);
    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('horacampus-theme', next);
    });
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => document.getElementById('pageLoader').classList.add('active'));
    });
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(t => {
            const bsToast = bootstrap.Toast.getOrCreateInstance(t, {delay: 6000});
            // auto hide after 6s
        });
    }, 500);
</script>
@stack('scripts')
</body>
</html>
