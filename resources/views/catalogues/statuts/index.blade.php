@extends('layouts.app')

@section('title', 'Gestion des statuts')

@section('page_css')
    @vite([
        'resources/css/pages/catalogues.css'
    ])
@endsection

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrateur'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'System Root';
    $filters = array_merge(['q' => '', 'status' => ''], $filters ?? []);
    $statusMetrics = array_merge(['total' => 0, 'active' => 0, 'final' => 0, 'last_updated_at' => null], $statusMetrics ?? []);
    $from = $statuts->firstItem() ?? 0;
    $to = $statuts->lastItem() ?? 0;
    $total = $statuts->total();
    $lastUpdated = $statusMetrics['last_updated_at']
        ? Carbon::parse($statusMetrics['last_updated_at'])->diffForHumans()
        : 'Aucune donnée';

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
        ['label' => 'Users', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'System Status', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')],
        ['label' => 'Catalogs', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')],
    ];
@endphp

@section('content')
<div class="ceet-statuses-page">
<main class="ceet-statuses-main">
<section class="ceet-statuses-head">
    <div>
        <nav aria-label="Fil d'Ariane">
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ Route::has('catalogues.index') ? route('catalogues.index') : '#' }}">Configuration</a>
            @endunless
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ route('catalogues.index') }}">Workflows</a>
            @endunless
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            <strong>Statuts</strong>
        </nav>
        <h1>Gestion des Statuts</h1>
    </div>

    @can('catalogues.manage')
        <a href="{{ route('catalogues.statuts.create') }}" class="ceet-statuses-primary-btn">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
            Nouveau Statut
        </a>
    @endcan
</section>

@if(session('success'))
    <div class="ceet-alert ceet-alert-success" role="status">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="ceet-alert ceet-alert-danger" role="alert">{{ session('error') }}</div>
@endif

<section class="ceet-statuses-metrics" aria-label="Indicateurs statuts">
    <article>
        <span>Total Statuts</span>
        <strong>{{ $statusMetrics['total'] }}</strong>
        <i class="material-symbols-outlined" aria-hidden="true">list_alt</i>
    </article>
    <article>
        <span>Actifs</span>
        <strong>{{ $statusMetrics['active'] }}</strong>
        <i aria-hidden="true"></i>
    </article>
    <article>
        <span>&Eacute;tapes finales</span>
        <strong>{{ $statusMetrics['final'] }}</strong>
        <i class="material-symbols-outlined" aria-hidden="true">check_circle</i>
    </article>
    <article>
        <span>Derni&egrave;re modif</span>
        <b>{{ $lastUpdated }}</b>
        <i class="material-symbols-outlined" aria-hidden="true">history</i>
    </article>
</section>

<section class="ceet-statuses-table-card">
    <header>
        <h2>Workflow Incidents &Eacute;lectriques</h2>
        <form method="GET" action="{{ route('catalogues.statuts.index') }}" class="ceet-statuses-filter">
            @if($filters['q'] !== '')
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
            @endif
            <select name="status" onchange="this.form.submit()" aria-label="Filtrer les statuts">
                <option value="" @selected($filters['status'] === '')>Tous</option>
                <option value="active" @selected($filters['status'] === 'active')>Actifs</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactifs</option>
                <option value="final" @selected($filters['status'] === 'final')>Finaux</option>
            </select>
            <a href="{{ route('catalogues.statuts.index') }}" aria-label="Réinitialiser les filtres">
                <span class="material-symbols-outlined" aria-hidden="true">filter_list_off</span>
            </a>
        </form>
    </header>

    <div class="ceet-statuses-table-wrap">
        <table class="ceet-statuses-table">
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Libell&eacute;</th>
                    <th>&Eacute;tape finale</th>
                    <th>Description</th>
                    <th>Statut actif</th>
                    <th class="is-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statuts as $statut)
                    <tr class="{{ $statut->is_active ? '' : 'is-muted' }}">
                        <td><strong>{{ $statut->ordre }}</strong></td>
                        <td>
                            <span class="ceet-statuses-label">
                                <i style="background-color: {{ $statut->couleur ?: '#76777d' }}" aria-hidden="true"></i>
                                {{ $statut->libelle }}
                            </span>
                        </td>
                        <td>
                            <span class="ceet-statuses-final {{ $statut->is_final ? 'is-final' : '' }}">
                                {{ $statut->is_final ? 'Oui' : 'Non' }}
                            </span>
                        </td>
                        <td>
                            <em>{{ $statut->description ?: 'Aucune description renseignée.' }}</em>
                        </td>
                        <td>
                            <span class="ceet-statuses-switch {{ $statut->is_active ? 'is-on' : '' }}" aria-label="{{ $statut->is_active ? 'Actif' : 'Inactif' }}"></span>
                        </td>
                        <td class="is-right">
                            <div class="ceet-statuses-actions">
                                @can('catalogues.manage')
                                    <a href="{{ route('catalogues.statuts.edit', $statut) }}" aria-label="Modifier {{ $statut->code }}">
                                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('catalogues.statuts.destroy', $statut) }}" onsubmit="return confirm('Supprimer ce statut ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Supprimer {{ $statut->code }}">
                                            <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="ceet-statuses-readonly">Lecture</span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="ceet-statuses-empty">Aucun statut trouv&eacute;.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <footer class="ceet-statuses-pagination">
        <span>Affichage de {{ $from }} &agrave; {{ $to }} sur {{ $total }} statuts</span>

        @if($statuts->lastPage() > 1)
            <nav aria-label="Pagination statuts">
                @if($statuts->onFirstPage())
                    <button type="button" disabled>Pr&eacute;c&eacute;dent</button>
                @else
                    <a href="{{ $statuts->previousPageUrl() }}">Pr&eacute;c&eacute;dent</a>
                @endif

                @foreach(range(1, $statuts->lastPage()) as $page)
                    @if($page === $statuts->currentPage())
                        <span class="is-current">{{ $page }}</span>
                    @else
                        <a href="{{ $statuts->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($statuts->hasMorePages())
                    <a href="{{ $statuts->nextPageUrl() }}">Suivant</a>
                @else
                    <button type="button" disabled>Suivant</button>
                @endif
            </nav>
        @endif
    </footer>
</section>

<section class="ceet-statuses-insights">
    <figure>
        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDoFhVWmqyUSEeQhytsQ10Kz1De8ervDRMr1VI0kVaxlI4M-VN76gv-y8AIiTsVhgQnsaYaK45ID772LNLcu4v8Hs0sWO8KuvpF4Hrx01UXugqpE9ZT5GMHGBlkyoizCZNtWS4xarJ0nUiZYtElMMP2hpn2GDLzxb4N98QbVUGvAmb8YaxEaGEIyOyva7bYwmWGyQezkLeM6lThVQwF6SOg512q7jY9Jb5xOQJLPktKhizTd5j9_2yu8kDWRGkhx9dRdXpfYlQ5k8W4" alt="Architecture réseau">
        <figcaption>
            <small>Architecture R&eacute;seau</small>
            <strong>Optimisation des flux de donn&eacute;es</strong>
        </figcaption>
    </figure>

    <article>
        <span class="material-symbols-outlined" aria-hidden="true">rule_settings</span>
        <div>
            <h2>Configuration Automatique</h2>
            <p>Les nouveaux statuts sont propag&eacute;s dans les formulaires d'incidents apr&egrave;s synchronisation du cache.</p>
        </div>
        <i aria-hidden="true"></i>
    </article>
</section>
    </main>
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/app.js'
    ])
@endsection
