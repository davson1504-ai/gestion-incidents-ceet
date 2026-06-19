@php
    use Illuminate\Support\Str;

    $s = $statut ?? null;
    $defaultLabel = old('libelle', $s->libelle ?? 'En Analyse');
    $defaultCode = old('code', $s->code ?? Str::upper(Str::slug($defaultLabel ?: 'EN_ANALYSE', '_')));
    $defaultColor = old('couleur', $s->couleur ?? '#141b2b');
    $isActive = old('is_active', $s->is_active ?? true);
    $isFinal = old('is_final', $s->is_final ?? false);
@endphp

<div class="ceet-status-form-grid">
    <div class="ceet-status-form-main">
        <section class="ceet-catalogue-form-panel">
            <div class="ceet-catalogue-form-panel-header">
                <h2>Informations générales</h2>
            </div>

            <div class="ceet-catalogue-form-panel-body">
                <div class="ceet-field-group">
                    <label class="form-label" for="libelle">Libellé du statut <span class="ceet-required">*</span></label>
                    <input
                        type="text"
                        id="libelle"
                        name="libelle"
                        class="form-control @error('libelle') is-invalid @enderror"
                        value="{{ old('libelle', $s->libelle ?? '') }}"
                        placeholder="Ex : En Analyse"
                        required
                        data-status-label
                    >
                    <p class="ceet-field-help">Le nom public du statut tel qu’affiché dans l’interface.</p>
                    @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="ceet-field-group">
                    <label class="form-label" for="description">Description du rôle</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Décrivez les responsabilités et les conditions de ce statut..."
                    >{{ old('description', $s->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <section class="ceet-catalogue-form-panel">
            <div class="ceet-catalogue-form-panel-header">
                <h2>Configuration technique</h2>
            </div>

            <div class="ceet-catalogue-form-panel-body">
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="ceet-field-group">
                            <label class="form-label" for="code">Code technique <span class="ceet-required">*</span></label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="form-control ceet-code-field @error('code') is-invalid @enderror"
                                value="{{ $defaultCode }}"
                                placeholder="Ex : EN_ANALYSE"
                                required
                                data-status-code
                            >
                            <p class="ceet-field-help">Identifiant unique utilisé par le système et les règles métier.</p>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="ceet-field-group">
                            <label class="form-label" for="ordre">Ordre d’affichage <span class="ceet-required">*</span></label>
                            <input
                                type="number"
                                min="0"
                                id="ordre"
                                name="ordre"
                                class="form-control @error('ordre') is-invalid @enderror"
                                value="{{ old('ordre', $s->ordre ?? 10) }}"
                                required
                            >
                            <p class="ceet-field-help">Position numérique dans les listes déroulantes.</p>
                            @error('ordre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="ceet-field-group">
                            <label class="form-label" for="couleur">Couleur de référence</label>
                            <input
                                type="color"
                                id="couleur"
                                name="couleur"
                                class="form-control form-control-color ceet-color-input @error('couleur') is-invalid @enderror"
                                value="{{ $defaultColor }}"
                                title="Choisir une couleur"
                                data-status-color
                            >
                            <p class="ceet-field-help">Conservée pour compatibilité avec les anciennes vues.</p>
                            @error('couleur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="ceet-field-group">
                            <label class="form-label">Étape finale</label>

                            <input type="hidden" name="is_final" value="0">
                            <label class="ceet-form-switch-card" for="is_final">
                                <span>
                                    <strong>Considérer comme clos</strong>
                                    <small>Si activé, l’incident sera marqué comme résolu ou clôturé.</small>
                                </span>
                                <span class="ceet-form-switch">
                                    <input
                                        type="checkbox"
                                        id="is_final"
                                        name="is_final"
                                        value="1"
                                        {{ $isFinal ? 'checked' : '' }}
                                    >
                                    <span></span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="ceet-status-form-side">
        <section class="ceet-catalogue-form-panel">
            <div class="ceet-catalogue-form-panel-header">
                <h2>Visibilité</h2>
            </div>

            <div class="ceet-catalogue-form-panel-body">
                <input type="hidden" name="is_active" value="0">
                <label class="ceet-form-switch-card is-compact" for="is_active">
                    <span>
                        <strong>Statut actif</strong>
                        <small>Disponible pour sélection.</small>
                    </span>
                    <span class="ceet-form-switch">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            {{ $isActive ? 'checked' : '' }}
                        >
                        <span></span>
                    </span>
                </label>

                <div class="ceet-catalogue-note mt-4">
                    <strong>Information</strong>
                    <p>Désactiver ce statut empêchera sa sélection pour de nouveaux incidents, sans modifier les incidents existants.</p>
                </div>
            </div>
        </section>

        <section class="ceet-status-preview-card">
            <div class="ceet-status-preview-decoration">⚙</div>
            <h2>Aperçu du badge</h2>
            <div class="ceet-status-preview-box">
                <span class="ceet-status-preview-badge" data-status-preview>{{ $defaultLabel ?: 'En Analyse' }}</span>
            </div>
            <p>Style utilitaire standard pour les grilles de données.</p>
        </section>

        <section class="ceet-catalogue-form-actions-mobile">
            <a href="{{ route('catalogues.statuts.index') }}" class="btn btn-outline-secondary w-100">Annuler</a>
            <button type="submit" class="btn btn-primary w-100">Enregistrer le statut</button>
        </section>
    </aside>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const labelInput = document.querySelector('[data-status-label]');
        const codeInput = document.querySelector('[data-status-code]');
        const colorInput = document.querySelector('[data-status-color]');
        const preview = document.querySelector('[data-status-preview]');

        const slugifyStatusCode = (value) => value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .toUpperCase();

        const syncPreview = () => {
            if (!labelInput || !preview) return;
            preview.textContent = labelInput.value.trim() || 'En Analyse';
        };

        const syncColor = () => {
            if (!colorInput || !preview) return;
            preview.style.borderColor = colorInput.value || '#141b2b';
            preview.style.color = colorInput.value || '#141b2b';
        };

        if (labelInput) {
            labelInput.addEventListener('input', () => {
                syncPreview();

                if (codeInput && (!codeInput.dataset.touched || codeInput.dataset.touched === '0')) {
                    codeInput.value = slugifyStatusCode(labelInput.value);
                }
            });
        }

        if (codeInput) {
            codeInput.addEventListener('input', () => {
                codeInput.dataset.touched = '1';
                codeInput.value = slugifyStatusCode(codeInput.value);
            });
        }

        if (colorInput) {
            colorInput.addEventListener('input', syncColor);
        }

        syncPreview();
        syncColor();
    });
</script>
