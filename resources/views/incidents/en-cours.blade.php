@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();

    

    $isAdmin = $isAdmin ?? ($currentUser?->isAdmin() ?? false);
    $isSupervisor = $isSupervisor ?? ($currentUser?->isSuperviseur() ?? false);
$userName = $currentUser?->name ?? 'Jean Dupont';
    $userEmail = $currentUser?->email ?? 'admin@ceet.tg';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = strtoupper($initials ?: 'JD');

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $incidentItems = method_exists($incidents ?? null, 'getCollection')
        ? $incidents->getCollection()
        : collect($incidents ?? []);

    $totalCurrent = method_exists($incidents ?? null, 'total')
        ? $incidents->total()
        : $incidentItems->count();

    $criticalCount = $incidentItems->filter(function ($incident) {
        $priority = mb_strtolower(optional($incident->priorite)->libelle ?? '');
        $level = optional($incident->priorite)->niveau;

        return str_contains($priority, 'critique')
            || str_contains($priority, 'haute')
            || (string) $level === '1';
    })->count();

    $mobilizedTeams = $incidentItems
        ->map(fn ($incident) => $incident->responsable_id ?? $incident->operateur_id ?? null)
        ->filter()
        ->unique()
        ->count();

    $durationValues = $incidentItems->map(function ($incident) {
        if (! is_null($incident->duree_minutes ?? null)) {
            return (int) $incident->duree_minutes;
        }

        if ($incident->date_debut) {
            return $incident->date_debut->diffInMinutes(now());
        }

        return null;
    })->filter(fn ($value) => ! is_null($value));

    $avgDurationMinutes = $durationValues->count() > 0
        ? round($durationValues->avg())
        : 0;

    $avgDurationLabel = $avgDurationMinutes > 0
        ? floor($avgDurationMinutes / 60) . '.' . str_pad((string) round(($avgDurationMinutes % 60) / 6), 1, '0') . 'h'
        : '0h';

    $roleName = 'ADMINISTRATOR';

    if ($currentUser && method_exists($currentUser, 'getRoleNames')) {
        $roleName = strtoupper($currentUser->getRoleNames()->first() ?? 'ADMINISTRATOR');
    }

    $isOperator = $currentUser?->isOperateur() ?? false;
    $canCreateIncident = $currentUser?->can('incidents.create') ?? false;
    $canViewUsers = $currentUser?->can('users.view') ?? false;
    $canViewCatalogues = $currentUser?->can('catalogues.view') ?? false;
    $canViewReports = $currentUser?->can('reporting.view') ?? false;
    $canViewSystem = ($currentUser?->isAdmin() ?? false) || ($currentUser?->isSuperviseur() ?? false);

    $activeView = request('vue', 'all');

    $quickViews = [
        'all' => 'Tous les incidents',
        'high' => 'Haute Priorité',
        'mine' => 'Mes affectations',
        'north' => 'Zone Nord',
        'hta' => 'HTA / BT',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Incidents en cours - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/incidents-en-cours.css',
        'resources/js/app.js',
        'resources/js/pages/incidents-en-cours.js'
    ])
</head>

