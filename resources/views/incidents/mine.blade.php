@extends('layouts.app')

@section('title', 'Mes incidents')

@section('page_css')
    @vite([
        'resources/css/app.css',
        'resources/css/pages/incidents-mine.css'
    ])
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();

    $userName = $currentUser?->name ?? 'Opérateur Terrain';
    $userEmail = $currentUser?->email ?? 'operateur@ceet.tg';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = strtoupper($initials ?: 'OT');

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $incidentItems = method_exists($incidents ?? null, 'getCollection')
        ? $incidents->getCollection()
        : collect($incidents ?? []);

    $totalAssigned = method_exists($incidents ?? null, 'total')
        ? $incidents->total()
        : $incidentItems->count();

    $openCount = (int) data_get($stats ?? [], 'openCount', 0);

    if ($openCount === 0) {
        $openCount = $incidentItems->filter(function ($incident) {
            $status = mb_strtolower(optional($incident->status)->libelle ?? '');

            return ! str_contains($status, 'résolu')
                && ! str_contains($status, 'resolu')
                && ! str_contains($status, 'clôt')
                && ! str_contains($status, 'clot');
        })->count();
    }

    $highPriorityCount = $incidentItems->filter(function ($incident) {
        $priority = mb_strtolower(optional($incident->priorite)->libelle ?? '');
        $level = optional($incident->priorite)->niveau;

        return str_contains($priority, 'haute')
            || str_contains($priority, 'critique')
            || (string) $level === '1';
    })->count();

    $resolvedToday = $incidentItems->filter(function ($incident) {
        $status = mb_strtolower(optional($incident->status)->libelle ?? '');
        $isResolved = str_contains($status, 'résolu')
            || str_contains($status, 'resolu')
            || str_contains($status, 'clôt')
            || str_contains($status, 'clot');

        return $isResolved && $incident->date_fin && $incident->date_fin->isToday();
    })->count();

    $roleName = 'OPÉRATEUR';

    if ($currentUser && method_exists($currentUser, 'getRoleNames')) {
        $roleName = strtoupper($currentUser->getRoleNames()->first() ?? 'OPÉRATEUR');
    }
@endphp

