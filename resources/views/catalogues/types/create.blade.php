<x-app-layout>
    <div class="ceet-catalogue-page ceet-catalogue-form-page">
        <header class="ceet-catalogue-header">
            <div>
                <nav class="ceet-catalogue-breadcrumb" aria-label="Fil d’Ariane">
                    @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                    <a href="{{ route('catalogues.index') }}">Configuration</a>
                    @endunless
                    <span>/</span>
                    <a href="{{ route('catalogues.types.index') }}">Types d’incidents</a>
                    <span>/</span>
                    <strong>Créer</strong>
                </nav>

                <h1 class="ceet-page-title">Créer un type d’incident</h1>
                <p class="ceet-page-subtitle">
                    Définissez une nouvelle famille d’incidents disponible dans les formulaires de déclaration.
                </p>
            </div>

            <div class="ceet-catalogue-table-actions">
                <a href="{{ route('catalogues.types.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" form="ceet-type-form" class="btn btn-primary">Enregistrer le type</button>
            </div>
        </header>

        <form method="POST" action="{{ route('catalogues.types.store') }}" id="ceet-type-form" class="ceet-catalogue-form-shell">
            @csrf
            @include('catalogues.types.partials.form')
        </form>
    </div>
</x-app-layout>
