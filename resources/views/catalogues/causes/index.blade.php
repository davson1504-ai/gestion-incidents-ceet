@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrator'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrator';
    $filters = array_merge(['q' => '', 'status' => ''], $filters ?? []);
    $from = $causes->firstItem() ?? 0;
    $to = $causes->lastItem() ?? 0;
    $total = $causes->total();
    $topCause = $causes->first();

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
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

    <title>Causes Probables - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-causes-page">
    <aside class="ceet-causes-sidebar">
        <div class="ceet-causes-brand">
            <strong>CEET Incidents</strong>
            <span>Electrical Management</span>
        </div>

        <nav class="ceet-causes-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-causes-nav-link'])
            </nav>

        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="ceet-causes-user">
            <span>{{ $initials }}</span>
            <div>
                <strong>{{ $fullName }}</strong>
                <small>{{ $currentUser?->email ?? $roleName }}</small>
            </div>
        </a>
    </aside>

    <header class="ceet-causes-topbar">
        <nav class="ceet-causes-breadcrumb" aria-label="Fil d'Ariane">
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ Route::has('catalogues.index') ? route('catalogues.index') : '#' }}">Catalogs</a>
            @endunless
            <span>/</span>
            <strong>Causes Probables</strong>
        </nav>

        <div class="ceet-causes-toolbar">
            <button type="button" class="has-dot" aria-label="Notifications">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <button type="button" aria-label="Aide">
                <span class="material-symbols-outlined" aria-hidden="true">help</span>
            </button>
        </div>
    </header>

    <main class="ceet-causes-main">
        <section class="ceet-causes-heading">
            <div>
                <h1>Causes Probables</h1>
                <p>G&eacute;rer le dictionnaire des causes d'incidents &eacute;lectriques pour la standardisation des rapports.</p>
            </div>

            @can('catalogues.manage')
                <a href="{{ route('catalogues.causes.create') }}" class="ceet-causes-primary-btn">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Nouvelle Cause
                </a>
            @endcan
        </section>

        @if(session('success'))
            <div class="ceet-alert ceet-alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="ceet-alert ceet-alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <section class="ceet-causes-filter-card" aria-label="Filtres causes">
            <form method="GET" action="{{ route('catalogues.causes.index') }}" class="ceet-causes-filters">
                <label class="ceet-causes-search-field">
                    <span>Rechercher</span>
                    <span class="ceet-causes-search-box">
                        <span class="material-symbols-outlined" aria-hidden="true">search</span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Filtrer par code ou libell&eacute;...">
                    </span>
                </label>

                <label>
                    <span>Statut</span>
                    <select name="status">
                        <option value="" @selected($filters['status'] === '')>Tous</option>
                        <option value="active" @selected($filters['status'] === 'active')>Actif</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inactif</option>
                    </select>
                </label>

                <button type="submit" class="ceet-causes-filter-submit">Appliquer</button>
                <a href="{{ route('catalogues.causes.index') }}" class="ceet-causes-reset">R&eacute;initialiser</a>
            </form>
        </section>

        <section class="ceet-causes-table-card">
            <div class="ceet-causes-table-wrap">
                <table class="ceet-causes-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Libell&eacute;</th>
                            <th>Type d'incident associ&eacute;</th>
                            <th>Statut</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($causes as $cause)
                            <tr>
                                <td><strong>{{ $cause->code }}</strong></td>
                                <td>{{ $cause->libelle }}</td>
                                <td>
                                    <span class="ceet-causes-chip">{{ $cause->typeIncident?->libelle ?? 'Non associe' }}</span>
                                </td>
                                <td>
                                    <span class="ceet-causes-status {{ $cause->is_active ? 'is-active' : 'is-inactive' }}">
                                        <i aria-hidden="true"></i>
                                        {{ $cause->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="is-right">
                                    <div class="ceet-causes-row-actions">
                                        @can('catalogues.manage')
                                            <a href="{{ route('catalogues.causes.edit', $cause) }}" aria-label="Modifier {{ $cause->code }}">
                                                <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('catalogues.causes.destroy', $cause) }}" onsubmit="return confirm('Supprimer cette cause ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Supprimer {{ $cause->code }}">
                                                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="ceet-causes-muted-action">Lecture</span>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ceet-causes-empty">Aucune cause trouv&eacute;e.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="ceet-causes-pagination">
                <span>Affichage de {{ $from }}-{{ $to }} sur {{ $total }} causes</span>

                @if($causes->lastPage() > 1)
                    <nav aria-label="Pagination causes">
                        @if($causes->onFirstPage())
                            <button type="button" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></button>
                        @else
                            <a href="{{ $causes->previousPageUrl() }}"><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></a>
                        @endif

                        @foreach(range(1, $causes->lastPage()) as $page)
                            @if($page <= 3 || $page === $causes->lastPage() || abs($page - $causes->currentPage()) <= 1)
                                @if($page === $causes->currentPage())
                                    <span class="is-current">{{ $page }}</span>
                                @else
                                    <a href="{{ $causes->url($page) }}">{{ $page }}</a>
                                @endif
                            @elseif($page === 4)
                                <span class="is-gap">...</span>
                            @endif
                        @endforeach

                        @if($causes->hasMorePages())
                            <a href="{{ $causes->nextPageUrl() }}"><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></a>
                        @else
                            <button type="button" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></button>
                        @endif
                    </nav>
                @endif
            </footer>
        </section>

        <section class="ceet-causes-insight-grid">
            <article class="ceet-causes-stats-card">
                <div>
                    <h2>Statistiques de Classification</h2>
                    <p>
                        @if($topCause)
                            La cause "{{ $topCause->libelle }}" est disponible dans le catalogue actif des rapports d'incidents.
                        @else
                            Aucune cause n'est encore disponible pour alimenter les rapports.
                        @endif
                    </p>
                </div>
                <span aria-hidden="true"></span>
            </article>

            <article class="ceet-causes-advice-card">
                <span class="material-symbols-outlined" aria-hidden="true">info</span>
                <h2>Standardisation</h2>
                <p>L'utilisation rigoureuse des codes causes permet une analyse pr&eacute;dictive plus pr&eacute;cise des pannes r&eacute;seau.</p>
            </article>
        </section>
    </main>
</body>
</html>
