@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('page_css')
    @vite([
        'resources/css/app.css',
        'resources/css/pages/dashboard-operator.css'
    ])
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();

    $userName = $currentUser?->name ?? 'Opérateur Terrain';
    $userEmail = $currentUser?->email ?? 'operateur@ceet.com';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = strtoupper($initials ?: 'OT');

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $myOpenIncidents = collect($myOpenIncidents ?? []);
    $recentIncidents = $myOpenIncidents->isNotEmpty()
        ? $myOpenIncidents
        : collect($recentIncidents ?? []);

    $assignedCount = (int) ($myTotalMonth ?? $recentIncidents->count());
    $openCount = (int) ($myTotalOpen ?? data_get($kpis ?? [], 'openCount', 0));
    $resolvedToday = (int) ($myResolvedToday ?? 0);

    $urgentCount = $recentIncidents
        ->filter(function ($incident) {
            $priorite = strtolower(optional($incident->priorite)->libelle ?? '');
            $niveau = optional($incident->priorite)->niveau;

            return str_contains($priorite, 'haute')
                || str_contains($priorite, 'critique')
                || (string) $niveau === '1';
        })
        ->count();

    $roleName = 'OPÉRATEUR N2';

    if ($currentUser && method_exists($currentUser, 'getRoleNames')) {
        $roleName = strtoupper($currentUser->getRoleNames()->first() ?? 'OPÉRATEUR N2');
    }

    $progressValue = min(100, max(8, $openCount * 12));
    $canCreateIncident = $currentUser?->can('incidents.create') ?? false;
    $canViewReports = $currentUser?->can('reporting.view') ?? false;
    $canViewHistory = ($currentUser?->isAdmin() ?? false) || ($currentUser?->isSuperviseur() ?? false);
    $operatorUnreadNotificationsCount = (int) ($currentUser?->unreadNotifications()->count() ?? 0);
@endphp

