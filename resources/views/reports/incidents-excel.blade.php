<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight:bold; font-size:14px; background-color:#1e3a5f; color:#ffffff;">
                @if($granularity === 'day')
                    Rapport journalier — {{ $start->format('d/m/Y') }}
                @else
                    Rapport mensuel — {{ $start->format('F Y') }}
                @endif
                · Généré le {{ now()->format('d/m/Y H:i') }}
            </th>
        </tr>
        <tr><td colspan="7"></td></tr>

        {{-- KPIs --}}
        <tr>
            <th style="background-color:#e8edf5; font-weight:bold;">Total incidents</th>
            <th style="background-color:#e8edf5; font-weight:bold;">En cours</th>
            <th style="background-color:#e8edf5; font-weight:bold;">Clôturés</th>
            <th style="background-color:#e8edf5; font-weight:bold;">Durée moy. (min)</th>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>{{ $total }}</td>
            <td>{{ $byStatus->where('label', '!=', 'Clôturé')->sum('total') }}</td>
            <td>{{ $byStatus->firstWhere('label', 'Clôturé')['total'] ?? 0 }}</td>
            <td>{{ number_format($avgDuration ?? 0, 0, ',', ' ') }}</td>
            <td colspan="3"></td>
        </tr>
        <tr><td colspan="7"></td></tr>

        {{-- En-têtes colonnes --}}
        <tr>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Code</th>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Titre</th>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Département</th>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Statut</th>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Priorité</th>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Début</th>
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold;">Durée (min)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($incidents as $inc)
        <tr>
            <td>{{ $inc->code_incident }}</td>
            <td>{{ $inc->titre }}</td>
            <td>{{ optional($inc->departement)->nom ?? '—' }}</td>
            <td>{{ optional($inc->statut)->libelle ?? '—' }}</td>
            <td>{{ optional($inc->priorite)->libelle ?? '—' }}</td>
            <td>{{ optional($inc->date_debut)?->format('d/m/Y H:i') }}</td>
            <td>{{ $inc->duree_minutes ?? '' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center; color:#999;">
                Aucun incident sur cette période.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>