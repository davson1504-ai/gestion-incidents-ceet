@extends('layouts.app')

@section('title', 'Catalogues techniques')

@section('page_css')
    @vite('resources/css/pages/catalogues.css')
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $departements = collect($departements ?? []);
    $types = collect($types ?? []);
    $causes = collect($causes ?? []);
    $statuts = collect($statuts ?? []);
    $priorites = collect($priorites ?? []);

    $catalogues = [
        [
            'title' => 'Départs électriques',
            'description' => 'Configuration de la topologie du réseau et des points de départ HT/BT.',
            'count' => $departements->count(),
            'unit' => $departements->count() > 1 ? 'départs configurés' : 'départ configuré',
            'icon' => 'electric_bolt',
            'index' => Route::has('catalogues.departements.index') ? route('catalogues.departements.index') : '#',
            'create' => Route::has('catalogues.departements.create') ? route('catalogues.departements.create') : '#',
            'keywords' => 'départs électriques departements réseau poste ht bt',
        ],
        [
            'title' => 'Types d’incidents',
            'description' => 'Classification technique des pannes : court-circuit, surcharge, défaut réseau.',
            'count' => $types->count(),
            'unit' => $types->count() > 1 ? 'types définis' : 'type défini',
            'icon' => 'category',
            'index' => Route::has('catalogues.types.index') ? route('catalogues.types.index') : '#',
            'create' => Route::has('catalogues.types.create') ? route('catalogues.types.create') : '#',
            'keywords' => 'types incidents panne court-circuit surcharge défaut',
        ],
        [
            'title' => 'Causes probables',
            'description' => 'Arbre des causes standardisé pour faciliter le diagnostic et le reporting.',
            'count' => $causes->count(),
            'unit' => $causes->count() > 1 ? 'causes répertoriées' : 'cause répertoriée',
            'icon' => 'help',
            'index' => Route::has('catalogues.causes.index') ? route('catalogues.causes.index') : '#',
            'create' => Route::has('catalogues.causes.create') ? route('catalogues.causes.create') : '#',
            'keywords' => 'causes probables diagnostic origine panne reporting',
        ],
        [
            'title' => 'Priorités',
            'description' => 'Niveaux de criticité et délais d’intervention associés aux SLA.',
            'count' => $priorites->count(),
            'unit' => $priorites->count() > 1 ? 'niveaux de priorité' : 'niveau de priorité',
            'icon' => 'priority_high',
            'index' => Route::has('catalogues.priorites.index') ? route('catalogues.priorites.index') : '#',
            'create' => Route::has('catalogues.priorites.create') ? route('catalogues.priorites.create') : '#',
            'keywords' => 'priorités priorité criticité urgence sla',
        ],
        [
            'title' => 'Statuts',
            'description' => 'Workflow des incidents, de l’ouverture à la clôture technique.',
            'count' => $statuts->count(),
            'unit' => $statuts->count() > 1 ? 'états configurés' : 'état configuré',
            'icon' => 'assignment_turned_in',
            'index' => Route::has('catalogues.statuts.index') ? route('catalogues.statuts.index') : '#',
            'create' => Route::has('catalogues.statuts.create') ? route('catalogues.statuts.create') : '#',
            'keywords' => 'statuts status workflow ouverture clôture traitement',
        ],
        [
            'title' => 'Observations types',
            'description' => 'Bibliothèque de commentaires pré-rédigés pour les rapports d’intervention.',
            'count' => 0,
            'unit' => 'modèles disponibles',
            'icon' => 'history_edu',
            'index' => Route::has('reports.index') ? route('reports.index') : '#',
            'create' => null,
            'keywords' => 'observations commentaires rapports intervention modèles',
        ],
    ];
@endphp

