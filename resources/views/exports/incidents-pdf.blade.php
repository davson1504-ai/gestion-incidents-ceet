@php
    $incidents = collect($incidents ?? []);
    $filters = $filters ?? [];

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
    <title>Export incidents - CEET</title>
    <style>
        @page { margin: 22px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1b1b1d; }
        h1, p { margin: 0; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 14px; }
        .kicker { color: #666; font-size: 8px; text-transform: uppercase; letter-spacing: .08em; }
        h1 { font-size: 20px; color: #111827; margin-top: 4px; }
        .meta, .muted { color: #666; }
        .filters { margin: 10px 0 14px; padding: 8px; border: 1px solid #d8dce2; background: #f5f6f8; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eef0f3; color: #3f4652; font-size: 8px; text-transform: uppercase; text-align: left; padding: 6px; border: 1px solid #d8dce2; }
        td { padding: 6px; border: 1px solid #d8dce2; vertical-align: top; }
        .badge { display: inline-block; border: 1px solid #b7bdc7; border-radius: 10px; padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .footer { margin-top: 14px; padding-top: 8px; border-top: 1px solid #d8dce2; color: #666; font-size: 8px; }
    
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
                    <h1>Export des incidents</h1>
                    <p class="meta">Document généré le {{ now()->format('d/m/Y H:i') }} — {{ $incidents->count() }} incident(s)</p>
                </td>
            </tr>
        </table>
    </header>

    <div class="filters">
        <strong>Filtres :</strong>
        département={{ $filters['departement_id'] ?? 'tous' }} ;
        statut={{ $filters['status_id'] ?? 'tous' }} ;
        priorité={{ $filters['priorite_id'] ?? 'toutes' }} ;
        période={{ $filters['date_from'] ?? '-' }} à {{ $filters['date_to'] ?? '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 14%;">Code</th>
                <th style="width: 18%;">Titre</th>
                <th style="width: 15%;">Départ</th>
                <th style="width: 12%;">Statut</th>
                <th style="width: 10%;">Priorité</th>
                <th style="width: 13%;">Début</th>
                <th>Opérateur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
                <tr>
                    <td><strong>{{ $incident->code_incident ?? ('INC-'.$incident->id) }}</strong></td>
                    <td>{{ $incident->titre ?? '-' }}</td>
                    <td>{{ $incident->departement?->nom ?? '-' }}</td>
                    <td><span class="badge">{{ $incident->status?->libelle ?? '-' }}</span></td>
                    <td>{{ $incident->priorite?->libelle ?? '-' }}</td>
                    <td>{{ $formatDate($incident->date_debut ?? null) }}</td>
                    <td>{{ $incident->operateur?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">Aucun incident à exporter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Export généré automatiquement. Document interne CEET.</p>
</body>
</html>
