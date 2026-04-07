<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1a1a1a; }

        .header { background:#1a1a2e; color:white; padding:14px 18px; margin-bottom:16px; }
        .header h1 { font-size:15px; }
        .header p  { font-size:9px; margin-top:3px; opacity:.8; }

        .meta { margin-bottom:14px; font-size:9px; color:#555; }
        .meta span { margin-right:16px; }

        table { width:100%; border-collapse:collapse; font-size:9px; }
        thead tr { background:#1a1a2e; color:white; }
        thead th { padding:6px 8px; text-align:left; }
        tbody tr:nth-child(even) { background:#f4f6fb; }
        tbody td { padding:5px 8px; border-bottom:1px solid #e4e8f0; vertical-align:top; }

        .badge { display:inline-block; padding:2px 6px; border-radius:8px;
                 font-size:8px; font-weight:bold; color:white; }
        .badge-create { background:#198754; }
        .badge-update { background:#0d6efd; }
        .badge-delete { background:#dc3545; }
        .badge-other  { background:#6c757d; }

        .footer { margin-top:20px; font-size:8px; color:#aaa;
                  text-align:center; border-top:1px solid #ddd; padding-top:6px; }
    </style>
</head>
<body>

<div class="header">
    <h1>📋 Historique des actions — CEET Gestion Incidents</h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }} · {{ $actions->count() }} action(s)</p>
</div>

<div class="meta">
    @if(!empty($filters['date_from']))
        <span>Du : {{ $filters['date_from'] }}</span>
    @endif
    @if(!empty($filters['date_to']))
        <span>Au : {{ $filters['date_to'] }}</span>
    @endif
    @if(!empty($filters['action_type']))
        <span>Type : {{ ucfirst($filters['action_type']) }}</span>
    @endif
    @if(!empty($filters['q']))
        <span>Recherche : {{ $filters['q'] }}</span>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th style="width:13%">Date / Heure</th>
            <th style="width:15%">Utilisateur</th>
            <th style="width:10%">Action</th>
            <th style="width:14%">Incident</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        @forelse($actions as $action)
        <tr>
            <td>
                {{ optional($action->action_date)?->format('d/m/Y') }}<br>
                <span style="color:#888;">{{ optional($action->action_date)?->format('H:i:s') }}</span>
            </td>
            <td>{{ optional($action->user)?->name ?? '—' }}</td>
            <td>
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
                {{ optional($action->incident)?->code_incident ?? '—' }}<br>
                @if($action->incident)
                <span style="color:#888;font-size:8px;">
                    {{ \Illuminate\Support\Str::limit($action->incident->titre, 25) }}
                </span>
                @endif
            </td>
            <td>{{ $action->description }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center;padding:20px;color:#999;">
                Aucune action trouvée.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    CEET — Historique des actions · Généré automatiquement le {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>