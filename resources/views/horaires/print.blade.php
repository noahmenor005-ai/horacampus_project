<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impression — Horaires HoraCampus</title>
    <style>
        * { box-sizing: border-box; }
        body { color: #111827; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 24px; }
        .no-print { margin-bottom: 16px; }
        .no-print button {
            background: #2563eb;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            padding: 8px 14px;
        }
        header { border-bottom: 3px solid #2563eb; margin-bottom: 16px; padding-bottom: 12px; }
        header h1 { font-size: 22px; margin: 0 0 4px; }
        header .meta { color: #6b7280; font-size: 13px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f3f4f6; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()">Imprimer</button>
</div>
<header>
    <h1>HoraCampus — Planning des horaires</h1>
    <div class="meta">Imprimé le {{ now()->format('d/m/Y à H:i') }}</div>
</header>
<table>
    <thead>
    <tr><th>Jour</th><th>Date</th><th>Heure</th><th>Cours</th><th>Salle</th><th>Enseignant</th><th>Promotion</th></tr>
    </thead>
    <tbody>
    @forelse($horaires as $h)
        <tr>
            <td>{{ $h->jour }}</td>
            <td>{{ $h->date?->format('d/m/Y') }}</td>
            <td>{{ substr($h->heure_debut, 0, 5) }} - {{ substr($h->heure_fin, 0, 5) }}</td>
            <td>{{ optional($h->cours)->intitule }}</td>
            <td>{{ optional($h->auditoire)->nom }}</td>
            <td>{{ optional($h->enseignant)->nom_complet }}</td>
            <td>{{ optional($h->promotion)->nom }}</td>
        </tr>
    @empty
        <tr><td colspan="7" style="text-align:center;color:#6b7280;">Aucun horaire à afficher.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
