@php
    
    $actionLabel = function ($action) {
        $key = mb_strtolower(trim((string) $action));

        $labels = [
            'create' => 'Création',
            'creation' => 'Création',
            'create_incident' => 'Création',
            'update' => 'Modification',
            'modification' => 'Modification',
            'update_incident' => 'Modification',
            'assign' => 'Affectation',
            'assignation' => 'Affectation',
            'affectation' => 'Affectation',
            'prise_en_charge' => 'Prise en charge',
            'resolution' => 'Résolution',
            'rapport' => 'Rapport d’intervention',
            'validation' => 'Validation',
            'close' => 'Clôture',
            'cloture' => 'Clôture',
            'delete' => 'Suppression',
        ];

        return $labels[$key] ?? str($action ?: '-')->replace('_', ' ')->title();
    };

$actions = collect($actions ?? []);
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
    <title>Export historique - CEET</title>
    <style>
        @page { margin: 22px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1b1b1d; }
        h1, p { margin: 0; }
        .header { border-bottom: 2px solid #141b2b; padding-bottom: 10px; margin-bottom: 14px; }
        .kicker { color: #76777d; font-size: 8px; text-transform: uppercase; letter-spacing: .08em; }
        h1 { font-size: 20px; color: #141b2b; margin-top: 4px; }
        .meta { margin-top: 6px; color: #45464c; }
        .filters { margin: 10px 0 14px; padding: 8px; border: 1px solid #e5e2e3; background: #f6f3f4; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f0edee; color: #45464c; font-size: 8px; text-transform: uppercase; text-align: left; padding: 6px; border: 1px solid #e5e2e3; }
        td { padding: 6px; border: 1px solid #e5e2e3; vertical-align: top; }
        .badge { display: inline-block; border: 1px solid #c6c6cd; border-radius: 12px; padding: 3px 6px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .muted { color: #76777d; }
        .footer { margin-top: 14px; padding-top: 8px; border-top: 1px solid #e5e2e3; color: #76777d; font-size: 8px; }
    
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
                    <h1>Export historique</h1>
                    <p class="meta">Document généré le {{ now()->format('d/m/Y H:i') }} — {{ $actions->count() }} action(s)</p>
                </td>
            </tr>
        </table>
    </header>

    <div class="filters">
        <strong>Filtres :</strong>
        utilisateur={{ $filters['user_id'] ?? 'tous' }} ;
        action={{ $filters['action_type'] ?? 'toutes' }} ;
        période={{ $filters['date_from'] ?? '-' }} à {{ $filters['date_to'] ?? '-' }} ;
        recherche={{ $filters['q'] ?? '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 13%;">Date</th>
                <th style="width: 17%;">Utilisateur</th>
                <th style="width: 12%;">Action</th>
                <th style="width: 14%;">Incident</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($actions as $action)
                <tr>
                    <td>{{ $formatDate($action->action_date ?? null) }}</td>
                    <td>{{ $action->user?->name ?? '-' }}<br><span class="muted">{{ $action->user?->email ?? '' }}</span></td>
                    <td><span class="badge">{{ $actionLabel($action->action_type ?? null) }}</span></td>
                    <td>{{ $action->incident?->code_incident ?? '-' }}<br><span class="muted">{{ $action->incident?->titre ?? '' }}</span></td>
                    <td>{{ $action->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">Aucune action à exporter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Export généré automatiquement. Document interne CEET.</p>
</body>
</html>
