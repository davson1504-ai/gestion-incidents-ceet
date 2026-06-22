@extends('layouts.app')

@section('title', $pageTitle ?? 'Gestion des incidents')

@section('page_css')
    @vite([
        'resources/css/app.css',
        'resources/css/pages/incidents-index.css'
    ])
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();

    

    $isAdmin = $isAdmin ?? ($currentUser?->isAdmin() ?? false);
    $isSupervisor = $isSupervisor ?? ($currentUser?->isSuperviseur() ?? false);
$userName = $currentUser?->name ?? 'Administrateur';
    $userEmail = $currentUser?->email ?? 'admin@ceet.tg';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = strtoupper($initials ?: 'AD');

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $pageTitle = data_get($listContext ?? [], 'title', 'Liste des incidents');
    $pageSubtitle = data_get($listContext ?? [], 'subtitle', 'Gestion et suivi en temps réel des pannes électriques sur le réseau.');
    $indexRoute = data_get($listContext ?? [], 'indexRoute', 'incidents.index');
    $routeName = Route::has($indexRoute) ? $indexRoute : 'incidents.index';

    $openCount = (int) data_get($stats ?? [], 'openCount', 0);
    $closedCount = (int) data_get($stats ?? [], 'closedCount', 0);

    $roleName = 'ADMINISTRATEUR';

    if ($currentUser && method_exists($currentUser, 'getRoleNames')) {
        $roleName = strtoupper($currentUser->getRoleNames()->first() ?? 'ADMINISTRATEUR');
    }
@endphp

@section('content')

