<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { margin: 0 0 4px 0; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h1>Export incidents CEET</h1>
    <p class="meta">Genere le {{ $generatedAt->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
        <tr>
            <th>Code</th>
            <th>Titre</th>
            <th>Departement</th>
            <th>Type</th>
            <th>Cause</th>
            <th>Statut</th>
            <th>Priorite</th>
            <th>Operateur</th>
            <th>Responsable</th>
            <th>Date debut</th>
            <th>Date fin</th>
            <th>Duree (min)</th>
        </tr>
        </thead>
        <tbody>
        @forelse($incidents as $incident)
            <tr>
                <td>{{ $incident->code_incident }}</td>
                <td>{{ $incident->titre }}</td>
                <td>{{ $incident->departement?->nom }}</td>
                <td>{{ $incident->typeIncident?->libelle }}</td>
                <td>{{ $incident->cause?->libelle }}</td>
                <td>{{ $incident->status?->libelle }}</td>
                <td>{{ $incident->priorite?->libelle }}</td>
                <td>{{ $incident->operateur?->name }}</td>
                <td>{{ $incident->responsable?->name }}</td>
                <td>{{ $incident->date_debut?->format('d/m/Y H:i') }}</td>
                <td>{{ $incident->date_fin?->format('d/m/Y H:i') }}</td>
                <td>{{ $incident->duree_minutes }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12">Aucun incident trouve pour cette extraction.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
