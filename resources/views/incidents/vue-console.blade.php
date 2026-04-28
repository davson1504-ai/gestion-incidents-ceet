<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold mb-0">Console Vue CEET</h2>
    </x-slot>

    @vite('resources/js/ceet-vue/main.js')

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-5">
            <div id="incident-quick-form-app"></div>
        </div>
        <div class="col-12 col-xl-7">
            <div id="incidents-dashboard-app"></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div id="incident-history-app"></div>
        </div>
        <div class="col-12 col-xl-4">
            @can('catalogues.manage')
                <div id="catalogues-manager-app"></div>
            @else
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-2">Gestion des catalogues</h5>
                        <p class="text-muted mb-0">Accès en lecture seule sur cette console pour votre profil.</p>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
