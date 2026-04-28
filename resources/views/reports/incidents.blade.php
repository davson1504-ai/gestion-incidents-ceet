<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 17mm 16mm 18mm 16mm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            background: #ffffff;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .logo-cell {
            width: 96px;
            text-align: center;
        }

        .logo {
            width: 56px;
            height: auto;
        }

        .title {
            margin: 9px 0 10px 0;
            color: #006b3f;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 11px;
        }

        .gold-rule {
            height: 4px;
            margin: 26px 0 32px 0;
            background: #f0c400;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            margin: 0 0 8px 0;
            color: #006b3f;
            font-size: 15px;
            font-weight: 700;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        .data-table th {
            padding: 5px 8px;
            color: #ffffff;
            background: #006b3f;
            border: 1px solid #d1d5db;
            text-align: left;
            font-weight: 700;
        }

        .data-table td {
            padding: 5px 8px;
            border: 1px solid #d8dde3;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #f1f1f1;
        }

        .data-table tbody tr:nth-child(odd) td {
            background: #ffffff;
        }

        .number {
            width: 120px;
            text-align: right;
            white-space: nowrap;
        }

        .muted {
            color: #6b7280;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -8mm;
            left: 0;
            padding-top: 8px;
            color: #7a7f87;
            border-top: 1px solid #e4c64a;
            font-size: 8px;
        }

        .empty {
            padding: 16px;
            color: #6b7280;
            border: 1px solid #d8dde3;
            background: #f8fafc;
            font-style: italic;
        }
    </style>
</head>
<body>
@php
    $logoPath = public_path('images/logo-ceet.png');
    $periodLabel = $granularity === 'day'
        ? $start->format('d/m/Y')
        : $start->format('m/Y');
    $reportType = $granularity === 'day' ? 'Rapport journalier' : 'Rapport mensuel';
    $closedCount = $closedCount ?? $incidents->filter(fn ($incident) => (bool) optional($incident->status)->is_final)->count();
    $openCount = $openCount ?? max(0, $total - $closedCount);
    $resolutionRate = $total > 0 ? round(($closedCount / $total) * 100, 1) : 0;
    $topTypes = $byType->sortByDesc('total')->take(10)->values();
    $topCauses = $byCause->sortByDesc('total')->take(10)->values();
    $priorityRows = $topDepart->take(8)->values();
@endphp

<table class="header-table">
    <tr>
        <td class="logo-cell">
            @if(file_exists($logoPath))
                <img class="logo" src="file://{{ str_replace('\\', '/', $logoPath) }}" alt="CEET">
            @endif
        </td>
        <td>
            <h1 class="title">CEET &mdash; Rapport des Incidents</h1>
            <div class="subtitle">
                Compagnie &Eacute;nergie &Eacute;lectrique du Togo | Direction de la Transformation Digitale<br>
                {{ $reportType }} | P&eacute;riode : {{ $periodLabel }} | G&eacute;n&eacute;r&eacute; le {{ now()->format('d/m/Y') }}
            </div>
        </td>
    </tr>
</table>

<div class="gold-rule"></div>

<div class="section">
    <h2 class="section-title">1. Indicateurs Cl&eacute;s</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Indicateur</th>
                <th class="number">Valeur</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Nombre total d'incidents</td>
                <td class="number">{{ $total }}</td>
            </tr>
            <tr>
                <td>Incidents en cours</td>
                <td class="number">{{ $openCount }}</td>
            </tr>
            <tr>
                <td>Incidents cl&ocirc;tur&eacute;s</td>
                <td class="number">{{ $closedCount }}</td>
            </tr>
            <tr>
                <td>Taux de r&eacute;solution</td>
                <td class="number">{{ number_format($resolutionRate, 1, ',', ' ') }}%</td>
            </tr>
            <tr>
                <td>Dur&eacute;e moyenne de r&eacute;solution</td>
                <td class="number">{{ number_format($avgDuration ?? 0, 0, ',', ' ') }} min</td>
            </tr>
        </tbody>
    </table>
</div>

@if($topTypes->count())
<div class="section">
    <h2 class="section-title">2. R&eacute;partition des Incidents par Type</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Type d'incident</th>
                <th class="number">Nombre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topTypes as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="number">{{ $row['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($priorityRows->count())
<div class="section">
    <h2 class="section-title">3. D&eacute;partements n&eacute;cessitant une attention prioritaire</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>D&eacute;partement</th>
                <th class="number">Nombre d'incidents</th>
            </tr>
        </thead>
        <tbody>
            @foreach($priorityRows as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="number">{{ $row['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($topCauses->count())
<div class="section">
    <h2 class="section-title">4. Causes les plus fr&eacute;quentes</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cause</th>
                <th class="number">Nombre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topCauses as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="number">{{ $row['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($incidents->count())
<div class="section">
    <h2 class="section-title">5. D&eacute;tail des incidents</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 17%;">Code</th>
                <th>Incident</th>
                <th style="width: 20%;">D&eacute;partement</th>
                <th style="width: 14%;">Statut</th>
                <th style="width: 14%;">D&eacute;but</th>
                <th class="number" style="width: 12%;">Dur&eacute;e</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $incident)
            <tr>
                <td>{{ $incident->code_incident }}</td>
                <td>{{ \Illuminate\Support\Str::limit($incident->titre, 42) }}</td>
                <td>{{ optional($incident->departement)->nom ?? '-' }}</td>
                <td>{{ optional($incident->status)->libelle ?? '-' }}</td>
                <td>{{ optional($incident->date_debut)?->format('d/m/Y') }}</td>
                <td class="number">{{ $incident->duree_minutes ? $incident->duree_minutes.' min' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="empty">Aucun incident sur cette p&eacute;riode.</div>
@endif

<div class="footer">
    CEET &mdash; Direction de la Transformation Digitale (DTD) | Application de Gestion des Incidents
</div>
</body>
</html>
