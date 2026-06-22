@extends('layouts.app')

@section('title', 'Vue console réseau')

@section('page_css')
    @vite([
        'resources/css/app.css'
    ])
@endsection

@php
    $currentUser = auth()->user();
    $userName = $currentUser?->name ?? 'Administrateur';
    $userEmail = $currentUser?->email ?? 'Console CEET';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames') ? ($currentUser->getRoleNames()->first() ?: 'Administrateur') : 'Administrateur';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    $liveIncidents = collect($liveIncidents ?? []);
    $recentCriticalAlerts = collect($recentCriticalAlerts ?? []);
    $networkNodes = collect($networkNodes ?? []);

    $activeFaults = (int) ($activeFaults ?? $liveIncidents->count());
    $criticalFaults = (int) ($criticalFaults ?? 0);
    $networkLoad = (float) ($networkLoad ?? 0);
    $nominalFrequency = number_format((float) ($nominalFrequency ?? 50.00), 2, ',', ' ');
    $frequencyStatus = $frequencyStatus ?? 'Plage stable';
    $averageResponseMinutes = $averageResponseMinutes ?? null;
    $responseDeltaMinutes = $responseDeltaMinutes ?? null;
    $lastCheckAt = $lastCheckAt ?? now()->format('H:i:s');

    $formatMinutes = function ($minutes) {
        if ($minutes === null || $minutes === '') {
            return 'N/A';
        }

        $minutes = (int) round((float) $minutes);

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $remaining > 0 ? $hours . ' h ' . $remaining . ' min' : $hours . ' h';
    };

    $incidentCode = fn ($incident) => $incident->code_incident ?: 'INC-' . str_pad((string) $incident->id, 5, '0', STR_PAD_LEFT);

    $elapsedMinutes = function ($incident) {
        if (! $incident->date_debut) {
            return null;
        }

        return $incident->date_debut->diffInMinutes(now());
    };

    $priorityClass = function ($incident) {
        $level = (int) ($incident->priorite?->niveau ?? 99);

        if ($level === 1) {
            return 'is-critical';
        }

        if ($level === 2) {
            return 'is-high';
        }

        return 'is-neutral';
    };

    $actionLabel = function ($incident) {
        if (! $incident->responsable_id) {
            return 'Affecter';
        }

        $statusCode = mb_strtoupper((string) ($incident->status?->code ?? ''));

        if (str_contains($statusCode, 'EN_COURS')) {
            return 'Détails';
        }

        return 'Consulter';
    };

    $nodeState = function ($node) {
        if (! $node->is_active) {
            return ['label' => 'Maintenance', 'icon' => 'build', 'class' => 'is-maintenance', 'detail' => 'Hors ligne planifié'];
        }

        if ((int) ($node->critical_incidents_count ?? 0) > 0) {
            return ['label' => 'Défaut critique', 'icon' => 'warning', 'class' => 'is-critical', 'detail' => 'Charge : ERR'];
        }

        if ((int) ($node->open_incidents_count ?? 0) > 0) {
            return ['label' => 'Sous surveillance', 'icon' => 'sensors', 'class' => 'is-warning', 'detail' => 'Incident actif'];
        }

        $load = $node->charge_maximale !== null
            ? number_format((float) $node->charge_maximale, 0, ',', ' ') . ' ' . ($node->charge_unite ?: 'kVA')
            : 'N/A';

        return ['label' => 'Opérationnel', 'icon' => 'electric_bolt', 'class' => 'is-operational', 'detail' => 'Charge : ' . $load];
    };

    $oldestActiveIncident = $oldestActiveIncident ?? null;
    $focusDepartement = $oldestActiveIncident?->departement;
    $focusZone = $focusDepartement?->zone ?: 'Zone CEET';
    $focusStation = $focusDepartement?->poste_source ?: ($focusDepartement?->poste_repartition ?: 'Poste source');
    $systemState = $criticalFaults > 0 ? 'Sous surveillance' : 'Stable';
@endphp

