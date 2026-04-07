<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }

        .header { background: #1e3a5f; color: white; padding: 16px 20px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; font-weight: bold; }
        .header p  { font-size: 10px; margin-top: 4px; opacity: .85; }

        .kpis { display: flex; gap: 10px; margin-bottom: 20px; }
        .kpi  { flex: 1; border: 1px solid #dde; border-radius: 6px; padding: 10px 14px; background: #f7f9fc; }
        .kpi .val  { font-size: 22px; font-weight: bold; color: #1e3a5f; }
        .kpi .lbl  { font-size: 9px; color: #666; margin-top: 2px; }

        .section { margin-bottom: 18px; }
        .section h2 { font-size: 12px; font-weight: bold; color: #1e3a5f;
                      border-bottom: 2px solid #1e3a5f; padding-bottom: 4px; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #1e3a5f; color: white; }
        thead th { padding: 6px 8px; text-align: left; }
        tbody tr:nth-child(even) { background: #f2f5fb; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #e8eaf0; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 10px;
                 font-size: 9px; font-weight: bold; color: white; }

        .footer { margin-top: 24px; font-size: 9px; color: #999; text-align: center;
                  border-top: 1px solid #ddd; padding-top: 8px; }

        .stat-row td { padding: 5px 10px; }
        .stat-bar-wrap { background: #e8eaf0; border-radius: 4px; height: 8px; width: 100%; }
        .stat-bar      { background: #1e3a5f; border-radius: 4px; height: 8px; }
    </style>
</head>
<body>

{{-- ── En-tête ──────────────────────────────────────────────────────── --}}
<div class="header">
    <h1>
        @if($granularity === 'day')
            Rapport journalier — {{ $start->format('d/m/Y') }}
        @else
            Rapport mensuel — {{ $start->format('F Y') }}
        @endif
    </h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }} · CEET — Gestion des Incidents</p>
</div>

{{-- ── KPIs ─────────────────────────────────────────────────────────── --}}
<table style="width:100%; margin-bottom:18px;">
    <tr>
        <td style="width:25%; padding:0 6px 0 0;">
            <div class="kpi">
                <div class="val">{{ $total }}</div>
                <div class="lbl">Total incidents</div>
            </div>
        </td>
        <td style="width:25%; padding:0 6px;">
            <div class="kpi">
                <div class="val">{{ $byStatus->where('label', '!=', 'Clôturé')->sum('total') }}</div>
                <div class="lbl">En cours</div>
            </div>
        </td>
        <td style="width:25%; padding:0 6px;">
            <div class="kpi">
                <div class="val">{{ $byStatus->firstWhere('label', 'Clôturé')['total'] ?? 0 }}</div>
                <div class="lbl">Clôturés</div>
            </div>
        </td>
        <td style="width:25%; padding:0 0 0 6px;">
            <div class="kpi">
                <div class="val">{{ number_format($avgDuration ?? 0, 0, ',', ' ') }}</div>
                <div class="lbl">Durée moy. (min)</div>
            </div>
        </td>
    </tr>
</table>

{{-- ── Répartition par statut ──────────────────────────────────────── --}}
@if($byStatus->count())
<div class="section">
    <h2>Répartition par statut</h2>
    <table>
        <thead>
            <tr><th>Statut</th><th>Nb</th><th style="width:55%">Proportion</th></tr>
        </thead>
        <tbody>
            @foreach($byStatus as $row)
            <tr>
                <td><span class="badge" style="background:{{ $row['color'] }}">{{ $row['label'] }}</span></td>
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
    <h2>Répartition par priorité</h2>
    <table>
        <thead>
            <tr><th>Priorité</th><th>Nb</th><th style="width:55%">Proportion</th></tr>
        </thead>
        <tbody>
            @foreach($byPriorite as $row)
            <tr>
                <td><span class="badge" style="background:{{ $row['color'] }}">{{ $row['label'] }}</span></td>
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
    <h2>Top départs touchés</h2>
    <table>
        <thead><tr><th>Départ</th><th>Nb incidents</th></tr></thead>
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

{{-- ── Liste détaillée ─────────────────────────────────────────────── --}}
@if($incidents->count())
<div class="section">
    <h2>Liste des incidents ({{ $incidents->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Titre</th>
                <th>Département</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Début</th>
                <th>Durée (min)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $inc)
            <tr>
                <td>{{ $inc->code_incident }}</td>
                <td>{{ \Illuminate\Support\Str::limit($inc->titre, 40) }}</td>
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
                <td>{{ $inc->duree_minutes ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p style="color:#999; text-align:center; padding:30px 0;">Aucun incident sur cette période.</p>
@endif

<div class="footer">
    CEET — Rapport généré automatiquement · {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>