@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrator'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrator';
    $filters = array_merge(['q' => '', 'zone' => ''], $filters ?? []);
    $zones = $zones ?? collect();
    $stats = array_merge([
        'totalCount' => $departements->total(),
        'activeCount' => $departements->where('is_active', true)->count(),
        'zoneCount' => 0,
        'totalPowerMw' => 0,
    ], $stats ?? []);
    $from = $departements->firstItem() ?? 0;
    $to = $departements->lastItem() ?? 0;
    $total = $departements->total();
    $lastUpdated = optional($departements->getCollection()->max('updated_at'))?->format("Aujourd'hui, H:i") ?? now()->format("Aujourd'hui, H:i");
    $htShare = $stats['totalCount'] > 0 ? (int) round(($stats['activeCount'] / max(1, $stats['totalCount'])) * 100) : 0;

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ['label' => 'Incidents', 'icon' => 'bolt', 'route' => route('incidents.index'), 'active' => request()->routeIs('incidents.*', 'incidents.mine')],
        ['label' => 'Users', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'System Status', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')],
        ['label' => 'Catalogs', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Départs Électriques - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-catalog-page">
    <aside class="ceet-catalog-sidebar">
        <div class="ceet-catalog-brand">
            <h1>CEET Incidents</h1>
            <p>Electrical Management</p>
        </div>

        <nav class="ceet-catalog-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-catalog-nav-link'])
            </nav>

        <a class="ceet-catalog-sidebar-user" href="{{ route('profile.edit') }}">
            <div class="ceet-catalog-avatar">{{ $initials }}</div>
            <div>
                <strong>{{ $roleName }}</strong>
                <span>View Profile</span>
            </div>
        </a>
    </aside>

    <header class="ceet-catalog-topbar">
        <div class="ceet-catalog-breadcrumb">
            <span>Catalogs</span>
            <span aria-hidden="true">›</span>
            <strong>Départs Électriques</strong>
        </div>

        <div class="ceet-catalog-top-actions">
            <button type="button" class="ceet-catalog-icon-btn" aria-label="Notifications">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <button type="button" class="ceet-catalog-icon-btn" aria-label="Aide">
                <span class="material-symbols-outlined" aria-hidden="true">help</span>
            </button>
        </div>
    </header>

    <main class="ceet-catalog-main">
        <section class="ceet-catalog-page-head">
            <div>
                <h2>Départs Électriques</h2>
                <p>Gestion du catalogue des départs du réseau national.</p>
            </div>

            @can('catalogues.manage')
                <a href="{{ route('catalogues.departements.create') }}" class="ceet-catalog-primary-btn">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Nouveau Départ
                </a>
            @endcan
        </section>

        @if(session('success'))
            <div class="ceet-alert ceet-alert-success" role="status">{{ session('success') }}</div>
        @endif

        <section class="ceet-catalog-filter-card" aria-label="Filtres départs">
            <form method="GET" action="{{ route('catalogues.departements.index') }}" class="ceet-catalog-filters">
                <label class="ceet-catalog-search-field">
                    <span>Recherche</span>
                    <span class="ceet-catalog-search-box">
                        <span class="material-symbols-outlined" aria-hidden="true">search</span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Rechercher par code ou nom...">
                    </span>
                </label>

                <label>
                    <span>Zone géographique</span>
                    <select name="zone">
                        <option value="">Toutes les zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone }}" @selected($filters['zone'] === $zone)>{{ $zone }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit" class="ceet-catalog-filter-btn">
                    <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                    Filtres Avancés
                </button>
            </form>
        </section>

        <section class="ceet-catalog-table-card">
            <div class="ceet-catalog-table-wrap">
                <table class="ceet-catalog-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom du départ</th>
                            <th>Zone</th>
                            <th>Tension</th>
                            <th>Statut</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departements as $departement)
                            @php
                                $tension = $departement->charge_unite ?: 'HT (20kV)';
                                $isBt = Str::contains(Str::lower($tension), ['bt', '400']);
                            @endphp
                            <tr>
                                <td><strong>{{ $departement->code }}</strong></td>
                                <td>{{ $departement->nom }}</td>
                                <td>{{ $departement->zone ?: 'Non renseignée' }}</td>
                                <td>
                                    <span class="ceet-catalog-voltage {{ $isBt ? 'is-bt' : '' }}">{{ $tension }}</span>
                                </td>
                                <td>
                                    <span class="ceet-catalog-status {{ $departement->is_active ? 'is-active' : 'is-inactive' }}">
                                        <i aria-hidden="true"></i>
                                        {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="is-right">
                                    <div class="ceet-catalog-row-actions">
                                        @can('catalogues.manage')
                                            <a href="{{ route('catalogues.departements.edit', $departement) }}" aria-label="Modifier {{ $departement->code }}">
                                                <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('catalogues.departements.destroy', $departement) }}" onsubmit="return confirm('Supprimer ce départ ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Supprimer {{ $departement->code }}">
                                                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="ceet-catalog-muted-action">Lecture</span>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ceet-catalog-empty">Aucun départ trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="ceet-catalog-pagination">
                <span>Affichage de {{ $from }} à {{ $to }} sur {{ $total }} départs</span>

                @if($departements->lastPage() > 1)
                    <nav aria-label="Pagination départs">
                        @if($departements->onFirstPage())
                            <button type="button" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></button>
                        @else
                            <a href="{{ $departements->previousPageUrl() }}"><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></a>
                        @endif

                        @foreach(range(1, $departements->lastPage()) as $page)
                            @if($page <= 3 || $page === $departements->lastPage() || abs($page - $departements->currentPage()) <= 1)
                                @if($page === $departements->currentPage())
                                    <span class="is-current">{{ $page }}</span>
                                @else
                                    <a href="{{ $departements->url($page) }}">{{ $page }}</a>
                                @endif
                            @elseif($page === 4)
                                <span class="is-gap">...</span>
                            @endif
                        @endforeach

                        @if($departements->hasMorePages())
                            <a href="{{ $departements->nextPageUrl() }}"><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></a>
                        @else
                            <button type="button" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></button>
                        @endif
                    </nav>
                @endif
            </footer>
        </section>

        <section class="ceet-catalog-summary-grid" aria-label="Synthèse départs">
            <article class="ceet-catalog-summary-card">
                <h3>Répartition tension</h3>
                <div class="ceet-catalog-summary-body">
                    <strong class="ceet-catalog-square">{{ $htShare }}%</strong>
                    <p><b>Haute Tension</b><span>Dominance du réseau HT</span></p>
                </div>
            </article>

            <article class="ceet-catalog-summary-card">
                <h3>État du parc</h3>
                <div class="ceet-catalog-summary-body">
                    <strong class="ceet-catalog-square is-green">{{ $stats['activeCount'] }}</strong>
                    <p><b>Actifs</b><span>Opérationnels en temps réel</span></p>
                </div>
            </article>

            <article class="ceet-catalog-summary-card">
                <h3>Dernière mise à jour</h3>
                <div class="ceet-catalog-summary-body">
                    <span class="ceet-catalog-sync-icon"><span class="material-symbols-outlined" aria-hidden="true">history</span></span>
                    <p><b>{{ $lastUpdated }}</b><span>Synchronisation effectuée</span></p>
                </div>
            </article>
        </section>
    </main>
</body>
</html>
