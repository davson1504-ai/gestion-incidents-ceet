@php
    $currentUser = auth()->user();
    $userName = $currentUser?->name ?? 'Administrateur';
    $userEmail = $currentUser?->email ?? 'system@ceet.tg';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames') ? ($currentUser->getRoleNames()->first() ?: 'Administrateur') : 'Administrateur';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    $logs = $logs ?? collect();
    $filters = $filters ?? [];
    $modules = collect($modules ?? []);
    $users = collect($users ?? []);
    $recentActivity = collect($recentActivity ?? []);
    $notificationCount = $currentUser?->unreadNotifications()->count() ?? 0;

    $moduleLabels = [
        'auth' => 'Authentification',
        'incidents' => 'Incidents',
        'catalogues' => 'Catalogues',
        'reporting' => 'Rapports',
        'users' => 'Utilisateurs',
        'system' => 'Système',
        'configuration' => 'Configuration',
    ];

    $actionLabels = [
        'login' => 'CONNEXION',
        'logout' => 'DÉCONNEXION',
        'create_incident' => 'CRÉATION_INCIDENT',
        'update_incident' => 'MODIFICATION_INCIDENT',
        'delete_incident' => 'SUPPRESSION_INCIDENT',
        'close_incident' => 'CLÔTURE_INCIDENT',
        'create_user' => 'CRÉATION_UTILISATEUR',
        'update_user' => 'MODIFICATION_UTILISATEUR',
        'delete_user' => 'SUPPRESSION_UTILISATEUR',
        'export_report' => 'EXPORT_RAPPORT',
        'update_catalogue' => 'MODIFICATION_CATALOGUE',
    ];

    $moduleLabel = function ($module) use ($moduleLabels) {
        $key = mb_strtolower((string) $module);

        return $moduleLabels[$key] ?? str($module ?: 'Système')->replace('_', ' ')->title();
    };

    $actionLabel = function ($action) use ($actionLabels) {
        $key = mb_strtolower((string) $action);

        return $actionLabels[$key] ?? str($action ?: 'ACTION_SYSTÈME')->replace(' ', '_')->upper();
    };

    $targetLabel = function ($log) {
        if ($log->incident) {
            return '#' . ($log->incident->code_incident ?: 'INC-' . str_pad((string) $log->incident->id, 5, '0', STR_PAD_LEFT));
        }

        $targetType = $log->target_type ?? null;
        $targetId = $targetId ?? null;

        if ($targetType && $targetId) {
            $target = class_basename($targetType);

            $target = match ($target) {
                'User' => 'Utilisateur',
                'Incident' => 'Incident',
                'Report' => 'Rapport',
                'Catalogue' => 'Catalogue',
                default => $target,
            };

            return $target . ' #' . $targetId;
        }

        return '--';
    };

    $detailsLabel = function ($details) {
        if (! $details) {
            return 'Aucun détail complémentaire.';
        }

        if (is_array($details)) {
            return json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return (string) $details;
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historique du système - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/admin-dashboard.css',
        'resources/js/pages/admin-dashboard.js'
    ])
</head>

