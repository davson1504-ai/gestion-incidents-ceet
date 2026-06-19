@php
    use Illuminate\Support\Str;

    $incidents = collect($incidents ?? []);
    $byStatus = collect($byStatus ?? []);
    $byPriorite = collect($byPriorite ?? []);
    $byDepart = collect($byDepart ?? []);
    $byType = collect($byType ?? []);
    $byCause = collect($byCause ?? []);
    $topDepart = collect($topDepart ?? []);

    $periodLabel = match ($granularity ?? null) {
        'day' => 'Rapport journalier',
        'month' => 'Rapport mensuel',
        default => 'Rapport incidents',
    };

    $formatDate = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return $date instanceof \Carbon\CarbonInterface
                ? $date->format('d/m/Y H:i')
                : \Illuminate\Support\Carbon::parse($date)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $date;
        }
    };

    $ceetPdfLogoPaths = [
        public_path('images/logo-ceet.png'),
        public_path('img/logo-ceet.png'),
        public_path('logo-ceet.png'),
    ];

    $ceetPdfLogoData = null;

    foreach ($ceetPdfLogoPaths as $ceetPdfLogoPath) {
        if (is_file($ceetPdfLogoPath)) {
            $ceetPdfLogoData = 'data:image/png;base64,'.base64_encode(file_get_contents($ceetPdfLogoPath));
            break;
        }
    }
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $periodLabel }} - CEET</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1b1b1d; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 2px solid #141b2b; padding-bottom: 12px; margin-bottom: 18px; }
        .kicker { color: #76777d; font-size: 9px; text-transform: uppercase; letter-spacing: .08em; }
        h1 { font-size: 22px; color: #141b2b; margin-top: 4px; }
        .subtitle { margin-top: 6px; color: #45464c; }
        .meta { margin-top: 8px; color: #45464c; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { width: 25%; border: 1px solid #e5e2e3; padding: 10px; vertical-align: top; }
        .metric-label { color: #76777d; text-transform: uppercase; font-size: 8px; }
        .metric-value { font-size: 18px; font-weight: bold; margin-top: 4px; color: #141b2b; }
        .section-title { font-size: 13px; color: #141b2b; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f0edee; color: #45464c; font-size: 8px; text-transform: uppercase; text-align: left; padding: 7px; border: 1px solid #e5e2e3; }
        td { padding: 7px; border: 1px solid #e5e2e3; vertical-align: top; }
        .muted { color: #76777d; }
        .badge { display: inline-block; border: 1px solid #c6c6cd; border-radius: 12px; padding: 3px 7px; font-size: 8px; font-weight: bold; }
        .footer { margin-top: 18px; padding-top: 8px; border-top: 1px solid #e5e2e3; color: #76777d; font-size: 9px; }
    
        /* CEET PDF logo header */
        .ceet-pdf-header-table { width: 100%; border-collapse: collapse; margin: 0; }
        .ceet-pdf-header-table td { border: 0 !important; padding: 0 !important; vertical-align: middle; }
        .ceet-pdf-logo-cell { width: 68px; }
        .ceet-pdf-title-cell { padding-left: 14px !important; }
        .ceet-pdf-logo { width: 56px; height: 56px; }
        .ceet-pdf-logo-fallback {
            width: 54px;
            height: 54px;
            border: 1px solid #d8dce2;
            border-radius: 10px;
            text-align: center;
            line-height: 54px;
            font-size: 11px;
            font-weight: bold;
            color: #141b2b;
        }
    </style>
</head>
<body>
    <header class="header">
        <table class="ceet-pdf-header-table">
            <tr>
                <td class="ceet-pdf-logo-cell">
                @if($ceetPdfLogoData)
                    <img src="{{ $ceetPdfLogoData }}" class="ceet-pdf-logo" alt="CEET">
                @else
                    <div class="ceet-pdf-logo-fallback">CEET</div>
                @endif
            </td>
                <td class="ceet-pdf-title-cell">
                    <p class="kicker">CEET - Gestion des incidents</p>
                    <h1>{{ $periodLabel }}</h1>
                    <p class="subtitle">Période du {{ $formatDate($start ?? null) }} au {{ $formatDate($end ?? null) }}</p>
                    <p class="meta">Document généré le {{ now()->format('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
    </header>

    <table class="grid">
        <tr>
            <td><div class="metric-label">Total incidents</div><div class="metric-value">{{ number_format((int) ($total ?? 0), 0, ',', ' ') }}</div></td>
            <td><div class="metric-label">Ouverts</div><div class="metric-value">{{ number_format((int) ($openCount ?? 0), 0, ',', ' ') }}</div></td>
            <td><div class="metric-label">Clôturés</div><div class="metric-value">{{ number_format((int) ($closedCount ?? 0), 0, ',', ' ') }}</div></td>
            <td><div class="metric-label">Durée moyenne</div><div class="metric-value">{{ $avgDuration !== null ? number_format((float) $avgDuration, 0, ',', ' ').' min' : '-' }}</div></td>
        </tr>
    </table>

    <h2 class="section-title">Répartition par statut</h2>
    <table>
        <thead><tr><th>Statut</th><th>Total</th><th>Final</th></tr></thead>
        <tbody>
            @forelse($byStatus as $row)
                <tr><td>{{ $row['label'] ?? '-' }}</td><td>{{ $row['total'] ?? 0 }}</td><td>{{ !empty($row['is_final']) ? 'Oui' : 'Non' }}</td></tr>
            @empty
                <tr><td colspan="3" class="muted">Aucune donnée statut.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section-title">Incidents détaillés</h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Titre</th>
                <th>Département</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Date</th>
                <th>Durée</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
                <tr>
                    <td><strong>{{ $incident->code_incident ?? '-' }}</strong></td>
                    <td>{{ $incident->titre ?? '-' }}</td>
                    <td>{{ $incident->departement?->nom ?? '-' }}</td>
                    <td><span class="badge">{{ $incident->status?->libelle ?? '-' }}</span></td>
                    <td>{{ $incident->priorite?->libelle ?? '-' }}</td>
                    <td>{{ $formatDate($incident->date_debut ?? null) }}</td>
                    <td>{{ $incident->duree_minutes !== null ? $incident->duree_minutes.' min' : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Aucun incident pour cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Rapport généré automatiquement par la plateforme CEET. Les données reflètent les filtres appliqués lors de l’export.</p>
</body>
</html>
