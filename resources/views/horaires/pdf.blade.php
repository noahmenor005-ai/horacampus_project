<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Planning des horaires — HoraCampus</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 14px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        td.cours { font-weight: bold; }
    </style>
</head>
<body>
<h1>Planning des horaires — HoraCampus</h1>
<div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>

@if (($orientation ?? '') === 'landscape')
<table>
    <thead>
    <tr><th>Jour</th><th>Date</th><th>Début</th><th>Fin</th><th>Cours</th><th>Salle</th><th>Enseignant</th><th>Promotion</th><th>Semestre</th></tr>
    </thead>
    <tbody>
    @forelse($horaires as $h)
        <tr>
            <td>{{ $h->jour }}</td>
            <td>{{ $h->date?->format('d/m/Y') }}</td>
            <td>{{ substr($h->heure_debut, 0, 5) }}</td>
            <td>{{ substr($h->heure_fin, 0, 5) }}</td>
            <td class="cours">{{ optional($h->cours)->intitule }}</td>
            <td>{{ optional($h->auditoire)->nom }}</td>
            <td>{{ optional($h->enseignant)->nom_complet }}</td>
            <td>{{ optional($h->promotion)->nom }}</td>
            <td>{{ optional($h->semestre)->libelle }}</td>
        </tr>
    @empty
        <tr><td colspan="9" style="text-align:center;color:#6b7280;">Aucun horaire à afficher.</td></tr>
    @endforelse
    </tbody>
</table>
@else
<table>
    <thead>
    <tr><th>Jour</th><th>Date</th><th>Heure</th><th>Cours</th><th>Salle</th><th>Enseignant</th></tr>
    </thead>
    <tbody>
    @forelse($horaires as $h)
        <tr>
            <td>{{ $h->jour }}</td>
            <td>{{ $h->date?->format('d/m/Y') }}</td>
            <td>{{ substr($h->heure_debut, 0, 5) }} - {{ substr($h->heure_fin, 0, 5) }}</td>
            <td class="cours">{{ optional($h->cours)->intitule }}</td>
            <td>{{ optional($h->auditoire)->nom }}</td>
            <td>{{ optional($h->enseignant)->nom_complet }}</td>
        </tr>
    @empty
        <tr><td colspan="6" style="text-align:center;color:#6b7280;">Aucun horaire à afficher.</td></tr>
    @endforelse
    </tbody>
</table>
@endif
</body>
</html>
