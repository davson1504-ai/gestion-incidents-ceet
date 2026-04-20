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
            <div id="catalogues-manager-app"></div>
        </div>
    </div>
</x-app-layout>
