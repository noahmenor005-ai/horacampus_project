<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport de gestion — HoraCampus</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 14px; }
        h2 { font-size: 13px; margin: 18px 0 6px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .nb { text-align: right; }
    </style>
</head>
<body>
<h1>Rapport de gestion — HoraCampus</h1>
<div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>

<h2>Indicateurs généraux</h2>
<table>
    <thead><tr><th>Indicateur</th><th class="nb">Valeur</th></tr></thead>
    <tbody>
    <tr><td>Facultés</td><td class="nb">{{ number_format($stats['facultes']) }}</td></tr>
    <tr><td>Promotions</td><td class="nb">{{ number_format($stats['promotions']) }}</td></tr>
    <tr><td>Cours</td><td class="nb">{{ number_format($stats['cours']) }}</td></tr>
    <tr><td>Horaires programmés</td><td class="nb">{{ number_format($stats['horaires']) }}</td></tr>
    <tr><td>Conflits</td><td class="nb">{{ number_format($stats['conflits']) }}</td></tr>
    <tr><td>Enseignants</td><td class="nb">{{ number_format($stats['enseignants']) }}</td></tr>
    <tr><td>Étudiants</td><td class="nb">{{ number_format($stats['etudiants']) }}</td></tr>
    <tr><td>Étudiants actifs</td><td class="nb">{{ number_format($stats['etudiants_actifs']) }}</td></tr>
    <tr><td>Auditoires</td><td class="nb">{{ number_format($stats['auditoires']) }}</td></tr>
    <tr><td>Capacité totale</td><td class="nb">{{ number_format($stats['capacite_totale']) }}</td></tr>
    <tr><td>Salles occupées aujourd'hui</td><td class="nb">{{ number_format($stats['salles_occupees_aujourdhui']) }}</td></tr>
    <tr><td>Bâtiments</td><td class="nb">{{ number_format($stats['batiments']) }}</td></tr>
    <tr><td>Disponibilités validées</td><td class="nb">{{ number_format($stats['disponibilites']) }}</td></tr>
    </tbody>
</table>

<h2>Demandes d'auditoire</h2>
<table>
    <thead><tr><th>Statut</th><th class="nb">Nombre</th></tr></thead>
    <tbody>
    <tr><td>En attente</td><td class="nb">{{ number_format($stats['demandes']['en_attente'] ?? 0) }}</td></tr>
    <tr><td>Acceptées</td><td class="nb">{{ number_format($stats['demandes']['acceptee'] ?? 0) }}</td></tr>
    <tr><td>Refusées</td><td class="nb">{{ number_format($stats['demandes']['refusee'] ?? 0) }}</td></tr>
    </tbody>
</table>

<h2>Occupation hebdomadaire par bâtiment</h2>
<table>
    <thead><tr><th>Bâtiment</th><th class="nb">Auditoires</th><th class="nb">Utilisés</th><th class="nb">Taux</th></tr></thead>
    <tbody>
    @forelse($stats['occupation_par_batiment'] as $batiment => $occupation)
        @php $pct = $occupation['auditoires'] > 0 ? round($occupation['utilises'] / $occupation['auditoires'] * 100) : 0; @endphp
        <tr>
            <td>{{ $batiment }}</td>
            <td class="nb">{{ $occupation['auditoires'] }}</td>
            <td class="nb">{{ $occupation['utilises'] }}</td>
            <td class="nb">{{ $pct }}%</td>
        </tr>
    @empty
        <tr><td colspan="4">Aucun bâtiment.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
