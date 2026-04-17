@php
    $topCauses = $byCause->sortByDesc('total')->take(10);
@endphp

<table>
    <thead>
        {{-- En-tête principal --}}
        <tr>
            <th colspan="7" style="font-weight:bold; font-size:14px; background-color:#ef2433; color:#ffffff; padding:12px 8px;">
                @if($granularity === 'day')
                    📋 Rapport journalier — {{ $start->format('d/m/Y') }}
                @else
                    📊 Rapport mensuel — {{ $start->format('F Y') }}
                @endif
            </th>
        </tr>
        <tr>
            <td colspan="7" style="font-size:10px; color:#6b7280; padding:6px 8px;">
                Généré le {{ now()->format('d/m/Y H:i') }} • CEET Gestion des Incidents
            </td>
        </tr>
        <tr><td colspan="7"></td></tr>

        {{-- KPIs --}}
        <tr>
            <th style="background-color:#dbeafe; font-weight:bold; color:#1e293b; padding:8px;">Total incidents</th>
            <th style="background-color:#fef08a; font-weight:bold; color:#1e293b; padding:8px;">En cours</th>
            <th style="background-color:#dcfce7; font-weight:bold; color:#1e293b; padding:8px;">Clôturés</th>
            <th style="background-color:#e9d5ff; font-weight:bold; color:#1e293b; padding:8px;">Durée moy. (min)</th>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td style="font-weight:bold; font-size:12px; background-color:#f0f9ff; padding:8px;">{{ $total }}</td>
            <td style="font-weight:bold; font-size:12px; background-color:#fffbeb; padding:8px;">{{ $byStatus->where('label', '!=', 'Clôturé')->sum('total') }}</td>
            <td style="font-weight:bold; font-size:12px; background-color:#f0fdf4; padding:8px;">{{ $byStatus->firstWhere('label', 'Clôturé')['total'] ?? 0 }}</td>
            <td style="font-weight:bold; font-size:12px; background-color:#faf5ff; padding:8px;">{{ number_format($avgDuration ?? 0, 0, ',', ' ') }}</td>
            <td colspan="3"></td>
        </tr>
        <tr><td colspan="7"></td></tr>

        {{-- Top causes --}}
        <tr>
            <th style="background-color:#334155; color:#ffffff; font-weight:bold; padding:8px;" colspan="2">Top 10 — Causes fréquentes</th>
            <th style="background-color:#334155; color:#ffffff; font-weight:bold; padding:8px;">Nombre incidents</th>
            <td colspan="4"></td>
        </tr>
        @forelse($topCauses as $cause)
        <tr>
            <td colspan="2" style="padding:6px 8px;">{{ $cause['label'] }}</td>
            <td style="padding:6px 8px; font-weight:bold;">{{ $cause['total'] }}</td>
            <td colspan="4"></td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="padding:8px; text-align:center; color:#999;">Aucune cause sur la période.</td>
        </tr>
        @endforelse
        <tr><td colspan="7"></td></tr>

        {{-- En-têtes du détail --}}
        <tr>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Code</th>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Titre</th>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Département</th>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Statut</th>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Priorité</th>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Début</th>
            <th style="background-color:#1e293b; color:#ffffff; font-weight:bold; padding:8px;">Durée (min)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($incidents as $inc)
        <tr>
            <td style="padding:6px 8px; font-weight:bold;">{{ $inc->code_incident }}</td>
            <td style="padding:6px 8px;">{{ $inc->titre }}</td>
            <td style="padding:6px 8px;">{{ optional($inc->departement)->nom ?? '—' }}</td>
            <td style="padding:6px 8px; background-color:{{ optional($inc->statut)->couleur ?? '#6c757d' }}22; color:{{ optional($inc->statut)->couleur ?? '#6c757d' }}; font-weight:bold;">
                {{ optional($inc->statut)->libelle ?? '—' }}
            </td>
            <td style="padding:6px 8px; background-color:{{ optional($inc->priorite)->couleur ?? '#aaa' }}22; color:{{ optional($inc->priorite)->couleur ?? '#aaa' }}; font-weight:bold;">
                {{ optional($inc->priorite)->libelle ?? '—' }}
            </td>
            <td style="padding:6px 8px;">{{ optional($inc->date_debut)?->format('d/m/Y H:i') }}</td>
            <td style="padding:6px 8px; text-align:right;">{{ $inc->duree_minutes ?? '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center; padding:16px 8px; color:#999;">
                ✗ Aucun incident sur cette période.
            </td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="7" style="font-size:9px; color:#999; text-align:center; padding:8px; border-top:1px solid #ddd;">
                CEET — Rapport généré automatiquement • {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </tfoot>
</table>
