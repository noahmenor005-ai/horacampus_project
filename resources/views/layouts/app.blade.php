<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HoraCampus | @yield('title', 'Plateforme universitaire')</title>
    <link rel="icon" href="/images/logo.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/horacampus.css" rel="stylesheet">
</head>
<body>
<div class="loader" id="pageLoader">
    <div class="spinner-border text-light" role="status" aria-label="Chargement"></div>
</div>

@php
    $user = auth()->user();
    $unread = $unreadNotifications ?? 0;
@endphp

<aside class="app-sidebar" id="appSidebar">
    <a class="brand" href="{{ route('dashboard') }}">
        <img src="/images/logo.svg" alt="HoraCampus">
        <span>
            <span class="brand-name">HORACAMPUS</span>
            <span class="brand-sub">Horaires & auditoires</span>
        </span>
    </a>

    @if($user && $user->isDecanat())
        <a class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('decanat.dashboard') ? 'active' : '' }}" href="{{ route('decanat.dashboard') }}"><i class="bi bi-grid-1x2"></i>Dashboard</a>
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
        <a class="sidebar-link {{ request()->routeIs('decanat.disponibilites.*') || request()->routeIs('disponibilites.*') ? 'active' : '' }}" href="{{ route('decanat.disponibilites.index') }}"><i class="bi bi-clock-history"></i>Disponibilités</a>
        <p class="nav-label">Étudiants</p>
        <a class="sidebar-link {{ request()->routeIs('decanat.etudiants.*') || request()->routeIs('etudiants.*') ? 'active' : '' }}" href="{{ route('decanat.etudiants.index') }}"><i class="bi bi-people"></i>Étudiants</a>
        <p class="nav-label">Planification</p>
        <a class="sidebar-link {{ request()->routeIs('decanat.horaires.*') || request()->routeIs('horaires.*') ? 'active' : '' }}" href="{{ route('decanat.horaires.index') }}"><i class="bi bi-calendar-week"></i>Horaires</a>
        <a class="sidebar-link {{ request()->routeIs('decanat.demandes-salles.*') || request()->routeIs('demandes.*') ? 'active' : '' }}" href="{{ route('decanat.demandes-salles.index') }}"><i class="bi bi-door-open"></i>Demandes de salles</a>
        <p class="nav-label">Profil</p>
        <a class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="sidebar-link danger w-100 border-0 bg-transparent text-start"><i class="bi bi-box-arrow-right"></i>Déconnexion</button>
        </form>
    @else
        <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i>Dashboard</a>
        <a class="sidebar-link {{ request()->routeIs('horaires.*') ? 'active' : '' }}" href="{{ route('horaires.index') }}"><i class="bi bi-calendar3"></i>Horaires</a>
    @endif

    @if($user && $user->isEnseignant())
        <p class="nav-label">Espace enseignant</p>
        <a class="sidebar-link {{ request()->routeIs('disponibilites.*') ? 'active' : '' }}" href="{{ route('disponibilites.index') }}"><i class="bi bi-clock-history"></i>Mes disponibilités</a>
        <a class="sidebar-link" href="{{ route('horaires.index') }}"><i class="bi bi-calendar-week"></i>Mon emploi du temps</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#cours"><i class="bi bi-easel"></i>Mes cours</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#salles"><i class="bi bi-door-open"></i>Mes salles</a>
        <a class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i>Mon profil</a>
    @endif

    @if($user && $user->isEtudiant())
        <p class="nav-label">Espace étudiant</p>
        <a class="sidebar-link" href="{{ route('horaires.index') }}"><i class="bi bi-calendar-week"></i>Mon emploi du temps</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#cours"><i class="bi bi-journal-bookmark"></i>Mes cours</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#enseignants"><i class="bi bi-person-workspace"></i>Mes enseignants</a>
        <a class="sidebar-link" href="{{ route('dashboard') }}#salles"><i class="bi bi-door-open"></i>Salles attribuées</a>
        <a class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i>Mon profil</a>
    @endif

    @if($user && $user->isAdmin())
        <p class="nav-label">Administration</p>
        <a class="sidebar-link {{ request()->routeIs('decanats.*') ? 'active' : '' }}" href="{{ route('decanats.index') }}"><i class="bi bi-briefcase"></i>Décanats</a>
        <a class="sidebar-link {{ request()->routeIs('facultes.*') ? 'active' : '' }}" href="{{ route('facultes.index') }}"><i class="bi bi-building"></i>Facultés</a>
        <a class="sidebar-link {{ request()->routeIs('batiments.*') ? 'active' : '' }}" href="{{ route('batiments.index') }}"><i class="bi bi-buildings"></i>Bâtiments</a>
        <a class="sidebar-link {{ request()->routeIs('auditoires.*') ? 'active' : '' }}" href="{{ route('auditoires.index') }}"><i class="bi bi-door-open"></i>Auditoires</a>
        <a class="sidebar-link {{ request()->routeIs('demandes.*') ? 'active' : '' }}" href="{{ route('demandes.index') }}"><i class="bi bi-inbox"></i>Demandes de salles</a>
        <a class="sidebar-link {{ request()->routeIs('attributions.*') ? 'active' : '' }}" href="{{ route('attributions.index') }}"><i class="bi bi-check2-square"></i>Attributions</a>
        <a class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-people"></i>Utilisateurs</a>
        <a class="sidebar-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}" href="{{ route('rapports.index') }}"><i class="bi bi-bar-chart"></i>Rapports</a>
        <a class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.edit') }}"><i class="bi bi-gear"></i>Paramètres</a>
    @endif