@section('content')
<section class="ceet-catalogues-page">
    <nav class="ceet-catalogues-breadcrumb" aria-label="Fil d’Ariane">
        <a href="{{ route('dashboard') }}">Administration</a>
        <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
        <strong>Catalogues</strong>
    </nav>

    <header class="ceet-catalogues-hero">
        <div class="ceet-catalogues-hero-main">
            <span class="ceet-catalogues-eyebrow">Référentiels techniques</span>
            <h1>Catalogues techniques</h1>
            <p>
                Configuration centralisée du système de gestion des incidents électriques.
                Gérez les référentiels métier pour garantir l’intégrité des données opérationnelles.
            </p>
        </div>

        <div class="ceet-catalogues-actions">
            <label class="ceet-catalogues-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input type="search" placeholder="Rechercher dans les catalogues..." data-catalogue-search>
            </label>

            @can('catalogues.manage')
                @if (Route::has('catalogues.import.store'))
                    <button type="button" class="ceet-catalogues-import-btn" data-catalogues-import-open>
                        <span class="material-symbols-outlined" aria-hidden="true">upload_file</span>
                        Importer Excel
                    </button>
                @endif

                <div class="ceet-catalogues-create" data-catalogue-create-menu>
                    <button type="button" data-catalogue-create-toggle aria-expanded="false">
                        <span class="material-symbols-outlined" aria-hidden="true">add</span>
                        Nouveau catalogue
                    </button>

                    <div class="ceet-catalogues-create-menu" data-catalogue-create-panel hidden>
                        @foreach ($catalogues as $catalogue)
                            @if (!empty($catalogue['create']) && $catalogue['create'] !== '#')
                                <a href="{{ $catalogue['create'] }}">{{ $catalogue['title'] }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endcan
        </div>
    </header>

    <section class="ceet-catalogues-grid" aria-label="Liste des catalogues techniques">
        @foreach ($catalogues as $catalogue)
            <article
                class="ceet-catalogue-card"
                data-catalogue-card
                data-catalogue-keywords="{{ $catalogue['keywords'] }} {{ $catalogue['title'] }} {{ $catalogue['description'] }}"
            >
                <a href="{{ $catalogue['index'] }}" aria-label="Ouvrir {{ $catalogue['title'] }}">
                    <div class="ceet-catalogue-card-top">
                        <span class="ceet-catalogue-icon">
                            <span class="material-symbols-outlined" aria-hidden="true">{{ $catalogue['icon'] }}</span>
                        </span>

                        <span class="material-symbols-outlined ceet-catalogue-arrow" aria-hidden="true">arrow_forward</span>
                    </div>

                    <div class="ceet-catalogue-card-body">
                        <h2>{{ $catalogue['title'] }}</h2>
                        <p>{{ $catalogue['description'] }}</p>
                    </div>

                    <footer class="ceet-catalogue-card-footer">
                        <span>Population</span>
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
        <div class="ceet-catalogues-footer-info">
            <span class="material-symbols-outlined" aria-hidden="true">info</span>
            <p>
                Les catalogues centralisent les données techniques utilisées dans les incidents,
                rapports et tableaux de bord.
            </p>
        </div>

        <nav class="ceet-catalogues-footer-actions" aria-label="Actions catalogue">
            @if (Route::has('historique.index'))
                <a href="{{ route('historique.index') }}">Journal d’audit</a>
            @endif

            @if (Route::has('reports.index'))
                <a href="{{ route('reports.index') }}">Rapports</a>
            @endif
        </nav>
    </section>

    @can('catalogues.manage')
        <div class="ceet-catalogues-import-modal" data-catalogues-import-modal hidden>
            <div class="ceet-catalogues-import-backdrop" data-catalogues-import-close></div>

            <section class="ceet-catalogues-import-dialog" role="dialog" aria-modal="true" aria-labelledby="ceet-catalogues-import-title">
                <header>
                    <div>
                        <span class="ceet-catalogues-eyebrow">Import référentiel</span>
                        <h2 id="ceet-catalogues-import-title">Téléverser un fichier Excel</h2>
                        <p>
                            Utilisez cette option pour alimenter les catalogues sans passer par les seeders.
                            Le fichier peut contenir les feuilles : départements, types_incidents, causes, priorités et statuts.
                        </p>
                    </div>

                    <button type="button" class="ceet-catalogues-import-close" data-catalogues-import-close aria-label="Fermer">
                        <span class="material-symbols-outlined" aria-hidden="true">close</span>
                    </button>
                </header>

                <form method="POST" action="{{ route('catalogues.import.store') }}" enctype="multipart/form-data">
                    @csrf

                    <label class="ceet-catalogues-import-dropzone">
                        <span class="material-symbols-outlined" aria-hidden="true">upload_file</span>
                        <strong>Choisir le fichier Excel</strong>
                        <small>Formats acceptés : .xlsx, .xls, .csv — taille max : 10 Mo</small>
                        <input type="file" name="catalogues_file" accept=".xlsx,.xls,.csv" required>
                    </label>

                    <div class="ceet-catalogues-import-options">
                        <label>
                            <input type="radio" name="mode" value="upsert" checked>
                            Mettre à jour si le code existe déjà
                        </label>

                        <label>
                            <input type="radio" name="mode" value="insert">
                            Insérer uniquement de nouvelles lignes
                        </label>
                    </div>

                    <footer>
                        @if (Route::has('catalogues.import.template'))
                            <a href="{{ route('catalogues.import.template') }}" class="ceet-catalogues-template-link">
                                Télécharger le modèle Excel
                            </a>
                        @endif

                        <div>
                            <button type="button" class="ceet-catalogues-import-cancel" data-catalogues-import-close>Annuler</button>
                            <button type="submit" class="ceet-catalogues-import-submit">Importer les données</button>
                        </div>
                    </footer>
                </form>
            </section>
        </div>
    @endcan
</section>
@endsection

@section('page_js')
    @vite('resources/js/pages/catalogues.js')
@endsection