<body class="ceet-current-page">
    <div class="ceet-current-shell" data-current-page>
        <div class="ceet-current-overlay" data-current-overlay></div>

        <aside class="ceet-current-sidebar" data-current-sidebar>
            <div class="ceet-current-brand">
                <div class="ceet-current-brand-logo">
                    <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                </div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Electrical Management</p>
                </div>
            </div>

            <nav class="ceet-current-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-current-nav-link'])
            </nav>

            <div class="ceet-current-sidebar-user">
                <div class="ceet-current-sidebar-user-main">
                    <div class="ceet-current-avatar">{{ $initials }}</div>

                    <div>
                        <strong>{{ $userName }}</strong>
                        <span>{{ $roleName }}</span>
                    </div>
                </div>

                <form action="{{ $safeRoute('logout', [], '#') }}" method="POST" class="ceet-current-logout-form">
                    @csrf

                    <button type="submit" class="ceet-current-logout-button">
                        <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <header class="ceet-current-topbar">
            <button type="button" class="ceet-current-menu-btn" data-current-sidebar-toggle aria-label="Ouvrir le menu">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>

            <form action="{{ $safeRoute('incidents.en-cours', [], '/incidents/en-cours') }}" method="GET" class="ceet-current-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? request('q') }}"
                    placeholder="Rechercher un incident, un code ou un départ..."
                    autocomplete="off"
                >
            </form>

            <div class="ceet-current-top-actions">
                <a href="{{ $safeRoute('notifications.index', [], '#') }}" class="ceet-current-icon-btn" aria-label="Notifications">
                    <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                    <span class="ceet-current-notification-dot"></span>
                </a>

                <a href="{{ $safeRoute('profile.edit', [], '/profile') }}" class="ceet-current-icon-btn" aria-label="Aide">
                    <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
                </a>

                <div class="ceet-current-top-divider"></div>

                <div class="ceet-current-top-user">
                    <span>{{ $userName }}</span>
                    <div class="ceet-current-avatar is-small">{{ $initials }}</div>
                </div>
            </div>
        </header>

        <main class="ceet-current-main">
            <section class="ceet-current-page-header">
                <div>
                    <nav class="ceet-current-breadcrumb" aria-label="Fil d'Ariane">
                        <span>Incidents</span>
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                        <strong>Opérationnel temps réel</strong>
                    </nav>

                    <h2>Incidents en cours</h2>
                </div>

                <div class="ceet-current-header-actions">
                    <button type="button" class="ceet-current-secondary-btn" data-current-filter-toggle>
                        <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                        Filtres avancés
                    </button>
                    @if($canCreateIncident)
                        <a href="{{ $safeRoute('incidents.create', [], '/incidents/create') }}" class="ceet-current-primary-btn">
                            <span class="material-symbols-outlined" aria-hidden="true">add</span>
                            Nouvel Incident
                        </a>
                    @endif
                </div>
            </section>

            @if (session('success'))
                <div class="ceet-current-alert is-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="ceet-current-alert is-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <section class="ceet-current-stats-grid" aria-label="Indicateurs temps réel">
                <article class="ceet-current-stat-card">
                    <p>Total en cours</p>

                    <div>
                        <strong>{{ number_format($totalCurrent, 0, ',', ' ') }}</strong>
                        <span class="is-danger">
                            <span class="material-symbols-outlined" aria-hidden="true">trending_up</span>
                            +{{ min(9, max(0, $criticalCount - 1)) }}
                        </span>
                    </div>
                </article>

                <article class="ceet-current-stat-card is-critical">
                    <p>Priorité critique</p>

                    <div>
                        <strong>{{ str_pad((string) $criticalCount, 2, '0', STR_PAD_LEFT) }}</strong>
                        <span>Niveau 1</span>
                    </div>
                </article>

                <article class="ceet-current-stat-card">
                    <p>Moyenne résolution</p>

                    <div>
                        <strong>{{ $avgDurationLabel }}</strong>
                        <span class="is-positive">
                            <span class="material-symbols-outlined" aria-hidden="true">trending_down</span>
                            -12%
                        </span>
                    </div>
                </article>

                <article class="ceet-current-stat-card">
                    <p>Équipes mobilisées</p>

                    <div>
                        <strong>{{ number_format($mobilizedTeams, 0, ',', ' ') }}</strong>
                        <span>sur 15 disp.</span>
                    </div>
                </article>
            </section>

            <section class="ceet-current-filter-panel" data-current-filter-panel hidden>
                <form action="{{ $safeRoute('incidents.en-cours', [], '/incidents/en-cours') }}" method="GET" class="ceet-current-filters">
                    <div class="ceet-current-field is-large">
                        <label for="q">Recherche</label>
                        <input
                            id="q"
                            type="search"
                            name="q"
                            value="{{ $filters['q'] ?? request('q') }}"
                            placeholder="Code, départ, localisation..."
                        >
                    </div>

                    <div class="ceet-current-field">
                        <label for="priorite_id">Priorité</label>
                        <select id="priorite_id" name="priorite_id">
                            <option value="">Toutes</option>

                            @foreach ($priorites ?? [] as $priorite)
                                <option value="{{ $priorite->id }}" @selected((string) ($filters['priorite_id'] ?? request('priorite_id')) === (string) $priorite->id)>
                                    {{ $priorite->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ceet-current-field">
                        <label for="departement_id">Départ</label>
                        <select id="departement_id" name="departement_id">
                            <option value="">Tous</option>

                            @foreach ($departements ?? [] as $departement)
                                <option value="{{ $departement->id }}" @selected((string) ($filters['departement_id'] ?? request('departement_id')) === (string) $departement->id)>
                                    {{ $departement->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ceet-current-filter-actions">
                        <button type="submit" class="ceet-current-square-btn" aria-label="Appliquer les filtres">
                            <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                        </button>

                        <a href="{{ $safeRoute('incidents.en-cours', [], '/incidents/en-cours') }}" class="ceet-current-square-btn" aria-label="Réinitialiser">
                            <span class="material-symbols-outlined" aria-hidden="true">refresh</span>
                        </a>
                    </div>
                </form>
            </section>

            @unless($isOperator)
                <section class="ceet-current-views" aria-label="Vues rapides">
                    <span>Vues :</span>

                    @foreach ($quickViews as $viewKey => $viewLabel)
                        <a
                            href="{{ $safeRoute('incidents.en-cours', ['vue' => $viewKey], '/incidents/en-cours?vue=' . $viewKey) }}"
                            class="{{ $activeView === $viewKey ? 'is-active' : '' }}"
                            data-current-view
                        >
                            {{ $viewLabel }}
                        </a>
                    @endforeach
                </section>
            @endunless

            <section class="ceet-current-table-panel">
                <div class="ceet-current-table-wrap">
                    <table class="ceet-current-table">
                        <thead>
                            <tr>
                                <th class="is-center">Prio</th>
                                <th>Code</th>
                                <th>Départ / Zone</th>
                                <th>Statut</th>
                                <th>Ancienneté</th>
                                <th>Opérateur affecté</th>
                                <th class="is-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($incidents as $incident)
                                @php
                                    $code = $incident->code_incident ?: 'INC-' . $incident->id;
                                    $priorite = optional($incident->priorite)->libelle ?? 'N/A';
                                    $status = optional($incident->status)->libelle ?? 'N/A';

                                    $depart = optional($incident->departement)->nom
                                        ?: $incident->localisation
                                        ?: 'Départ non renseigné';

                                    $zone = $incident->localisation
                                        ?: optional($incident->typeIncident)->libelle
                                        ?: optional($incident->cause)->libelle
                                        ?: 'Zone non renseignée';

                                    $operatorName = optional($incident->responsable)->name
                                        ?? optional($incident->operateur)->name
                                        ?? null;

                                    $operatorInitials = $operatorName
                                        ? strtoupper(collect(preg_split('/\s+/', trim($operatorName)))->filter()->map(fn ($part) => substr($part, 0, 1))->take(2)->implode(''))
                                        : null;

                                    $ageMinutes = $incident->date_debut
                                        ? $incident->date_debut->diffInMinutes(now())
                                        : null;

                                    $ageLabel = $ageMinutes !== null
                                        ? floor($ageMinutes / 60) . 'h ' . str_pad((string) ($ageMinutes % 60), 2, '0', STR_PAD_LEFT) . 'm'
                                        : '--';

                                    $priorityLower = mb_strtolower($priorite);
                                    $statusLower = mb_strtolower($status);

                                    $priorityClass = str_contains($priorityLower, 'critique')
                                        ? 'is-critical'
                                        : (str_contains($priorityLower, 'haute')
                                            ? 'is-high'
                                            : (str_contains($priorityLower, 'moyenne')
                                                ? 'is-medium'
                                                : 'is-low'));

                                    $statusClass = str_contains($statusLower, 'déclenche') || str_contains($statusLower, 'declenche')
                                        ? 'is-triggered'
                                        : (str_contains($statusLower, 'route')
                                            ? 'is-route'
                                            : (str_contains($statusLower, 'diagnostic')
                                                ? 'is-diagnostic'
                                                : (str_contains($statusLower, 'réparation') || str_contains($statusLower, 'reparation')
                                                    ? 'is-repair'
                                                    : (str_contains($statusLower, 'critique') || str_contains($statusLower, 'alerte')
                                                        ? 'is-alert'
                                                        : 'is-waiting'))));

                                    $isCritical = $priorityClass === 'is-critical' || $statusClass === 'is-alert';
                                    $incidentUrl = Route::has('incidents.show') ? route('incidents.show', $incident) : '#';
                                    $editUrl = Route::has('incidents.edit') ? route('incidents.edit', $incident) : '#';
                                    $canTakeIncident = $currentUser?->can('take', $incident) ?? false;
                                    $canResolveIncident = $currentUser?->can('resolve', $incident) ?? false;
                                    $canReportIncident = $currentUser?->can('report', $incident) ?? false;
                                    $canUpdateIncident = $currentUser?->can('update', $incident) ?? false;
                                    $canIntervene = $canTakeIncident || $canResolveIncident || $canReportIncident;
                                @endphp

                                <tr class="{{ $isCritical ? 'is-critical-row' : '' }}">
                                    <td class="is-center">
                                        <span class="ceet-current-priority-dot {{ $priorityClass }}" title="{{ $priorite }}"></span>
                                    </td>

                                    <td>
                                        <strong>{{ $code }}</strong>
                                    </td>

                                    <td>
                                        <strong>{{ $depart }}</strong>
                                        <span>{{ $zone }}</span>
                                    </td>

                                    <td>
                                        <span class="ceet-current-status {{ $statusClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>

                                    <td>
                                        <strong class="{{ $isCritical ? 'is-danger-text' : '' }}">
                                            {{ $ageLabel }}
                                        </strong>
                                    </td>

                                    <td>
                                        @if ($operatorName)
                                            <div class="ceet-current-operator">
                                                <div>{{ $operatorInitials ?: 'OP' }}</div>
                                                <span>{{ $operatorName }}</span>
                                            </div>
                                        @else
                                            <em>Non affecté</em>
                                        @endif
                                    </td>

                                    <td class="is-right">
                                        @if ($isCritical && $canIntervene)
                                            <a href="{{ $incidentUrl }}" class="ceet-current-intervene-btn">
                                                Intervenir
                                            </a>
                                        @else
                                            <div class="ceet-current-row-actions">
                                                @if (! $operatorName && $canUpdateIncident)
                                                    <a href="{{ $editUrl }}" aria-label="Affecter un opérateur">
                                                        <span class="material-symbols-outlined" aria-hidden="true">person_add</span>
                                                    </a>
                                                @endif

                                                <a href="{{ $incidentUrl }}" aria-label="Voir l'incident">
                                                    <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="ceet-current-empty-row">
                                        Aucun incident en cours.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="ceet-current-table-footer">
                    <span>
                        @if (method_exists($incidents, 'firstItem') && $incidents->total() > 0)
                            Affichage de <strong>{{ $incidents->firstItem() }}-{{ $incidents->lastItem() }}</strong> incidents sur <strong>{{ $incidents->total() }}</strong>
                        @else
                            Affichage de <strong>{{ collect($incidents)->count() }}</strong> incidents
                        @endif
                    </span>

                    @if (method_exists($incidents, 'links'))
                        <div class="ceet-current-pagination">
                            {{ $incidents->links() }}
                        </div>
                    @endif
                </footer>
            </section>

            <section class="ceet-current-alert-grid">
                <article class="ceet-current-info-card is-danger">
                    <div>
                        <span class="material-symbols-outlined" aria-hidden="true">error_outline</span>
                    </div>

                    <div>
                        <h3>Alerte Météo - Zone Littorale</h3>
                        <p>Vents violents prévus entre 18h00 et 22h00. Risque accru de chutes d'arbres sur les lignes aériennes BT. Mobilisation des équipes d'astreinte requise.</p>
                    </div>
                </article>

                <article class="ceet-current-info-card">
                    <div>
                        <span class="material-symbols-outlined" aria-hidden="true">sync_alt</span>
                    </div>

                    <div>
                        <h3>Synchronisation SCADA</h3>
                        <p>Dernière mise à jour : il y a 45 secondes. 102 nœuds surveillés. Latence moyenne : 120ms. Système stable.</p>
                    </div>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
