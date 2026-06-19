@php
    use Illuminate\Support\Str;

    $item = $type ?? null;
    $defaultLabel = old('libelle', $item->libelle ?? 'Manque de tension');
    $defaultCode = old('code', $item->code ?? Str::limit(Str::upper(Str::slug($defaultLabel ?: 'TYPE_INCIDENT', '_')), 20, ''));
    $isActive = (bool) old('is_active', $item->is_active ?? true);
@endphp

<div class="ceet-status-form-grid">
    <div class="ceet-status-form-main">
        <section class="ceet-catalogue-form-panel">
            <div class="ceet-catalogue-form-panel-header">
                <h2>Informations générales</h2>
            </div>

            <div class="ceet-catalogue-form-panel-body">
                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="ceet-field-group">
                            <label class="form-label" for="code">Code technique <span class="ceet-required">*</span></label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                maxlength="20"
                                class="form-control ceet-code-field @error('code') is-invalid @enderror"
                                value="{{ $defaultCode }}"
                                placeholder="Ex : MTENSION"
                                required
                                data-type-code
                            >
                            <p class="ceet-field-help">Identifiant unique utilisé par les règles métier et les exports.</p>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="col-12 col-lg-7">
                        <div class="ceet-field-group">
                            <label class="form-label" for="libelle">Libellé <span class="ceet-required">*</span></label>
                            <input
                                type="text"
                                id="libelle"
                                name="libelle"
                                maxlength="150"
                                class="form-control @error('libelle') is-invalid @enderror"
                                value="{{ old('libelle', $item->libelle ?? '') }}"
                                placeholder="Ex : Manque de tension"
                                required
                                data-type-label
                            >
                            <p class="ceet-field-help">Nom visible par les opérateurs lors de la déclaration.</p>
                            @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="ceet-field-group mt-4">
                    <label class="form-label" for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Décrivez le périmètre de ce type d’incident..."
                    >{{ old('description', $item->description ?? '') }}</textarea>
                    <p class="ceet-field-help">Cette description facilite la qualification correcte des incidents.</p>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <section class="ceet-catalogue-form-panel">
            <div class="ceet-catalogue-form-panel-header">
                <h2>Usage opérationnel</h2>
            </div>

            <div class="ceet-catalogue-form-panel-body">
                <div class="ceet-catalogue-info-grid">
                    <article class="ceet-catalogue-info-card">
                        <span class="material-symbols-outlined" aria-hidden="true">assignment</span>
                        <div>
                            <strong>Déclaration</strong>
                            <p>Le type sera proposé dans le formulaire de création d’incident si son statut est actif.</p>
                        </div>
                    </article>

                    <article class="ceet-catalogue-info-card">
                        <span class="material-symbols-outlined" aria-hidden="true">analytics</span>
                        <div>
                            <strong>Reporting</strong>
                            <p>Le code technique sert aux regroupements, filtres et exports statistiques.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>

    <aside class="ceet-status-form-side">
        <section class="ceet-catalogue-form-panel">
            <div class="ceet-catalogue-form-panel-header">
                <h2>Disponibilité</h2>
            </div>

            <div class="ceet-catalogue-form-panel-body">
                <input type="hidden" name="is_active" value="0">
                <label class="ceet-form-switch-card is-compact" for="is_active">
                    <span>
                        <strong>Type actif</strong>
                        <small>Disponible pour les nouveaux incidents.</small>
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
                    <strong>Attention</strong>
                    <p>Désactiver un type ne modifie pas les incidents existants. Il bloque seulement son usage futur.</p>
                </div>
            </div>
        </section>

        <section class="ceet-status-preview-card">
            <div class="ceet-status-preview-decoration">T</div>
            <h2>Aperçu</h2>
            <div class="ceet-status-preview-box">
                <span class="ceet-status-preview-badge" data-type-preview>{{ $defaultLabel ?: 'Manque de tension' }}</span>
            </div>
            <p>Prévisualisation du libellé affiché dans les listes métier.</p>
        </section>

        <section class="ceet-catalogue-form-actions-mobile">
            <a href="{{ route('catalogues.types.index') }}" class="btn btn-outline-secondary w-100">Annuler</a>
            <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
        </section>
    </aside>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const labelInput = document.querySelector('[data-type-label]');
        const codeInput = document.querySelector('[data-type-code]');
        const preview = document.querySelector('[data-type-preview]');

        const slugifyTypeCode = (value) => value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .toUpperCase()
            .slice(0, 20);

        if (labelInput) {
            labelInput.addEventListener('input', () => {
                if (preview) {
                    preview.textContent = labelInput.value.trim() || 'Manque de tension';
                }

                if (codeInput && (!codeInput.dataset.touched || codeInput.dataset.touched === '0')) {
                    codeInput.value = slugifyTypeCode(labelInput.value);
                }
            });
        }

        if (codeInput) {
            codeInput.addEventListener('input', () => {
                codeInput.dataset.touched = '1';
                codeInput.value = slugifyTypeCode(codeInput.value);
            });
        }
    });
</script>
