@php
    $incidents = collect($incidents ?? []);
    $formatDate = function ($date) {
        if (! $date) return '-';
        try {
            return $date instanceof \Carbon\CarbonInterface
                ? $date->format('d/m/Y H:i')
                : \Illuminate\Support\Carbon::parse($date)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $date;
        }
    };
@endphp
<table>
    <thead>
        <tr>
            <th colspan="8">Rapport incidents CEET</th>
        </tr>
        <tr>
            <th>Période</th>
            <th colspan="7">{{ $formatDate($start ?? null) }} - {{ $formatDate($end ?? null) }}</th>
        </tr>
        <tr>
            <th>Total</th>
            <th>Ouverts</th>
            <th>Clôturés</th>
            <th>Durée moyenne</th>
            <th colspan="4"></th>
        </tr>
        <tr>
            <td>{{ $total ?? 0 }}</td>
            <td>{{ $openCount ?? 0 }}</td>
            <td>{{ $closedCount ?? 0 }}</td>
            <td>{{ $avgDuration !== null ? round($avgDuration, 0).' min' : '-' }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <th>Code incident</th>
            <th>Titre</th>
            <th>Département</th>
            <th>Statut</th>
            <th>Priorité</th>
            <th>Date début</th>
            <th>Durée minutes</th>
            <th>Observations</th>
        </tr>
    </thead>
    <tbody>
        @forelse($incidents as $incident)
            <tr>
                <td>{{ $incident->code_incident ?? '-' }}</td>
                <td>{{ $incident->titre ?? '-' }}</td>
                <td>{{ $incident->departement?->nom ?? '-' }}</td>
                <td>{{ $incident->status?->libelle ?? '-' }}</td>
                <td>{{ $incident->priorite?->libelle ?? '-' }}</td>
                <td>{{ $formatDate($incident->date_debut ?? null) }}</td>
                <td>{{ $incident->duree_minutes ?? '' }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Aucun incident pour cette période.</td>
            </tr>
        @endforelse
    </tbody>
</table>
