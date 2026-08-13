@php
    $jours = $grille['jours'] ?? ['Lundi','Mardi','Mercredi','Jeudi','Vendredi'];
    $hours = $grille['hours'] ?? [];
    $grid = $grille['grid'] ?? [];
@endphp
<div class="table-responsive">
    <table class="timetable mb-0">
        <thead>
        <tr>
            <th style="width:80px">Heure</th>
            @foreach($jours as $jour)<th>{{ $jour }}</th>@endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($hours as $hour)
            <tr>
                <th>{{ $hour }}</th>
                @foreach($jours as $jour)
                    <td>
                        @foreach($grid[$hour][$jour] ?? [] as $h)
                            @php
                                $intitule = optional($h->ec)->nom ?: optional(optional($h->cours)->ec)->nom ?: optional($h->cours)->intitule;
                                $salle = optional($h->auditoire)->nom;
                                if ($salle === 'EN-ATTENTE') $salle = 'Salle en attente';
                            @endphp
                            <div class="slot-card mb-1">
                                <strong>{{ $intitule }}</strong>
                                <small>{{ substr($h->heure_debut,0,5) }} – {{ substr($h->heure_fin,0,5) }}</small>
                                <small>{{ optional($h->enseignant)->nom_complet }}</small>
                                <small>{{ optional($h->promotion)->nom }}</small>
                                <span class="room">{{ $salle ?: '—' }}</span>
                            </div>
                        @endforeach
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
