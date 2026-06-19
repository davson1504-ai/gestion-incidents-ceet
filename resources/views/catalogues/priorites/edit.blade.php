@php
    use Illuminate\Support\Str;

    $incidentCount = 0;

    try {
        $incidentCount = $priorite->incidents()->count();
    } catch (\Throwable $e) {
        $incidentCount = 0;
    }

    $updatedAt = $priorite->updated_at ?? $priorite->created_at ?? null;
    $updatedAtLabel = $updatedAt ? $updatedAt->format('d/m/Y à H:i') : 'Non renseigné';
@endphp

<x-app-layout>
    <div class="ceet-page ceet-priority-editor-page">
        <header class="ceet-priority-editor-header">
            <div class="d-flex align-items-start gap-3">
                <a
                    href="{{ route('catalogues.priorites.index') }}"
                    class="ceet-priority-back-btn"
                    aria-label="Retour aux priorités"
                >
                    ←
                </a>

                <div>
                    <span class="ceet-page-kicker">Paramètres / Catalogues / Priorités</span>
                    <h1 class="ceet-page-title">Modifier la Priorité</h1>
                    <p class="ceet-page-subtitle">
                        Configuration du niveau de criticité utilisé pour classifier les incidents électriques.
                    </p>
                </div>
            </div>

            <div class="ceet-priority-editor-actions-top">
                <a href="{{ route('catalogues.priorites.index') }}" class="btn btn-outline-secondary">
                    Annuler
                </a>
                <button form="priority-edit-form" type="submit" class="btn btn-primary">
                    Enregistrer
                </button>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Correction nécessaire.</strong>
                <div class="mt-1">Vérifiez les champs signalés avant d’enregistrer.</div>
            </div>
        @endif

        <div class="ceet-priority-editor-grid">
            <section class="ceet-priority-main-card">
                <div class="ceet-priority-card-header">
                    <div>
                        <h2>Informations générales</h2>
                        <p>Libellé, code et description officielle de la priorité.</p>
                    </div>
                    <span class="ceet-priority-pill">{{ $priorite->code ?? 'PRIORITÉ' }}</span>
                </div>

                <form
                    id="priority-edit-form"
                    method="POST"
                    action="{{ route('catalogues.priorites.update', $priorite) }}"
                    class="ceet-priority-form"
                >
                    @csrf
                    @method('PUT')

                    @include('catalogues.priorites.partials.form', ['priorite' => $priorite])
                </form>
            </section>

            <aside class="ceet-priority-side-column">
                <section class="ceet-priority-side-card">
                    <div class="ceet-priority-card-header compact">
                        <div>
                            <h2>Paramètres d’activation</h2>
                            <p>État opérationnel de ce niveau.</p>
                        </div>
                    </div>

                    <div class="ceet-priority-side-body">
                        <input form="priority-edit-form" type="hidden" name="is_active" value="0">

                        <label class="ceet-priority-switch-row" for="priority-active-switch">
                            <span>
                                <strong>État de la priorité</strong>
                                <small>{{ old('is_active', $priorite->is_active) ? 'Active dans les formulaires incident' : 'Masquée dans les formulaires incident' }}</small>
                            </span>
                            <span class="ceet-priority-switch-shell">
                                <input
                                    id="priority-active-switch"
                                    form="priority-edit-form"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', $priorite->is_active ?? true) ? 'checked' : '' }}
                                >
                                <span></span>
                            </span>
                        </label>

                        <div class="ceet-priority-meta-box">
                            <span>Dernière modification</span>
                            <strong>{{ $updatedAtLabel }}</strong>
                        </div>

                        <div class="ceet-priority-meta-box">
                            <span>Utilisation actuelle</span>
                            <strong>{{ $incidentCount }} incident(s)</strong>
                        </div>
                    </div>
                </section>

                <section class="ceet-priority-preview-card">
                    <div class="ceet-priority-preview-title">Aperçu visuel</div>

                    <div class="ceet-priority-preview-icon" style="--priority-color: {{ old('couleur', $priorite->couleur ?: '#141b2b') }};">
                        <span>!</span>
                        <small>{{ old('code', $priorite->code) }}</small>
                    </div>

                    <strong>{{ old('libelle', $priorite->libelle) }}</strong>
                    <p>{{ Str::limit(old('description', $priorite->description), 96) }}</p>

                    <div class="ceet-priority-level-bars" aria-label="Niveau de priorité">
                        @for ($i = 1; $i <= 4; $i++)
                            <span class="{{ $i <= min(4, max(1, (int) old('niveau', $priorite->niveau))) ? 'active' : '' }}"></span>
                        @endfor
                    </div>
                </section>
            </aside>
        </div>

        <footer class="ceet-priority-sticky-actions">
            @can('catalogues.manage')
                <form
                    method="POST"
                    action="{{ route('catalogues.priorites.destroy', $priorite) }}"
                    onsubmit="return confirm('Supprimer ce niveau de priorité ?');"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="ceet-priority-delete-btn"
                        {{ $incidentCount > 0 ? 'disabled' : '' }}
                        title="{{ $incidentCount > 0 ? 'Suppression impossible : priorité utilisée par des incidents.' : 'Supprimer ce niveau' }}"
                    >
                        Supprimer ce niveau
                    </button>
                </form>
            @endcan

            <div class="ceet-priority-sticky-buttons">
                <a href="{{ route('catalogues.priorites.index') }}" class="btn btn-outline-secondary">
                    Annuler
                </a>
                <button form="priority-edit-form" type="submit" class="btn btn-primary">
                    Enregistrer les modifications
                </button>
            </div>
        </footer>
    </div>
</x-app-layout>
