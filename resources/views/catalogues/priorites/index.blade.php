@extends('layouts.app')

@section('title', "Priorités d'intervention")

@section('page_css')
    @vite([
        'resources/css/pages/catalogues.css'
    ])
@endsection

@php
    use Illuminate\Support\Str;

    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrateur'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrateur';
    $filters = array_merge(['q' => '', 'status' => ''], $filters ?? []);
    $from = $priorites->firstItem() ?? 0;
    $to = $priorites->lastItem() ?? 0;
    $total = $priorites->total();
    $activeCount = $priorites->getCollection()->where('is_active', true)->count();
    $slaCompliance = $total > 0 ? round(($activeCount / max($priorites->count(), 1)) * 100, 1) : 0;

    $slaFor = function ($priorite): array {
        return match ((int) $priorite->niveau) {
            1 => ['take' => '15 minutes', 'resolve' => '2 heures', 'height' => 88],
            2 => ['take' => '30 minutes', 'resolve' => '4 heures', 'height' => 70],
            3 => ['take' => '2 heures', 'resolve' => '24 heures', 'height' => 52],
            4 => ['take' => '1 jour', 'resolve' => '5 jours', 'height' => 36],
            default => ['take' => '3 jours', 'resolve' => '15 jours', 'height' => 22],
        };
    };

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Tous les incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
        ['label' => 'Utilisateurs', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'Statut du système', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')],
        ['label' => 'Catalogues', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Rapports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')],
    ];
@endphp

@section('content')
<div class="ceet-priorities-page">
<main class="ceet-priorities-main">
<section class="ceet-priorities-heading">
    <div>
        <h1>Priorit&eacute;s d'intervention</h1>
        <p>Gestion des seuils de Service Level Agreement (SLA) pour les incidents &eacute;lectriques.</p>
    </div>

    @can('catalogues.manage')
        <a href="{{ route('catalogues.priorites.create') }}" class="ceet-priorities-primary-btn">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
            Nouvelle Priorit&eacute;
        </a>
    @endcan
</section>

@if(session('success'))
    <div class="ceet-alert ceet-alert-success" role="status">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="ceet-alert ceet-alert-danger" role="alert">{{ session('error') }}</div>
@endif

<section class="ceet-priorities-table-card">
    <header>
        <h2>Catalogue des priorit&eacute;s</h2>
        <form method="GET" action="{{ route('catalogues.priorites.index') }}" class="ceet-priorities-filter">
            @if($filters['q'] !== '')
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
            @endif
            <select name="status" onchange="this.form.submit()" aria-label="Filtrer par statut">
                <option value="" @selected($filters['status'] === '')>Tous</option>
                <option value="active" @selected($filters['status'] === 'active')>Actifs</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactifs</option>
            </select>
            <a href="{{ route('catalogues.priorites.index') }}" aria-label="R&eacute;initialiser les filtres">
                <span class="material-symbols-outlined" aria-hidden="true">filter_list_off</span>
            </a>
        </form>
    </header>

    <div class="ceet-priorities-table-wrap">
        <table class="ceet-priorities-table">
            <thead>
                <tr>
                    <th>Niveau</th>
                    <th>Libell&eacute;</th>
                    <th>D&eacute;lai de prise en charge</th>
                    <th>D&eacute;lai de r&eacute;solution (SLA)</th>
                    <th>Statut</th>
                    <th class="is-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($priorites as $priorite)
                    @php
                        $sla = $slaFor($priorite);
                        $levelLabel = Str::startsWith(Str::upper($priorite->code), 'P') ? Str::upper($priorite->code) : 'P'.$priorite->niveau;
                    @endphp
                    <tr class="{{ $priorite->is_active ? '' : 'is-muted' }}">
                        <td>
                            <span class="ceet-priorities-level {{ (int) $priorite->niveau === 1 ? 'is-critical' : '' }}">{{ $levelLabel }}</span>
                        </td>
                        <td>
                            <strong>{{ $priorite->libelle }}</strong>
                            <small>{{ $priorite->description ?: 'Priorit&eacute; op&eacute;rationnelle CEET' }}</small>
                        </td>
                        <td>{{ $sla['take'] }}</td>
                        <td><b>{{ $sla['resolve'] }}</b></td>
                        <td>
                            <span class="ceet-priorities-status {{ $priorite->is_active ? 'is-active' : 'is-inactive' }}">
                                {{ $priorite->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="is-right">
                            <div class="ceet-priorities-actions">
                                @can('catalogues.manage')
                                    <a href="{{ route('catalogues.priorites.edit', $priorite) }}" aria-label="Modifier {{ $priorite->code }}">
                                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('catalogues.priorites.destroy', $priorite) }}" onsubmit="return confirm('Supprimer cette priorit&eacute; ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Supprimer {{ $priorite->code }}">
                                            <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="ceet-priorities-readonly">Lecture</span>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="ceet-priorities-empty">Aucune priorit&eacute; trouv&eacute;e.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <footer class="ceet-priorities-pagination">
        <span>Affichage de {{ $from }} &agrave; {{ $to }} sur {{ $total }} priorit&eacute;s</span>

        @if($priorites->lastPage() > 1)
            <nav aria-label="Pagination priorit&eacute;s">
                @if($priorites->onFirstPage())
                    <button type="button" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></button>
                @else
                    <a href="{{ $priorites->previousPageUrl() }}"><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></a>
                @endif

                @foreach(range(1, $priorites->lastPage()) as $page)
                    @if($page === $priorites->currentPage())
                        <span class="is-current">{{ $page }}</span>
                    @else
                        <a href="{{ $priorites->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($priorites->hasMorePages())
                    <a href="{{ $priorites->nextPageUrl() }}"><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></a>
                @else
                    <button type="button" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></button>
                @endif
            </nav>
        @endif
    </footer>
</section>

<section class="ceet-priorities-insights">
    <article class="ceet-priorities-sla-card">
        <div>
            <span class="material-symbols-outlined" aria-hidden="true">info</span>
            <h2>Gestion des SLA</h2>
        </div>
        <p>Les d&eacute;lais de r&eacute;solution sont calcul&eacute;s &agrave; partir de l'heure d'enregistrement de l'incident. Le non-respect de ces d&eacute;lais entra&icirc;ne une escalade automatique vers la direction technique.</p>
        <section>
            <div>
                <span>Conformit&eacute; Globale</span>
                <strong>{{ number_format($slaCompliance, 1, ',', ' ') }}%</strong>
            </div>
            <i style="--progress: {{ min(100, max(0, $slaCompliance)) }}%"></i>
        </section>
    </article>

    <article class="ceet-priorities-chart-card">
        <h2>R&eacute;partition des types d'incidents par priorit&eacute;</h2>
        <div class="ceet-priorities-bars">
            @forelse($priorites->getCollection()->take(5) as $priorite)
                @php($sla = $slaFor($priorite))
                <div>
                    <span style="--height: {{ $sla['height'] }}%"></span>
                    <strong>{{ Str::limit($priorite->libelle, 12) }}</strong>
                </div>
            @empty
                <p>Aucune donn&eacute;e disponible.</p>
            @endforelse
        </div>
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
