<x-app-layout>
    <div class="ceet-catalogue-page ceet-catalogue-form-page">
        <header class="ceet-catalogue-header">
            <div>
                <nav class="ceet-catalogue-breadcrumb" aria-label="Fil d’Ariane">
                    @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                    <a href="{{ route('catalogues.index') }}">Configuration</a>
                    @endunless
                    <span>/</span>
                    <a href="{{ route('catalogues.statuts.index') }}">Workflows</a>
                    <span>/</span>
                    <strong>Créer statut</strong>
                </nav>

                <h1 class="ceet-page-title">37. Créer statut</h1>
                <p class="ceet-page-subtitle">
                    Définissez les paramètres d’un nouveau jalon pour le workflow des incidents.
                </p>
            </div>

            <div class="ceet-catalogue-table-actions">
                <a href="{{ route('catalogues.statuts.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" form="ceet-status-create-form" class="btn btn-primary">Enregistrer le statut</button>
            </div>
        </header>

        <form method="POST" action="{{ route('catalogues.statuts.store') }}" id="ceet-status-create-form" class="ceet-catalogue-form-shell">
            @csrf
            @include('catalogues.statuts.partials.form')
        </form>
    </div>
</x-app-layout>