<body class="ceet-admin-dashboard-page ceet-history-page">
    <div class="ceet-admin-shell" data-admin-dashboard data-history-page>
        <div class="ceet-dashboard-overlay" data-dashboard-overlay></div>

        <aside class="ceet-admin-sidebar ceet-history-sidebar ceet-role-sidebar" data-dashboard-sidebar>
            <div class="ceet-history-brand ceet-role-brand">
                <div class="ceet-history-brand-logo ceet-role-brand-logo"><img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET" loading="lazy"></div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Electrical Management</p>
                </div>
            </div>

            <nav class="ceet-history-nav ceet-sidebar-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-history-nav-link'])
            </nav>

            <div class="ceet-history-sidebar-user">
                <div class="ceet-history-sidebar-avatar">{{ $initials }}</div>

                <div class="ceet-history-sidebar-user-info">
                    <strong>{{ $userName }}</strong>
                    <span>{{ strtoupper($roleName) }}</span>
                    <small>{{ $userEmail }}</small>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="ceet-history-logout-form">
                @csrf

                <button type="submit" class="ceet-history-logout-button">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                    <span>Se déconnecter</span>
                </button>
            </form>
        </aside>

        <header class="ceet-admin-topbar ceet-history-topbar">
            <button type="button" class="ceet-admin-menu-btn ceet-history-menu-btn" data-dashboard-sidebar-toggle aria-label="Ouvrir le menu" aria-expanded="false">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <form action="{{ route('historique.index') }}" method="GET" class="ceet-admin-search ceet-history-search" data-ceet-filter-form>
                <span class="material-symbols-outlined">search</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Rechercher dans les logs..."
                    autocomplete="off"
                >
            </form>

            <div class="ceet-history-top-actions">
                <a href="{{ route('notifications.index') }}" class="ceet-history-icon-btn" aria-label="Notifications" data-ceet-notification-trigger>
                    <span class="material-symbols-outlined">notifications</span>

                    @if($notificationCount > 0)
                        <em>{{ $notificationCount > 99 ? '99+' : $notificationCount }}</em>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}" class="ceet-history-icon-btn" aria-label="Aide">
                    <span class="material-symbols-outlined">help_outline</span>
                </a>

                <div class="ceet-history-top-divider"></div>

                <a href="{{ route('profile.edit') }}" class="ceet-history-top-user" data-ceet-link>
                    <strong>{{ $userName }}</strong>
                    <span>{{ $initials }}</span>
                </a>
            </div>
        </header>


        <main class="ceet-admin-main ceet-history-main">
            <section class="ceet-history-header">
                <div>
                    <h2>Historique du système</h2>
                    <p>Journal d’audit détaillé des opérations liées à l’infrastructure électrique.</p>
                </div>

                <div class="ceet-history-header-actions">
                    <a href="{{ route('historique.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="ceet-history-btn is-light">
                        <span class="material-symbols-outlined">download</span>
                        Exporter CSV
                    </a>

                    <a href="{{ route('historique.index') }}" class="ceet-history-btn is-dark" data-ceet-link>
                        <span class="material-symbols-outlined">refresh</span>
                        Actualiser
                    </a>
                </div>
            </section>

            <form action="{{ route('historique.index') }}" method="GET" class="ceet-history-filter-card" data-ceet-filter-form>
                <div class="ceet-history-filter-field">
                    <label for="user_id">Utilisateur</label>
                    <select id="user_id" name="user_id">
                        <option value="">Tous les utilisateurs</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ceet-history-filter-field">
                    <label for="date_from">Période</label>
                    <div class="ceet-history-date-range">
                        <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                        <span>—</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                </div>

                <div class="ceet-history-filter-field">
                    <label for="module">Action</label>
                    <select id="module" name="module">
                        <option value="">Toutes les actions</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>
                                {{ $moduleLabel($module) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">

                <button type="submit" class="ceet-history-apply-btn">Appliquer les filtres</button>

                <a href="{{ route('historique.index') }}" class="ceet-history-reset-btn" aria-label="Réinitialiser les filtres">
                    <span class="material-symbols-outlined">filter_alt_off</span>
                </a>
            </form>

            <section class="ceet-history-table-card">
                <div class="ceet-history-table-wrap">
                    <table class="ceet-history-table">
                        <thead>
                            <tr>
                                <th>Date & heure</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Cible</th>
                                <th>Adresse IP</th>
                                <th>Détails</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($logs as $log)
                                @php
                                    $displayUser = $log->user?->name ?? 'Système';
                                    $displayInitials = collect(preg_split('/\s+/', trim($displayUser)))
                                        ->filter()
                                        ->map(fn ($part) => mb_substr($part, 0, 1))
                                        ->take(2)
                                        ->implode('');

                                    $displayInitials = mb_strtoupper($displayInitials ?: 'SYS');
                                    $displayAction = $log->action_type ?? $log->action ?? 'action';
                                    $displayModule = $log->module ?? 'incidents';
                                    $displayDate = $log->action_date ?? $log->created_at ?? null;
                                    $displayIp = $log->ip_address ?? 'localhost';
                                    $displayDetails = $log->description ?? $log->details ?? null;
                                    $displayUserAgent = $log->user_agent ?? null;
                                    $isError = str_contains(mb_strtolower((string) $displayAction), 'error') || str_contains(mb_strtolower((string) $displayAction), 'fail');
                                @endphp

                                <tr>
                                    <td class="is-mono">{{ optional($displayDate)->format('Y-m-d H:i:s') ?? '--' }}</td>

                                    <td>
                                        <div class="ceet-history-user-cell">
                                            <span>{{ $displayInitials }}</span>
                                            <strong>{{ $displayUser }}</strong>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="ceet-history-action-chip {{ $isError ? 'is-error' : '' }}">
                                            {{ $actionLabel($displayAction) }}
                                        </span>
                                    </td>

                                    <td>{{ $moduleLabel($displayModule) }}</td>

                                    <td class="{{ $isError ? 'is-error-text' : '' }}">{{ $targetLabel($log) }}</td>

                                    <td class="is-mono">{{ $displayIp }}</td>

                                    <td>
                                        <button
                                            type="button"
                                            class="ceet-history-detail-btn"
                                            data-history-detail-toggle="history-detail-{{ $log->id }}"
                                            aria-label="Afficher les détails du log"
                                        >
                                            <span class="material-symbols-outlined">info</span>
                                        </button>
                                    </td>
                                </tr>

                                <tr id="history-detail-{{ $log->id }}" class="ceet-history-detail-row" hidden>
                                    <td colspan="7">
                                        <div>
                                            <strong>Détails techniques</strong>
                                            <pre>{{ $detailsLabel($displayDetails) }}</pre>
                                            <small>
                                                Agent utilisateur :
                                                {{ $displayUserAgent ?: 'Non renseigné' }}
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="ceet-history-empty">
                                        Aucun log ne correspond aux filtres appliqués.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="ceet-history-pagination">
                    <span>
                        Affichage de {{ $logs->firstItem() ?? 0 }} à {{ $logs->lastItem() ?? 0 }}
                        sur {{ number_format($logs->total(), 0, ',', ' ') }} entrées
                    </span>

                    {{ $logs->links() }}
                </footer>
            </section>

            <section class="ceet-history-bottom-grid">
                <article class="ceet-history-status-card">
                    <span>État du journal</span>
                    <strong>{{ number_format($journalAvailability ?? 0, 1, ',', ' ') }}%</strong>
                    <em>Disponibilité</em>
                    <div></div>
                    <p>
                        Dernière activité :
                        {{ $lastLog?->created_at ? $lastLog->created_at->diffForHumans() : 'aucune donnée enregistrée' }}.
                    </p>
                </article>

                <article class="ceet-history-chart-card">
                    <header>Activité récente du journal</header>

                    <div class="ceet-history-chart">
                        @foreach ($recentActivity as $activity)
                            <div>
                                <span style="height: {{ $activity['height'] }}%"></span>
                                <strong>{{ $activity['label'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>
        </main>
    </div>
</body>
</html>