@section('content')
<div class="ceet-mine-page" data-mine-page>
<main class="ceet-mine-main">
    <section class="ceet-mine-page-header">
        <div>
            <h2>{{ $listContext['title'] ?? 'Mes traitements' }}</h2>

            <nav class="ceet-mine-breadcrumb" aria-label="Fil d'Ariane">
                <span>Incidents</span>
                <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                <strong>Mes incidents</strong>
            </nav>
        </div>

        <div class="ceet-mine-header-actions">
            <button type="button" class="ceet-mine-secondary-btn" data-mine-filter-toggle>
                <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                Filtrer
            </button>
        </div>
    </section>

    @if (session('success'))
        <div class="ceet-mine-alert is-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ceet-mine-alert is-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <section class="ceet-mine-stats-grid" aria-label="Indicateurs incidents opérateur">
        <article class="ceet-mine-stat-card">
            <p>Total traitements</p>
            <strong>{{ number_format($totalAssigned, 0, ',', ' ') }}</strong>
        </article>

        <article class="ceet-mine-stat-card">
            <p>En cours</p>
            <strong>{{ number_format($openCount, 0, ',', ' ') }}</strong>
        </article>

        <article class="ceet-mine-stat-card">
            <p>Priorité haute</p>
            <strong class="is-danger">{{ number_format($highPriorityCount, 0, ',', ' ') }}</strong>
        </article>

        <article class="ceet-mine-stat-card">
            <p>Résolus 24h</p>
            <strong>{{ number_format($resolvedToday, 0, ',', ' ') }}</strong>
        </article>
    </section>

    <section class="ceet-mine-filter-panel" data-mine-filter-panel hidden>
        <form action="{{ $safeRoute('incidents.mine', [], '/mes-incidents') }}" method="GET" class="ceet-mine-filters">
            <div class="ceet-mine-field is-large">
                <label for="q">Recherche</label>
                <input
                    id="q"
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Code, type, localisation..."
                >
            </div>

            <div class="ceet-mine-field">
                <label for="priorite_id">Priorité</label>
                <select id="priorite_id" name="priorite_id">
                    <option value="">Toutes</option>

                    @foreach ($priorites ?? [] as $priorite)
                        <option value="{{ $priorite->id }}" @selected((string) ($filters['priorite_id'] ?? '') === (string) $priorite->id)>
                            {{ $priorite->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ceet-mine-field">
                <label for="status_id">Statut</label>
                <select id="status_id" name="status_id">
                    <option value="">Tous</option>

                    @foreach ($statuts ?? [] as $statut)
                        <option value="{{ $statut->id }}" @selected((string) ($filters['status_id'] ?? '') === (string) $statut->id)>
                            {{ $statut->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ceet-mine-filter-actions">
                <button type="submit" class="ceet-mine-square-btn" aria-label="Appliquer les filtres">
                    <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                </button>

                <a href="{{ $safeRoute('incidents.mine', [], '/mes-incidents') }}" class="ceet-mine-square-btn" aria-label="Réinitialiser">
                    <span class="material-symbols-outlined" aria-hidden="true">refresh</span>
                </a>
            </div>
        </form>
    </section>

    <section class="ceet-mine-table-panel">
        <div class="ceet-mine-table-wrap">
            <table class="ceet-mine-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Rapport</th>
                        <th>Départ / Secteur</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($incidents as $incident)
                        @php
                            $code = $incident->code_incident ?: 'INC-' . $incident->id;
                            $date = $incident->date_debut;
                            $priorite = optional($incident->priorite)->libelle ?? 'N/A';
                            $status = optional($incident->status)->libelle ?? 'N/A';
                            $depart = optional($incident->departement)->nom ?? 'N/A';
                            $sector = $incident->localisation
                                ?: optional($incident->typeIncident)->libelle
                                ?: optional($incident->cause)->libelle
                                ?: 'Secteur non renseigné';

                            $priorityLower = mb_strtolower($priorite);
                            $statusLower = mb_strtolower($status);

                            $priorityClass = str_contains($priorityLower, 'critique')
                                ? 'is-critical'
                                : (str_contains($priorityLower, 'haute')
                                    ? 'is-high'
                                    : (str_contains($priorityLower, 'moyenne')
                                        ? 'is-medium'
                                        : 'is-low'));

                            $statusClass = str_contains($statusLower, 'cours')
                                ? 'is-progress'
                                : (str_contains($statusLower, 'attente')
                                    ? 'is-pending'
                                    : ((str_contains($statusLower, 'assign') || str_contains($statusLower, 'affect'))
                                        ? 'is-assigned'
                                        : ((str_contains($statusLower, 'résolu') || str_contains($statusLower, 'resolu') || str_contains($statusLower, 'clôt') || str_contains($statusLower, 'clot'))
                                            ? 'is-resolved'
                                            : 'is-new')));

                            $report = $incident->report;
                            $reportStatusCode = mb_strtoupper((string) ($report?->statut_rapport ?? ''));
                            $reportLabel = match ($reportStatusCode) {
                                'SOUMIS' => 'Soumis',
                                'VALIDE' => 'Validé',
                                'REFUSE' => 'Refusé',
                                'BROUILLON' => 'Brouillon',
                                default => 'Non soumis',
                            };
                            $reportClass = match ($reportStatusCode) {
                                'SOUMIS' => 'is-pending',
                                'VALIDE' => 'is-resolved',
                                'REFUSE' => 'is-rejected',
                                default => 'is-new',
                            };

                            $incidentUrl = Route::has('incidents.show') ? route('incidents.show', $incident) : '#';
                        @endphp

                        <tr data-mine-row>
                            <td>
                                <strong>{{ $code }}</strong>

                                @if ($date)
                                    <span>Signalé {{ $date->diffForHumans() }}</span>
                                @else
                                    <span>Date non renseignée</span>
                                @endif
                            </td>

                            <td>
                                <div class="ceet-mine-priority {{ $priorityClass }}">
                                    <i></i>
                                    <span>{{ $priorite }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="ceet-mine-status {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td>
                                <span class="ceet-mine-status {{ $reportClass }}">
                                    {{ $reportLabel }}
                                </span>
                                @if ($reportStatusCode === 'REFUSE')
                                    <span class="ceet-mine-report-reason">Motif : {{ $report->motif_refus ?: 'Non renseigné' }}</span>
                                @elseif ($report?->date_soumission)
                                    <span class="ceet-mine-report-reason">Soumis {{ $report->date_soumission->diffForHumans() }}</span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $sector }}</strong>
                                <span>{{ $depart }}</span>
                            </td>

                            <td class="is-right">
                                <div class="ceet-mine-row-actions">
                                    <a href="{{ $incidentUrl }}" title="Détails" aria-label="Voir l'incident">
                                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="ceet-mine-empty-row">
                                Aucun incident assigné.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="ceet-mine-table-footer">
            <span>
                @if (method_exists($incidents, 'firstItem') && $incidents->total() > 0)
                    Affichage de {{ $incidents->firstItem() }}-{{ $incidents->lastItem() }} sur {{ $incidents->total() }} incidents
                @else
                    Affichage {{ collect($incidents)->count() }} incident(s)
                @endif
            </span>

            @if (method_exists($incidents, 'links'))
                <div class="ceet-mine-pagination">
                    {{ $incidents->links() }}
                </div>
            @endif
        </footer>
    </section>

    <section class="ceet-mine-info-grid">
        <article class="ceet-mine-info-card">
            <div class="ceet-mine-info-icon">
                <span class="material-symbols-outlined" aria-hidden="true">support_agent</span>
            </div>

            <div>
                <h3>Support Technique</h3>
                <p>Besoin d'assistance pour résoudre un incident complexe ? Contactez l'ingénierie centrale.</p>
            </div>
        </article>

        <article class="ceet-mine-info-card">
            <div class="ceet-mine-info-icon">
                <span class="material-symbols-outlined" aria-hidden="true">history</span>
            </div>

            <div>
                <h3>Journal d'activité</h3>
                <p>Vous avez résolu {{ $resolvedToday }} incident(s) sur les dernières 24 heures.</p>
            </div>
        </article>
    </section>
</main>
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/app.js',
        'resources/js/pages/incidents-mine.js'
    ])
@endsection