<section class=\"ceet-incidents-index-layout\" data-incidents-index-layout>
<div class="ceet-incidents-index-page" data-incidents-index>
<main class="ceet-incidents-main">
    <section class="ceet-incidents-page-header">
        <div>
            <h2>{{ $pageTitle ?? 'Gestion des incidents' }}</h2>
            <p>{{ $pageSubtitle ?? 'Consultez, filtrez et suivez les incidents enregistrés dans le système.' }}</p>
        </div>
    </section>

    @if (session('success'))
        <div class="ceet-incidents-alert is-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ceet-incidents-alert is-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <section class="ceet-incidents-filter-panel">
        <form action="{{ $safeRoute($routeName, [], '/incidents') }}" method="GET" class="ceet-incidents-filters">
            <div class="ceet-incidents-field is-large">
                <label for="q">Recherche</label>
                <input
                    id="q"
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Code, description..."
                >
            </div>

            <div class="ceet-incidents-field">
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

            <div class="ceet-incidents-field">
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

            <div class="ceet-incidents-field">
                <label for="departement_id">Départ</label>
                <select id="departement_id" name="departement_id">
                    <option value="">Tous les départs</option>

                    @foreach ($departements ?? [] as $departement)
                        <option value="{{ $departement->id }}" @selected((string) ($filters['departement_id'] ?? '') === (string) $departement->id)>
                            {{ $departement->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ceet-incidents-field">
                <label for="operateur_id">Opérateur</label>
                <select id="operateur_id" name="operateur_id">
                    <option value="">Tous les opérateurs</option>

                    @foreach ($operateurs ?? [] as $operateur)
                        <option value="{{ $operateur->id }}" @selected((string) ($filters['operateur_id'] ?? '') === (string) $operateur->id)>
                            {{ $operateur->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ceet-incidents-field">
                <label for="date_from">Date début</label>
                <input
                    id="date_from"
                    type="date"
                    name="date_from"
                    value="{{ $filters['date_from'] ?? '' }}"
                >
            </div>

            <div class="ceet-incidents-field">
                <label for="date_to">Date fin</label>
                <input
                    id="date_to"
                    type="date"
                    name="date_to"
                    value="{{ $filters['date_to'] ?? '' }}"
                >
            </div>

            <div class="ceet-incidents-filter-actions">
                <button type="submit" class="ceet-incidents-square-btn" aria-label="Filtrer">
                    <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                </button>

                <a href="{{ $safeRoute($routeName, [], '/incidents') }}" class="ceet-incidents-square-btn" aria-label="Réinitialiser">
                    <span class="material-symbols-outlined" aria-hidden="true">refresh</span>
                </a>
            </div>
        </form>
    </section>

    <section class="ceet-incidents-summary-grid">
        <article>
            <span>Incidents ouverts</span>
            <strong>{{ number_format($openCount, 0, ',', ' ') }}</strong>
        </article>

        <article>
            <span>Incidents clôturés</span>
            <strong>{{ number_format($closedCount, 0, ',', ' ') }}</strong>
        </article>

        <article>
            <span>Total affiché</span>
            <strong>
                @if (method_exists($incidents, 'total'))
                    {{ number_format($incidents->total(), 0, ',', ' ') }}
                @else
                    {{ number_format(collect($incidents)->count(), 0, ',', ' ') }}
                @endif
            </strong>
        </article>
    </section>

    <section class="ceet-incidents-table-panel">
        <div class="ceet-incidents-table-wrap">
            <table class="ceet-incidents-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Date/Heure</th>
                        <th>Départ</th>
                        <th>Type</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Opérateur</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($incidents as $incident)
                        @php
                            $code = $incident->code_incident ?: 'INC-' . $incident->id;
                            $date = $incident->date_debut;
                            $depart = optional($incident->departement)->nom ?? 'N/A';
                            $type = optional($incident->typeIncident)->libelle ?? ($incident->titre ?: 'N/A');
                            $cause = optional($incident->cause)->libelle;
                            $priorite = optional($incident->priorite)->libelle ?? 'N/A';
                            $status = optional($incident->status)->libelle ?? 'N/A';
                            $operateur = optional($incident->responsable)->name ?? optional($incident->operateur)->name ?? 'Non affecté';

                            $priorityLower = str($priorite)->lower();
                            $statusLower = str($status)->lower();

                            $priorityClass = $priorityLower->contains('critique')
                                ? 'is-critical'
                                : ($priorityLower->contains('haute')
                                    ? 'is-high'
                                    : ($priorityLower->contains('moyenne')
                                        ? 'is-medium'
                                        : 'is-low'));

                            $statusClass = $statusLower->contains('résolu') || $statusLower->contains('resolu')
                                ? 'is-resolved'
                                : ($statusLower->contains('clôt') || $statusLower->contains('clot')
                                    ? 'is-closed'
                                    : ($statusLower->contains('cours')
                                        ? 'is-progress'
                                        : 'is-new'));

                            $incidentUrl = Route::has('incidents.show') ? route('incidents.show', $incident) : '#';
                            $editUrl = Route::has('incidents.edit') ? route('incidents.edit', $incident) : '#';
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ str_starts_with($code, '#') ? $code : '#' . $code }}</strong>
                            </td>

                            <td>
                                @if ($date)
                                    {{ $date->format('d M Y') }}<br>
                                    <span>{{ $date->format('H:i') }}</span>
                                @else
                                    N/A
                                @endif
                            </td>

                            <td>{{ $depart }}</td>

                            <td>
                                {{ $type }}

                                @if ($cause)
                                    <span>{{ $cause }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="ceet-incidents-chip {{ $priorityClass }}">
                                    {{ $priorite }}
                                </span>
                            </td>

                            <td>
                                <span class="ceet-incidents-chip {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td>
                                <div class="ceet-incidents-operator">
                                    <div class="ceet-incidents-mini-avatar">
                                        {{ strtoupper(substr($operateur, 0, 1)) }}
                                    </div>

                                    <span>{{ $operateur }}</span>
                                </div>
                            </td>

                            <td class="is-right">
                                <div class="ceet-incidents-row-actions">
                                    <a href="{{ $incidentUrl }}" aria-label="Voir l'incident">
                                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                                    </a>

                                    <a href="{{ $editUrl }}" aria-label="Modifier l'incident">
                                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ceet-incidents-empty-row">
                                Aucun incident trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="ceet-incidents-table-footer ceet-incidents-pagination-wrap">
            <span class="ceet-incidents-pagination-info">
                @if (method_exists($incidents, 'firstItem') && $incidents->total() > 0)
                    Affichage de {{ $incidents->firstItem() }}-{{ $incidents->lastItem() }} sur {{ $incidents->total() }} incidents
                @else
                    Affichage {{ collect($incidents)->count() }} incident(s)
                @endif
            </span>

            @if (method_exists($incidents, 'previousPageUrl') && method_exists($incidents, 'hasMorePages'))
                <div class="ceet-incidents-pagination-arrows" aria-label="Pagination incidents">
                    @if ($incidents->onFirstPage())
                        <span class="ceet-incidents-page-arrow is-disabled" aria-disabled="true" aria-label="Page précédente">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                        </span>
                    @else
                        <a href="{{ $incidents->previousPageUrl() }}" class="ceet-incidents-page-arrow" aria-label="Page précédente">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                        </a>
                    @endif

                    @if ($incidents->hasMorePages())
                        <a href="{{ $incidents->nextPageUrl() }}" class="ceet-incidents-page-arrow" aria-label="Page suivante">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                        </a>
                    @else
                        <span class="ceet-incidents-page-arrow is-disabled" aria-disabled="true" aria-label="Page suivante">
                            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                        </span>
                    @endif
                </div>
            @endif
        </footer>
    </section>

    <div class="ceet-incidents-toast" data-incidents-toast hidden>
        <div>
            <strong>Nouvel incident détecté</strong>
            <span>Synchronisation en cours...</span>
        </div>

        <button type="button" data-incidents-toast-close aria-label="Fermer">
            <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
    </div>
</main>
</div>
</section>

@endsection

@section('page_js')
    @vite([
        'resources/js/app.js',
        'resources/js/pages/incidents-index.js'
    ])
@endsection
