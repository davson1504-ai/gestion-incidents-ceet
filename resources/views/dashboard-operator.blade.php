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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tableau de bord - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/operator-dashboard.css',
        'resources/js/app.js',
        'resources/js/pages/operator-dashboard.js'
    ])
</head>

<body class="ceet-operator-dashboard-page">
    <div class="ceet-operator-shell" data-operator-dashboard>
        <div class="ceet-operator-overlay" data-operator-overlay></div>

        <aside class="ceet-operator-sidebar" data-operator-sidebar>
            <div class="ceet-operator-brand">
                <div class="ceet-operator-brand-logo">
                    <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                </div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Electrical Management</p>
                </div>
            </div>

            <nav class="ceet-operator-nav" aria-label="Navigation opérateur">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-operator-nav-link'])
            </nav>

            <div class="ceet-operator-sidebar-user">
                <div class="ceet-operator-sidebar-user-main">
                    <div class="ceet-operator-avatar">{{ $initials }}</div>

                    <div>
                        <strong>{{ $userName }}</strong>
                        <span>{{ $roleName }}</span>
                    </div>
                </div>

                <form action="{{ $safeRoute('logout', [], '#') }}" method="POST" class="ceet-operator-logout-form">
                    @csrf

                    <button type="submit" class="ceet-operator-logout-button">
                        <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <header class="ceet-operator-topbar">
            <button type="button" class="ceet-operator-menu-btn" data-operator-sidebar-toggle aria-label="Ouvrir le menu">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>

            <form action="{{ $safeRoute('incidents.mine', [], '/mes-incidents') }}" method="GET" class="ceet-operator-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input
                    type="search"
                    name="q"
                    placeholder="Rechercher un incident, un ID ou un technicien..."
                    autocomplete="off"
                >
            </form>

            <div class="ceet-operator-top-actions">
                <div class="ceet-operator-notification-wrap"
                    data-operator-notifications
                    data-notifications-url="{{ $safeRoute('notifications.index', [], '/notifications') }}"
                    data-read-all-url="{{ $safeRoute('notifications.read-all', [], '/notifications/read-all') }}"
                    data-read-url-template="{{ url('/notifications/__ID__/read') }}">
                    <button type="button"
                        class="ceet-operator-icon-btn ceet-operator-notification-trigger"
                        aria-label="Notifications"
                        aria-expanded="false"
                        data-notifications-toggle>
                        <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                        <span class="ceet-operator-notification-badge" data-notification-badge @if($operatorUnreadNotificationsCount < 1) hidden @endif>{{ $operatorUnreadNotificationsCount > 99 ? '99+' : $operatorUnreadNotificationsCount }}</span>
                    </button>

                    <div class="ceet-operator-notification-panel" data-notification-panel hidden>
                        <div class="ceet-operator-notification-header">
                            <div>
                                <strong>Notifications</strong>
                                <span data-notification-summary>{{ $operatorUnreadNotificationsCount }} non lue(s)</span>
                            </div>

                            <button type="button" data-notifications-read-all>Tout lire</button>
                        </div>

                        <div class="ceet-operator-notification-list" data-notification-list>
                            <div class="ceet-operator-notification-empty">Chargement des notifications...</div>
                        </div>
                    </div>
                </div>

                <a href="{{ $safeRoute('profile.edit', [], '/profile') }}" class="ceet-operator-icon-btn" aria-label="Profil">
                    <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
                </a>

                <div class="ceet-operator-top-divider"></div>

                <div class="ceet-operator-top-user">
                    <span>{{ $userName }}</span>
                    <div class="ceet-operator-avatar is-small">{{ $initials }}</div>
                </div>
            </div>
        </header>

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
</body>
</html>