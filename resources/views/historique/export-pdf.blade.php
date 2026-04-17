<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { width:100%; height:100%; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
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
            padding: 16px 20px;
            margin-bottom: 18px;
            background: white;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-container {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 6px;
            padding: 3px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-text h1 {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            line-height: 1.2;
        }

        .header-text p {
            font-size: 8px;
            color: #6b7280;
            margin: 3px 0 0 0;
        }

        .header-right {
            text-align: right;
        }

        .header-right .count {
            font-size: 11px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 2px;
        }

        .header-right .date {
            font-size: 8px;
            color: #9ca3af;
        }

        /* Métadonnées */
        .meta {
            margin-bottom: 14px;
            font-size: 8px;
            color: #6b7280;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .meta-label {
            font-weight: 600;
            color: #1f2937;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        thead tr {
            background: #4a5568;
            color: white;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 8px;
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
            padding: 7px 10px;
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .badge-create {
            background: #717171;
        }

        .badge-update {
            background: #717171;
        }

        .badge-delete {
            background: #717171;
        }

        .badge-other {
            background: #717171;
        }

        /* Code incident */
        .incident-code {
            font-weight: 600;
            color: #333333;
        }

        .incident-title {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Footer */
        .footer {
            margin-top: 18px;
            padding-top: 10px;
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

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #9ca3af;
            font-style: italic;
        }

        @page {
            margin: 20mm;
            size: A4 portrait;
        }
    </style>
</head>
<body>

{{-- En-tête professionnel avec logo --}}
<div class="page-header">
    <div class="header-left">
        @php $logoPath = public_path('images/logo-ceet.png'); @endphp
        @if(file_exists($logoPath))
        <div class="logo-container">
            <img src="file://{{ str_replace('\\', '/', $logoPath) }}" alt="CEET Logo">
        </div>
        @endif
        <div class="header-text">
            <h1>Historique des Actions</h1>
            <p>CEET — Gestion des Incidents</p>
        </div>
    </div>
    <div class="header-right">
        <div class="count">{{ $actions->count() }} action(s)</div>
        <div class="date">{{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

{{-- Filtres appliqués --}}
@if(!empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['action_type']) || !empty($filters['q']))
<div class="meta">
    @if(!empty($filters['date_from']))
        <span><strong class="meta-label">Début:</strong> {{ $filters['date_from'] }}</span>
    @endif
    @if(!empty($filters['date_to']))
        <span><strong class="meta-label">Fin:</strong> {{ $filters['date_to'] }}</span>
    @endif
    @if(!empty($filters['action_type']))
        <span><strong class="meta-label">Type:</strong> {{ ucfirst($filters['action_type']) }}</span>
    @endif
    @if(!empty($filters['q']))
        <span><strong class="meta-label">Recherche:</strong> {{ $filters['q'] }}</span>
    @endif
</div>
@endif

{{-- Tableau des actions --}}
<table>
    <thead>
        <tr>
            <th style="width: 15%">Date / Heure</th>
            <th style="width: 16%">Utilisateur</th>
            <th style="width: 10%">Action</th>
            <th style="width: 15%">Incident</th>
            <th style="width: 44%">Description</th>
        </tr>
    </thead>
    <tbody>
        @forelse($actions as $action)
        <tr>
            <td>
                <strong>{{ optional($action->action_date)?->format('d/m/Y') }}</strong><br>
                <span style="color: #9ca3af; font-size: 8px;">{{ optional($action->action_date)?->format('H:i:s') }}</span>
            </td>
            <td>{{ optional($action->user)?->name ?? '—' }}</td>
            <td style="text-align: center;">
                @php
                    $cls = match($action->action_type) {
                        'create' => 'badge-create',
                        'update' => 'badge-update',
                        'delete' => 'badge-delete',
                        default  => 'badge-other',
                    };
                @endphp
                <span class="badge {{ $cls }}">{{ strtoupper($action->action_type) }}</span>
            </td>
            <td>
                @if($action->incident)
                <div class="incident-code">{{ optional($action->incident)?->code_incident ?? '—' }}</div>
                <div class="incident-title">{{ \Illuminate\Support\Str::limit($action->incident->titre, 30) }}</div>
                @else
                <span style="color: #9ca3af;">—</span>
                @endif
            </td>
            <td>{{ $action->description }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="empty-state">
                ✗ Aucune action trouvée sur cette période
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- Footer --}}
<div class="footer">
    <div class="footer-left">CEET — Gestion des Incidents</div>
    <div class="footer-right">{{ now()->format('d/m/Y H:i') }}</div>
</div>

</body>
</html>
