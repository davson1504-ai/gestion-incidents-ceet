<x-app-layout>
    <div class="ceet-page ceet-priority-editor-page">
        <header class="ceet-page-header d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <span class="ceet-page-kicker">Paramètres / Catalogues / Priorités</span>
                <h1 class="ceet-page-title">Nouvelle priorité</h1>
                <p class="ceet-page-subtitle">
                    Définissez un niveau de criticité disponible dans les formulaires de déclaration d’incident.
                </p>
            </div>

            <div class="ceet-page-actions d-flex gap-2">
                <a href="{{ route('catalogues.priorites.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button form="priority-create-form" type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Correction nécessaire.</strong>
                <div class="mt-1">Vérifiez les champs signalés avant d’enregistrer.</div>
            </div>
        @endif

        <section class="ceet-card ceet-priority-main-card">
            <div class="ceet-priority-card-header">
                <div>
                    <h2>Informations générales</h2>
                    <p>Code, libellé, niveau et état d’activation de la priorité.</p>
                </div>
                <span class="ceet-priority-pill">{{ old('code', 'P1') }}</span>
            </div>

            <form id="priority-create-form" method="POST" action="{{ route('catalogues.priorites.store') }}" class="ceet-priority-form">
                @csrf

                @include('catalogues.priorites.partials.form')

                <div class="mt-4">
                    <input type="hidden" name="is_active" value="0">
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
                        <span class="form-check-label">Priorité active dans les formulaires incident</span>
                    </label>
                    @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
