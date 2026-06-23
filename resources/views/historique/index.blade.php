@extends('layouts.app')

@section('title', 'Historique')

@section('page_css')
    @vite('resources/css/pages/historique.css')
@endsection

@php
    $logs = $logs ?? collect();
    $filters = $filters ?? [];
    $modules = collect($modules ?? []);
    $users = collect($users ?? []);
    $recentActivity = collect($recentActivity ?? []);

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
        'login' => 'Connexion',
        'logout' => 'Déconnexion',
        'create' => 'Création',
        'creation' => 'Création',
        'create_incident' => 'Création',
        'update' => 'Modification',
        'modification' => 'Modification',
        'update_incident' => 'Modification',
        'delete' => 'Suppression',
        'delete_incident' => 'Suppression',
        'assign' => 'Affectation',
        'assignation' => 'Affectation',
        'affectation' => 'Affectation',
        'prise_en_charge' => 'Prise en charge',
        'resolution' => 'Résolution',
        'rapport' => 'Rapport d’intervention',
        'validation' => 'Validation',
        'close' => 'Clôture',
        'cloture' => 'Clôture',
        'close_incident' => 'Clôture',
        'create_user' => 'Création utilisateur',
        'update_user' => 'Modification utilisateur',
        'delete_user' => 'Suppression utilisateur',
        'export_report' => 'Export rapport',
        'update_catalogue' => 'Modification catalogue',
    ];

    $moduleLabel = function ($module) use ($moduleLabels) {
        $key = mb_strtolower((string) $module);

        return $moduleLabels[$key] ?? str($module ?: 'Système')->replace('_', ' ')->title();
    };

    $actionLabel = function ($action) use ($actionLabels) {
        $key = mb_strtolower((string) $action);

        return $actionLabels[$key] ?? str($action ?: 'Action système')->replace('_', ' ')->title();
    };

    $targetLabel = function ($log) {
        if ($log->incident) {
            return '#' . ($log->incident->code_incident ?: 'INC-' . str_pad((string) $log->incident->id, 5, '0', STR_PAD_LEFT));
        }

        if ($log->target_type && $log->target_id) {
            $target = class_basename($log->target_type);

            $target = match ($target) {
                'User' => 'Utilisateur',
                'Incident' => 'Incident',
                'Report' => 'Rapport',
                'Catalogue' => 'Catalogue',
                default => $target,
            };

            return $target . ' #' . $log->target_id;
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

@section('content')

    <section class="ceet-history-header">
        <div>
            <h2>Historique du système</h2>
            <p>Journal d'audit détaillé des opérations liées à l'infrastructure électrique.</p>
        </div>

        <div class="ceet-history-header-actions">
            <a href="{{ route('historique.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="ceet-history-btn is-light">
                <span class="material-symbols-outlined">download</span>
                Exporter CSV
            </a>

                    <form method="POST" action="{{ route('historique.clear') }}" class="ceet-history-clear-form" onsubmit="return confirm('Vider logiquement tout l’historique ? Les lignes seront masquées mais récupérables en base.');">
                        @csrf
                        <button type="submit" class="ceet-history-btn is-danger">
                            <span class="material-symbols-outlined" aria-hidden="true">delete_sweep</span>
                            Vider l’historique
                        </button>
                    </form>


            <a href="{{ route('historique.index') }}" class="ceet-history-btn is-dark">
                <span class="material-symbols-outlined">refresh</span>
                Actualiser
            </a>
        </div>
    </section>

    <form action="{{ route('historique.index') }}" method="GET" class="ceet-history-filter-card">
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
            <label for="module">Module</label>
            <select id="module" name="module">
                <option value="">Tous les modules</option>
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
                            $actionType = (string) $log->action_type;
                            $isError = str_contains(mb_strtolower($actionType), 'error') || str_contains(mb_strtolower($actionType), 'fail');
                        @endphp

                        <tr>
                            <td class="is-mono">{{ $log->action_date?->format('Y-m-d H:i:s') ?? '--' }}</td>

                            <td>
                                <div class="ceet-history-user-cell">
                                    <span>{{ $displayInitials }}</span>
                                    <strong>{{ $displayUser }}</strong>
                                </div>
                            </td>

                            <td>
                                <span class="ceet-history-action-chip {{ $isError ? 'is-error' : '' }}">
                                    {{ $actionLabel($actionType) }}
                                </span>
                            </td>

                            <td>{{ $moduleLabel('incidents') }}</td>

                            <td class="{{ $isError ? 'is-error-text' : '' }}">{{ $targetLabel($log) }}</td>

                            <td class="is-mono">{{ $log->ip_address ?? 'localhost' }}</td>

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
                                    <p>{{ $log->description ?: 'Aucune description.' }}</p>
                                    <pre>{{ $detailsLabel([
                                        'anciennes_valeurs' => $log->old_values,
                                        'nouvelles_valeurs' => $log->new_values,
                                    ]) }}</pre>
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

@endsection