@section('content')
<div class="ceet-admin-dashboard-page ceet-console-page" data-admin-dashboard data-vue-console-page>
<main class="ceet-admin-main ceet-console-main">
    <section class="ceet-console-kpis" aria-label="Indicateurs réseau">
        <article class="ceet-console-kpi">
            <header>
                <span>Supervision en direct</span>
                <b class="is-online"><i></i>En ligne</b>
            </header>
            <strong data-console-clock>{{ now()->format('H:i:s') }}</strong>
            <p>GMT+0 · Actualisation : 2 s</p>
        </article>

        <article class="ceet-console-kpi">
            <span>Charge réseau surveillée</span>
            <div><strong>{{ number_format($networkLoad, 1, ',', ' ') }}</strong><em>%</em></div>
            <meter min="0" max="100" value="{{ $networkLoad }}">{{ $networkLoad }}%</meter>
        </article>

        <article class="ceet-console-kpi">
            <span>Fréquence nominale</span>
            <div><strong>{{ $nominalFrequency }}</strong><em>Hz</em></div>
            <p class="{{ $criticalFaults > 0 ? 'is-watch' : 'is-stable' }}">{{ $frequencyStatus }}</p>
        </article>

        <article class="ceet-console-kpi is-danger">
            <span>Défauts actifs</span>
            <div><strong>{{ str_pad((string) $criticalFaults, 2, '0', STR_PAD_LEFT) }}</strong><em>critiques</em></div>
            <p>Intervention immédiate requise</p>
        </article>

        <article class="ceet-console-kpi">
            <span>Délai de réponse</span>
            <div><strong>{{ $averageResponseMinutes !== null ? (int) round($averageResponseMinutes) : 0 }}</strong><em>min</em></div>
            @if ($responseDeltaMinutes !== null)
                <p class="{{ $responseDeltaMinutes <= 0 ? 'is-stable' : 'is-watch' }}">
                    {{ $responseDeltaMinutes <= 0 ? '−' : '+' }}{{ abs($responseDeltaMinutes) }} min vs période précédente
                </p>
            @else
                <p>Moyenne des interventions enregistrées</p>
            @endif
        </article>
    </section>

    <section class="ceet-console-grid">
        <article class="ceet-console-feed">
            <header class="ceet-console-panel-header">
                <h2>Flux des incidents en direct</h2>
                <div>
                    @can('incidents.export')
                        <a href="{{ route('incidents.export', ['format' => 'csv']) }}">Exporter CSV</a>
                    @endcan
                    <a href="{{ route('incidents.vue-console') }}" class="is-primary">Actualiser le flux</a>
                </div>
            </header>

            <div class="ceet-console-table-wrap">
                <table class="ceet-console-table">
                    <thead>
                        <tr>
                            <th>Code incident</th>
                            <th>Statut</th>
                            <th>Type d'incident</th>
                            <th>Localisation</th>
                            <th>Temps écoulé</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($liveIncidents as $incident)
                            @php
                                $elapsed = $elapsedMinutes($incident);
                                $statusLabel = $incident->status?->libelle ?? 'Ouvert';
                                $priorityLabel = $incident->priorite?->libelle ?: $statusLabel;
                                $location = $incident->localisation ?: ($incident->departement?->nom ?? 'Localisation non renseignée');
                            @endphp
                            <tr>
                                <td><strong>#{{ $incidentCode($incident) }}</strong></td>
                                <td>
                                    <span class="ceet-console-status {{ $priorityClass($incident) }}">
                                        {{ $priorityLabel }}
                                    </span>
                                </td>
                                <td>{{ $incident->typeIncident?->libelle ?? ($incident->titre ?: 'Incident réseau') }}</td>
                                <td>{{ $location }}</td>
                                <td class="{{ (int) ($incident->priorite?->niveau ?? 99) === 1 ? 'is-critical-time' : '' }}">
                                    <span data-incident-elapsed data-started-at="{{ $incident->date_debut?->toIso8601String() }}">
                                        {{ $formatMinutes($elapsed) }}
                                    </span>
                                </td>
                                <td class="is-right">
                                    <a href="{{ route('incidents.show', $incident) }}">{{ $actionLabel($incident) }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ceet-console-empty">Aucun incident actif à afficher.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer>
                <a href="{{ route('incidents.index') }}">Charger les 50 incidents précédents</a>
            </footer>
        </article>

        <aside class="ceet-console-aside">
            <article class="ceet-console-alerts">
                <header>
                    <span>Alertes système critiques</span>
                    <span class="material-symbols-outlined">error</span>
                </header>

                <div class="ceet-console-alert-list">
                    @forelse ($recentCriticalAlerts as $alert)
                        <div class="ceet-console-alert-item">
                            <span class="material-symbols-outlined">bolt</span>
                            <div>
                                <strong>{{ $alert->typeIncident?->libelle ?? ($alert->titre ?: 'Incident prioritaire') }}</strong>
                                <p>
                                    {{ $alert->departement?->nom ?? 'Secteur non renseigné' }}.
                                    {{ $alert->description ? str($alert->description)->limit(95) : 'Risque réseau à traiter en priorité.' }}
                                </p>
                                <small>Détecté {{ $alert->date_debut?->diffForHumans() ?? 'récemment' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="ceet-console-alert-item">
                            <span class="material-symbols-outlined">check_circle</span>
                            <div>
                                <strong>Aucune alerte critique</strong>
                                <p>Le réseau ne présente pas d’incident critique ouvert dans la console.</p>
                                <small>Surveillance active</small>
                            </div>
                        </div>
                    @endforelse

                    <div class="ceet-console-alert-item is-muted">
                        <span class="material-symbols-outlined">router</span>
                        <div>
                            <strong>Synchronisation de télémétrie</strong>
                            <p>Données consolidées depuis les incidents, les départements et les postes sources enregistrés.</p>
                            <small>Contrôle permanent</small>
                        </div>
                    </div>
                </div>
            </article>

            <article class="ceet-console-map-panel">
                <header>
                    <span>Géographie du réseau</span>
                    <strong>{{ mb_strtoupper($focusZone) }}</strong>
                </header>
                <div class="ceet-console-map">
                    <div class="ceet-console-map-lines"></div>
                    <div class="ceet-console-map-marker">
                        <i></i>
                        <b>{{ $focusStation }}</b>
                    </div>
                    <div class="ceet-console-map-legend">
                        <span><i class="is-fault"></i>Zone en défaut</span>
                        <span><i></i>Poste source</span>
                    </div>
                </div>
            </article>
        </aside>
    </section>

    <section class="ceet-console-nodes">
        <header>
            <h2>État de l’infrastructure réseau — nœuds actifs</h2>
        </header>

        <div class="ceet-console-node-grid">
            @forelse ($networkNodes as $node)
                @php($state = $nodeState($node))
                <article class="ceet-console-node {{ $state['class'] }}">
                    <div>
                        <span class="material-symbols-outlined">{{ $state['icon'] }}</span>
                    </div>
                    <strong>{{ $node->nom }}</strong>
                    <em>{{ $state['label'] }}</em>
                    <small>{{ $state['detail'] }}</small>
                </article>
            @empty
                <article class="ceet-console-node is-maintenance">
                    <div><span class="material-symbols-outlined">domain_disabled</span></div>
                    <strong>Aucun nœud</strong>
                    <em>Catalogue vide</em>
                    <small>Ajoutez des départements actifs</small>
                </article>
            @endforelse
        </div>
    </section>
</main>

<footer class="ceet-console-footer">
    <div>
        <span>État système : {{ $systemState }}</span>
        <span>Dernière donnée : {{ $lastCheckAt }}</span>
    </div>
    <div>© {{ now()->year }} CEET — Console de conduite opérationnelle</div>
</footer>

@can('incidents.create')
    <a href="{{ route('incidents.create') }}" class="ceet-console-fab" aria-label="Déclarer un incident">
        <span class="material-symbols-outlined">add</span>
    </a>
@endcan
</div>
@endsection

@section('page_js')
@endsection
