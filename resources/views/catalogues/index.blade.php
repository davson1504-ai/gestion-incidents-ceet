@php
    $currentUser = auth()->user();
    

$userName = $currentUser?->name ?? 'Administrateur';
    $userEmail = $currentUser?->email ?? 'admin@ceet.tg';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames')
        ? ($currentUser->getRoleNames()->first() ?: 'Administrateur')
        : 'Administrateur';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    $departements = collect($departements ?? []);
    $types = collect($types ?? []);
    $causes = collect($causes ?? []);
    $statuts = collect($statuts ?? []);
    $priorites = collect($priorites ?? []);

    $observationsCount = 0;
    $lastAuditUser = 'Admin_Tech.';
    $lastAuditDate = null;

    try {
        if (class_exists(\App\Models\IncidentReport::class) && \Illuminate\Support\Facades\Schema::hasTable('incident_reports')) {
            $observationsCount = \App\Models\IncidentReport::query()
                ->whereNotNull('observations')
                ->where('observations', '<>', '')
                ->distinct()
                ->count('observations');
        }

        if (class_exists(\App\Models\Log::class) && \Illuminate\Support\Facades\Schema::hasTable('logs')) {
            $lastLog = \App\Models\Log::query()
                ->with('user')
                ->where('module', 'catalogues')
                ->latest('created_at')
                ->first();

            if ($lastLog) {
                $lastAuditUser = $lastLog->user?->name ?? 'Système';
                $lastAuditDate = $lastLog->created_at;
            }
        }
    } catch (\Throwable) {
        $observationsCount = 0;
    }

    $latestCatalogueDate = collect([
        $departements->max('updated_at'),
        $types->max('updated_at'),
        $causes->max('updated_at'),
        $statuts->max('updated_at'),
        $priorites->max('updated_at'),
        $lastAuditDate,
    ])->filter()->sortDesc()->first();

    $lastDisplayDate = $latestCatalogueDate
        ? \Illuminate\Support\Carbon::parse($latestCatalogueDate)->translatedFormat('d F Y')
        : 'aucune modification enregistrée';

    $catalogues = collect([
        [
            'title' => 'Départs électriques',
            'description' => 'Configuration de la topologie du réseau et des points de départ HT/BT.',
            'label' => 'Population',
            'count' => $departements->count(),
            'unit' => $departements->count() > 1 ? 'départs configurés' : 'départ configuré',
            'icon' => 'electric_bolt',
            'index' => route('catalogues.departements.index'),
            'create' => route('catalogues.departements.create'),
            'keywords' => 'departements départs electriques électrique réseau poste source transformateur',
        ],
        [
            'title' => 'Types d’incidents',
            'description' => 'Classification technique des pannes : court-circuit, surcharge, défaut réseau et autres incidents.',
            'label' => 'Catégories',
            'count' => $types->count(),
            'unit' => $types->count() > 1 ? 'types définis' : 'type défini',
            'icon' => 'category',
            'index' => route('catalogues.types.index'),
            'create' => route('catalogues.types.create'),
            'keywords' => 'types incidents panne court-circuit surcharge défaut catégorie classification',
        ],
        [
            'title' => 'Causes probables',
            'description' => 'Arbre des causes standardisé pour faciliter le diagnostic et le reporting.',
            'label' => 'Éléments',
            'count' => $causes->count(),
            'unit' => $causes->count() > 1 ? 'causes répertoriées' : 'cause répertoriée',
            'icon' => 'help',
            'index' => route('catalogues.causes.index'),
            'create' => route('catalogues.causes.create'),
            'keywords' => 'causes probables diagnostic arbre cause reporting origine panne',
        ],
        [
            'title' => 'Priorités',
            'description' => 'Niveaux de criticité et délais d’intervention associés aux SLA.',
            'label' => 'Niveaux',
            'count' => $priorites->count(),
            'unit' => $priorites->count() > 1 ? 'niveaux de priorité' : 'niveau de priorité',
            'icon' => 'priority_high',
            'index' => route('catalogues.priorites.index'),
            'create' => route('catalogues.priorites.create'),
            'keywords' => 'priorités priorité criticité sla urgence niveau intervention',
        ],
        [
            'title' => 'Statuts',
            'description' => 'Workflow des incidents, de l’ouverture à la clôture technique.',
            'label' => 'Workflow',
            'count' => $statuts->count(),
            'unit' => $statuts->count() > 1 ? 'états configurés' : 'état configuré',
            'icon' => 'inventory',
            'index' => route('catalogues.statuts.index'),
            'create' => route('catalogues.statuts.create'),
            'keywords' => 'statuts status workflow ouverture fermeture clôture en cours état',
        ],
        [
            'title' => 'Observations types',
            'description' => 'Bibliothèque de commentaires et observations utilisés dans les rapports d’intervention.',
            'label' => 'Modèles',
            'count' => $observationsCount,
            'unit' => $observationsCount > 1 ? 'observations recensées' : 'observation recensée',
            'icon' => 'edit_note',
            'index' => route('reports.index'),
            'create' => null,
            'keywords' => 'observations types commentaires rapports intervention modèles notes',
        ],
    ]);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catalogues techniques - CEET Incidents</title>

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
<body class="ceet-admin-dashboard-page ceet-catalogues-page">
    <div class="ceet-admin-shell" data-admin-dashboard data-catalogues-page>
        <div class="ceet-dashboard-overlay" data-dashboard-overlay></div>

        <aside class="ceet-admin-sidebar ceet-catalogues-sidebar" data-dashboard-sidebar>
            <div class="ceet-admin-brand">
                <div class="ceet-admin-brand-logo">
                    <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                </div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Gestion électrique</p>
                </div>
            </div>

            <nav class="ceet-admin-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-admin-nav-link'])
            </nav>

            <div class="ceet-catalogues-sidebar-user">
                <span class="ceet-catalogues-sidebar-avatar">{{ $initials }}</span>
                <div>
                    <strong>{{ $roleName }}</strong>
                    <em>{{ $userEmail }}</em>
                </div>
            </div>
        </aside>

        <header class="ceet-admin-topbar ceet-catalogues-topbar">
            <button type="button" class="ceet-admin-menu-btn" data-dashboard-sidebar-toggle aria-label="Ouvrir le menu" aria-expanded="false">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <form action="{{ route('catalogues.index') }}" method="GET" class="ceet-admin-search ceet-catalogues-search">
                <span class="material-symbols-outlined">search</span>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Rechercher dans les catalogues..."
                    autocomplete="off"
                    data-catalogue-search
                >
            </form>

            <div class="ceet-admin-top-actions">
                <a href="{{ route('notifications.index') }}" class="ceet-admin-icon-btn" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="ceet-admin-icon-btn" aria-label="Aide et profil">
                    <span class="material-symbols-outlined">help_outline</span>
                </a>

                <div class="ceet-admin-top-divider"></div>

                <div class="ceet-admin-top-user">
                    <span>{{ $userName }}</span>
                    <div class="ceet-admin-avatar is-small">{{ $initials }}</div>
                </div>
            </div>
        </header>

        <main class="ceet-admin-main ceet-catalogues-main">
            <nav class="ceet-catalogues-breadcrumb" aria-label="Fil d’Ariane">
                <a href="{{ route('dashboard') }}">Administration</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <strong>Catalogues</strong>
            </nav>

            <section class="ceet-catalogues-heading">
                <div>
                    <h2>Catalogues techniques</h2>
                    <p>
                        Configuration centralisée du système de gestion des incidents électriques.
                        Gérez les référentiels métier pour garantir l’intégrité des données opérationnelles.
                    </p>
                </div>

                @can('catalogues.manage')
                    <div class="ceet-catalogues-create" data-catalogue-create-menu>
                        <button type="button" aria-expanded="false" data-catalogue-create-toggle>
                            <span class="material-symbols-outlined">add</span>
                            Nouveau catalogue
                        </button>

                        <div class="ceet-catalogues-create-menu" data-catalogue-create-panel hidden>
                            <a href="{{ route('catalogues.departements.create') }}">Nouveau départ électrique</a>
                            <a href="{{ route('catalogues.types.create') }}">Nouveau type d’incident</a>
                            <a href="{{ route('catalogues.causes.create') }}">Nouvelle cause probable</a>
                            <a href="{{ route('catalogues.priorites.create') }}">Nouveau niveau de priorité</a>
                            <a href="{{ route('catalogues.statuts.create') }}">Nouveau statut</a>
                        </div>
                    </div>
                @endcan
            </section>

            <section class="ceet-catalogues-grid" aria-label="Liste des catalogues techniques">
                @foreach ($catalogues as $catalogue)
                    <article class="ceet-catalogue-card" data-catalogue-card data-catalogue-keywords="{{ $catalogue['keywords'] }} {{ $catalogue['title'] }} {{ $catalogue['description'] }}">
                        <a href="{{ $catalogue['index'] }}" aria-label="Ouvrir {{ $catalogue['title'] }}">
                            <header>
                                <span class="ceet-catalogue-icon">
                                    <span class="material-symbols-outlined">{{ $catalogue['icon'] }}</span>
                                </span>

                                <span class="material-symbols-outlined ceet-catalogue-arrow">arrow_forward</span>
                            </header>

                            <h3>{{ $catalogue['title'] }}</h3>
                            <p>{{ $catalogue['description'] }}</p>

                            <footer>
                                <span>{{ $catalogue['label'] }}</span>
                                <strong>{{ number_format($catalogue['count'], 0, ',', ' ') }} {{ $catalogue['unit'] }}</strong>
                            </footer>
                        </a>
                    </article>
                @endforeach
            </section>

            <p class="ceet-catalogues-empty" data-catalogue-empty hidden>
                Aucun catalogue ne correspond à votre recherche.
            </p>

            <section class="ceet-catalogues-footer-panel">
                <div>
                    <span class="material-symbols-outlined">info</span>
                    <p>
                        Dernière modification globale effectuée le
                        <strong>{{ $lastDisplayDate }}</strong>
                        par <strong>{{ $lastAuditUser }}</strong>.
                    </p>
                </div>

                <nav aria-label="Actions catalogue">
                    @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                    <a href="{{ route('catalogues.index') }}" data-catalogue-print>Exporter les schémas</a>
                    @endunless
                    <a href="{{ route('historique.index') }}">Journal d’audit</a>
                </nav>
            </section>
        </main>
    </div>
</body>
</html>