@section('content')
<div class="ceet-operator-dashboard-page" data-operator-dashboard>
<main class="ceet-operator-main">
    <section class="ceet-operator-page-header">
        <div>
            <h2>Tableau de bord</h2>
            <p>Gestion temps réel des incidents réseau électrique.</p>
        </div>

        <div class="ceet-operator-system-state">
            <span></span>
            Système opérationnel
        </div>
    </section>

    <section class="ceet-operator-kpi-grid" aria-label="Indicateurs opérateur">
        <article class="ceet-operator-kpi-card">
            <div class="ceet-operator-kpi-head">
                <span>Incidents affectés</span>
                <span class="material-symbols-outlined" aria-hidden="true">assignment_ind</span>
            </div>

            <strong>{{ str_pad((string) $assignedCount, 2, '0', STR_PAD_LEFT) }}</strong>
            <p>{{ $resolvedToday }} résolu(s) aujourd’hui</p>
        </article>

        <article class="ceet-operator-kpi-card">
            <div class="ceet-operator-kpi-head">
                <span>En cours</span>
                <span class="material-symbols-outlined" aria-hidden="true">work_history</span>
            </div>

            <strong>{{ str_pad((string) $openCount, 2, '0', STR_PAD_LEFT) }}</strong>

            <div class="ceet-operator-progress" aria-label="Progression incidents en cours">
                <span style="width: {{ $progressValue }}%"></span>
            </div>
        </article>

        <article class="ceet-operator-kpi-card is-urgent">
            <div class="ceet-operator-kpi-head">
                <span>À résoudre urgent</span>
                <span class="material-symbols-outlined" aria-hidden="true">priority_high</span>
            </div>

            <strong>{{ str_pad((string) $urgentCount, 2, '0', STR_PAD_LEFT) }}</strong>
            <p>Interventions critiques requises</p>
        </article>
    </section>

    <section class="ceet-operator-content-grid">
        <article class="ceet-operator-panel ceet-operator-table-panel">
            <header class="ceet-operator-panel-header">
                <h3>Mes derniers incidents</h3>
                <a href="{{ $safeRoute('incidents.mine', [], '/mes-incidents') }}">Voir tout</a>
            </header>

            <div class="ceet-operator-table-wrap">
                <table class="ceet-operator-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Incident</th>
                            <th>Localisation</th>
                            <th>Statut</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recentIncidents->take(5) as $incident)
                            @php
                                $incidentCode = $incident->code_incident ?: 'INC-' . $incident->id;
                                $incidentTitle = $incident->titre
                                    ?: optional($incident->typeIncident)->libelle
                                    ?: 'Incident sans titre';

                                $location = $incident->localisation
                                    ?: optional($incident->departement)->nom
                                    ?: 'N/A';

                                $statusLabel = optional($incident->status)->libelle ?? 'N/A';
                                $priorityLabel = strtolower(optional($incident->priorite)->libelle ?? '');

                                $statusClass = str_contains(strtolower($statusLabel), 'cours')
                                    ? 'is-progress'
                                    : (str_contains(strtolower($statusLabel), 'résolu') || str_contains(strtolower($statusLabel), 'resolu')
                                        ? 'is-done'
                                        : 'is-critical');

                                $incidentUrl = Route::has('incidents.show')
                                    ? route('incidents.show', $incident)
                                    : '#';
                            @endphp

                            <tr>
                                <td>
                                    <strong>{{ str_starts_with($incidentCode, '#') ? $incidentCode : '#' . $incidentCode }}</strong>
                                </td>

                                <td>{{ $incidentTitle }}</td>

                                <td>{{ $location }}</td>

                                <td>
                                    <span class="ceet-operator-status {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="is-right">
                                    <a href="{{ $incidentUrl }}" class="ceet-operator-row-action" aria-label="Voir l'incident">
                                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ceet-operator-empty-row">
                                    Aucun incident affecté pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="ceet-operator-panel ceet-operator-actions-panel">
            <header class="ceet-operator-panel-header is-stacked">
                <h3>Actions rapides</h3>
            </header>

            <div class="ceet-operator-quick-actions">
                <a href="{{ $safeRoute('incidents.mine', [], '/mes-incidents') }}" class="ceet-operator-action-btn is-primary">
                    <span class="material-symbols-outlined" aria-hidden="true">assignment_ind</span>
                    <strong>Mes incidents</strong>
                </a>

                <a href="{{ $safeRoute('incidents.en-cours', [], '/incidents/en-cours') }}" class="ceet-operator-action-btn">
                    <span class="material-symbols-outlined" aria-hidden="true">work_history</span>
                    <strong>Incidents en cours</strong>
                </a>

                @if($canCreateIncident)
                    <a href="{{ $safeRoute('incidents.create', [], '/incidents/create') }}" class="ceet-operator-action-btn">
                        <span class="material-symbols-outlined" aria-hidden="true">add_alert</span>
                        <strong>Déclarer un incident</strong>
                    </a>
                @endif

                @if($canViewReports)
                    <a href="{{ $safeRoute('reports.index', [], '/reports') }}" class="ceet-operator-action-btn">
                        <span class="material-symbols-outlined" aria-hidden="true">note_add</span>
                        <strong>Rapports</strong>
                    </a>
                @endif

                @if($canViewHistory)
                    <a href="{{ $safeRoute('historique.index', [], '/historique') }}" class="ceet-operator-action-btn">
                        <span class="material-symbols-outlined" aria-hidden="true">history</span>
                        <strong>Historique complet</strong>
                    </a>
                @endif
            </div>

            <div class="ceet-operator-nearby">
                <span>Infrastructure à proximité</span>

                <div>
                    <p>Dernière synchronisation</p>
                    <strong>{{ $lastCheckAt ?? now()->format('H:i:s') }}</strong>
                </div>
            </div>
        </aside>
    </section>
</main>

@if($canCreateIncident)
    <a href="{{ $safeRoute('incidents.create', [], '/incidents/create') }}" class="ceet-operator-fab" aria-label="Déclarer un incident">
        <span class="material-symbols-outlined" aria-hidden="true">support_agent</span>
    </a>
@endif
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/app.js',
        'resources/js/pages/operator-dashboard.js'
    ])
@endsection
