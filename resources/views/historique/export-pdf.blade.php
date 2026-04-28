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
            font-size: 10.5px;
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
            font-size: 10px;
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
            font-size: 8.5px;
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
    $createCount = $actions->where('action_type', 'create')->count();
    $updateCount = $actions->where('action_type', 'update')->count();
    $deleteCount = $actions->where('action_type', 'delete')->count();
    $byAction = $actions->groupBy('action_type')->map(fn ($items, $type) => [
        'label' => ucfirst((string) $type),
        'total' => $items->count(),
    ])->sortByDesc('total')->values();
    $hasFilters = ! empty($filters['date_from'])
        || ! empty($filters['date_to'])
        || ! empty($filters['action_type'])
        || ! empty($filters['q']);
@endphp

<table class="header-table">
    <tr>
        <td class="logo-cell">
            @if(file_exists($logoPath))
                <img class="logo" src="file://{{ str_replace('\\', '/', $logoPath) }}" alt="CEET">
            @endif
        </td>
        <td>
            <h1 class="title">CEET &mdash; Historique des Actions</h1>
            <div class="subtitle">
                Compagnie &Eacute;nergie &Eacute;lectrique du Togo | Direction de la Transformation Digitale<br>
                Rapport d'audit | G&eacute;n&eacute;r&eacute; le {{ now()->format('d/m/Y') }} &agrave; {{ now()->format('H:i') }}
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
                <td>Nombre total d'actions</td>
                <td class="number">{{ $actions->count() }}</td>
            </tr>
            <tr>
                <td>Cr&eacute;ations</td>
                <td class="number">{{ $createCount }}</td>
            </tr>
            <tr>
                <td>Mises &agrave; jour</td>
                <td class="number">{{ $updateCount }}</td>
            </tr>
            <tr>
                <td>Suppressions</td>
                <td class="number">{{ $deleteCount }}</td>
            </tr>
        </tbody>
    </table>
</div>

@if($hasFilters)
<div class="section">
    <h2 class="section-title">2. Filtres appliqu&eacute;s</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Filtre</th>
                <th>Valeur</th>
            </tr>
        </thead>
        <tbody>
            @if(! empty($filters['date_from']))
            <tr>
                <td>Date de d&eacute;but</td>
                <td>{{ $filters['date_from'] }}</td>
            </tr>
            @endif
            @if(! empty($filters['date_to']))
            <tr>
                <td>Date de fin</td>
                <td>{{ $filters['date_to'] }}</td>
            </tr>
            @endif
            @if(! empty($filters['action_type']))
            <tr>
                <td>Type d'action</td>
                <td>{{ ucfirst($filters['action_type']) }}</td>
            </tr>
            @endif
            @if(! empty($filters['q']))
            <tr>
                <td>Recherche</td>
                <td>{{ $filters['q'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

@if($byAction->count())
<div class="section">
    <h2 class="section-title">{{ $hasFilters ? '3' : '2' }}. R&eacute;partition des Actions par Type</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Type d'action</th>
                <th class="number">Nombre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byAction as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td class="number">{{ $row['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="section">
    <h2 class="section-title">{{ $hasFilters ? '4' : '3' }}. Journal des actions</h2>
    @if($actions->count())
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 16%;">Utilisateur</th>
                <th style="width: 11%;">Action</th>
                <th style="width: 17%;">Incident</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($actions as $action)
            <tr>
                <td>
                    {{ optional($action->action_date)?->format('d/m/Y') }}<br>
                    <span class="muted">{{ optional($action->action_date)?->format('H:i:s') }}</span>
                </td>
                <td>{{ optional($action->user)->name ?? '-' }}</td>
                <td>{{ strtoupper($action->action_type) }}</td>
                <td>
                    @if($action->incident)
                        {{ optional($action->incident)->code_incident ?? '-' }}<br>
                        <span class="muted">{{ \Illuminate\Support\Str::limit($action->incident->titre, 26) }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $action->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty">Aucune action trouv&eacute;e sur cette p&eacute;riode.</div>
    @endif
</div>

<div class="footer">
    CEET &mdash; Direction de la Transformation Digitale (DTD) | Application de Gestion des Incidents
</div>
</body>
</html>
