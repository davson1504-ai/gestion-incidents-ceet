<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
            background: white;
        }

        /* En-tête professionnel */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #333333;
            padding: 18px 24px;
            margin-bottom: 24px;
            background: white;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-container {
            width: 52px;
            height: 52px;
            background: white;
            border-radius: 8px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-text h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            line-height: 1.2;
        }

        .header-text p {
            font-size: 9px;
            color: #6b7280;
            margin: 3px 0 0 0;
        }

        .header-right {
            text-align: right;
        }

        .header-right .period {
            font-size: 12px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 4px;
        }

        .header-right .date {
            font-size: 9px;
            color: #9ca3af;
        }

        /* KPIs */
        .kpis-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .kpi {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: all 0.2s;
        }

        .kpi.critical { border-left: 4px solid #666666; }
        .kpi.warning { border-left: 4px solid #666666; }
        .kpi.success { border-left: 4px solid #666666; }
        .kpi.info { border-left: 4px solid #666666; }

        .kpi-value {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
            margin-bottom: 6px;
        }

        .kpi-label {
            font-size: 8.5px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Sections */
        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333333;
        }

        .section-header::before {
            content: '';
            display: block;
            width: 0px;
            height: 0px;
            background: transparent;
            border-radius: 2px;
        }

        .section h2 {
            font-size: 12px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        thead tr {
            background: #4a5568;
            color: white;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #f3f4f6;
        }

        tbody td {
            padding: 8px 10px;
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Barres de progression */
        .stat-bar-wrap {
            background: #e5e7eb;
            border-radius: 6px;
            height: 6px;
            width: 100%;
            overflow: hidden;
        }

        .stat-bar {
            background: #4a5568;
            height: 100%;
            border-radius: 6px;
            transition: width 0.3s;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }

        /* Page break */
        @page {
            margin: 20mm;
            size: A4 portrait;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</head>
<body>
@php
    $topCauses = $byCause->sortByDesc('total')->take(10);
    $logoPath = public_path('images/logo-ceet.png');
@endphp

{{-- ── En-tête professionnel avec logo ──────────────────────────────── --}}
<div class="page-header">
    <div class="header-left">
        @if(file_exists($logoPath))
        <div class="logo-container">
            <img src="file://{{ str_replace('\\', '/', $logoPath) }}" alt="CEET Logo">
        </div>
        @endif
        <div class="header-text">
            <h1>CEET — Gestion des Incidents</h1>
            <p>Rapport d'analyse automatisé</p>
        </div>
    </div>
    <div class="header-right">
        <div class="period">
            @if($granularity === 'day')
                Rapport journalier
            @else
                Rapport mensuel
            @endif
        </div>
        <div class="date">
            @if($granularity === 'day')
                {{ $start->format('d') }} {{ $start->monthName }} {{ $start->format('Y') }}
            @else
                {{ $start->monthName }} {{ $start->format('Y') }}
            @endif
        </div>
    </div>
</div>

{{-- ── KPIs en grille ──────────────────────────────────────────────── --}}
<div class="kpis-grid">
    <div class="kpi critical">
        <div class="kpi-value">{{ $total }}</div>
        <div class="kpi-label">Incidents totaux</div>
    </div>
    <div class="kpi warning">
        <div class="kpi-value">{{ $byStatus->where('label', '!=', 'Clôturé')->sum('total') }}</div>
        <div class="kpi-label">En cours</div>
    </div>
    <div class="kpi success">
        <div class="kpi-value">{{ $byStatus->firstWhere('label', 'Clôturé')['total'] ?? 0 }}</div>
        <div class="kpi-label">Clôturés</div>
    </div>
    <div class="kpi info">
        <div class="kpi-value">{{ number_format($avgDuration ?? 0, 0, ',', ' ') }}</div>
        <div class="kpi-label">Durée moy. (min)</div>
    </div>
</div>

{{-- ── Répartition par statut ──────────────────────────────────────── --}}
@if($byStatus->count())
<div class="section">
    <div class="section-header">
        <h2>Répartition par statut</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Statut</th>
                <th style="width:12%">Nombre</th>
                <th style="width:60%">Proportion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byStatus as $row)
            <tr>
                <td>
                    <span class="badge" style="background:{{ $row['color'] }}">{{ $row['label'] }}</span>
                </td>
                <td>{{ $row['total'] }}</td>
                <td>
                    @if($total > 0)
                    <div class="stat-bar-wrap">
                        <div class="stat-bar" style="width:{{ round($row['total']/$total*100) }}%; background:{{ $row['color'] }}"></div>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Répartition par priorité ────────────────────────────────────── --}}
@if($byPriorite->count())
<div class="section">
    <div class="section-header">
        <h2>Répartition par priorité</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Priorité</th>
                <th style="width:12%">Nombre</th>
                <th style="width:60%">Proportion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byPriorite as $row)
            <tr>
                <td>
                    <span class="badge" style="background:{{ $row['color'] }}">{{ $row['label'] }}</span>
                </td>
                <td>{{ $row['total'] }}</td>
                <td>
                    @if($total > 0)
                    <div class="stat-bar-wrap">
                        <div class="stat-bar" style="width:{{ round($row['total']/$total*100) }}%; background:{{ $row['color'] }}"></div>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Top départs ─────────────────────────────────────────────────── --}}
@if($topDepart->count())
<div class="section">
    <div class="section-header">
        <h2>Top départs touchés</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Départ</th>
                <th style="width:20%">Nombre d'incidents</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topDepart as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Causes les plus fréquentes ───────────────────────────────────── --}}
@if($topCauses->count())
<div class="section">
    <div class="section-header">
        <h2>Top 10 — Causes les plus fréquentes</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Cause</th>
                <th style="width:20%">Nombre d'incidents</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topCauses as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Liste détaillée des incidents ───────────────────────────── --}}
@if($incidents->count())
<div class="section">
    <div class="section-header">
        <h2>Détail des incidents ({{ $incidents->count() }})</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Titre</th>
                <th>Département</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th style="width:12%">Début</th>
                <th style="width:8%">Durée (min)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $inc)
            <tr>
                <td><strong>{{ $inc->code_incident }}</strong></td>
                <td>{{ \Illuminate\Support\Str::limit($inc->titre, 35) }}</td>
                <td>{{ optional($inc->departement)->nom ?? '—' }}</td>
                <td>
                    <span class="badge" style="background:{{ optional($inc->statut)->couleur ?? '#6c757d' }}">
                        {{ optional($inc->statut)->libelle ?? '—' }}
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:{{ optional($inc->priorite)->couleur ?? '#aaa' }}">
                        {{ optional($inc->priorite)->libelle ?? '—' }}
                    </span>
                </td>
                <td>{{ optional($inc->date_debut)?->format('d/m/Y H:i') }}</td>
                <td style="text-align:right">{{ $inc->duree_minutes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="empty-state">
    ✗ Aucun incident sur cette période
</div>
@endif

</body>
</html>