</aside>

<main class="app-main">
    <nav class="topbar px-3 py-3 d-flex justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm offcanvas-nav" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <div class="fw-semibold">@yield('title', 'HoraCampus')</div>
                <small class="text-muted">
                    @if($user && $user->isDecanat())
                        Décanat — {{ optional($user->faculte)->nom }}
                    @elseif($user && $user->isEtudiant())
                        Étudiant — {{ $user->matricule }} — {{ optional($user->promotion)->nom }}
                    @elseif($user && $user->isEnseignant())
                        Enseignant — {{ optional($user->faculte)->nom }}
                    @else
                        Supervision générale de la plateforme
                    @endif
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm position-relative" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    @if($unread > 0)<span class="notif-dot"></span>@endif
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" style="min-width:320px">
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <strong>Notifications</strong>
                        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-link btn-sm p-0">Tout lire</button></form>
                    </div>
                    @forelse(($latestNotifications ?? []) as $n)
                        <div class="px-3 py-2 border-bottom {{ $n->lu_at ? '' : 'bg-light' }}">
                            <div class="fw-semibold small">{{ $n->titre }}</div>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($n->message, 90) }}</div>
                        </div>
                    @empty
                        <div class="px-3 py-3 text-muted small">Aucune notification.</div>
                    @endforelse
                    <a class="dropdown-item text-center small py-2" href="{{ route('notifications.index') }}">Voir toutes</a>
                </div>
            </div>
            <button class="btn btn-outline-secondary btn-sm" type="button" id="themeToggle" title="Mode sombre"><i class="bi bi-moon-stars"></i></button>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i> {{ $user->prenom ?? 'Profil' }}</a>
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

<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="mobileNav">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">HoraCampus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="mobileNavBody"></div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055">
    @if(session('success'))
        <div class="toast show text-bg-success border-0" role="alert">
            <div class="d-flex"><div class="toast-body">{{ session('success') }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
        </div>
    @endif
    @if(session('error'))
        <div class="toast show text-bg-danger border-0" role="alert">
            <div class="d-flex"><div class="toast-body">{{ session('error') }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
        </div>
    @endif
    @if($errors->any())
        <div class="toast show text-bg-danger border-0" role="alert">
            <div class="d-flex"><div class="toast-body">{{ $errors->first() }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
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
    const mobileBody = document.getElementById('mobileNavBody');
    const sidebar = document.getElementById('appSidebar');
    if (mobileBody && sidebar) mobileBody.innerHTML = sidebar.innerHTML;
</script>
@stack('scripts')
</body>
</html>
