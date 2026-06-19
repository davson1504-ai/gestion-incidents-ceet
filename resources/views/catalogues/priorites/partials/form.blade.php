@php
    $p = $priorite ?? null;
    $currentLevel = (int) old('niveau', $p->niveau ?? 1);
    $sla = match (true) {
        $currentLevel <= 1 => ['prise' => '15 min', 'resolution' => '2 heures', 'label' => 'Critique'],
        $currentLevel === 2 => ['prise' => '30 min', 'resolution' => '4 heures', 'label' => 'Élevée'],
        $currentLevel === 3 => ['prise' => '1 heure', 'resolution' => '8 heures', 'label' => 'Normale'],
        default => ['prise' => '4 heures', 'resolution' => '24 heures', 'label' => 'Faible'],
    };
@endphp

<div class="ceet-priority-form-body">
    <div class="ceet-priority-form-grid">
        <div class="ceet-field-group">
            <label for="code" class="form-label">Code *</label>
            <input
                id="code"
                type="text"
                name="code"
                class="form-control @error('code') is-invalid @enderror"
                value="{{ old('code', $p->code ?? '') }}"
                placeholder="ex : P1"
                maxlength="50"
                required
            >
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="ceet-field-group">
            <label for="libelle" class="form-label">Libellé de priorité *</label>
            <input
                id="libelle"
                type="text"
                name="libelle"
                class="form-control @error('libelle') is-invalid @enderror"
                value="{{ old('libelle', $p->libelle ?? '') }}"
                placeholder="ex : Critique"
                maxlength="100"
                required
            >
            @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="ceet-field-group">
            <label for="niveau" class="form-label">Niveau hiérarchique *</label>
            <select
                id="niveau"
                name="niveau"
                class="form-select @error('niveau') is-invalid @enderror"
                required
            >
                @for ($level = 1; $level <= 4; $level++)
                    <option value="{{ $level }}" @selected((int) old('niveau', $p->niveau ?? 1) === $level)>
                        Niveau {{ $level }} {{ $level === 1 ? '(plus élevé)' : ($level === 4 ? '(plus faible)' : '') }}
                    </option>
                @endfor
            </select>
            @error('niveau')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="ceet-field-group">
            <label for="couleur" class="form-label">Couleur d’indication</label>
            <div class="ceet-priority-color-field">
                <input
                    id="couleur"
                    type="color"
                    name="couleur"
                    class="form-control form-control-color @error('couleur') is-invalid @enderror"
                    value="{{ old('couleur', $p->couleur ?? '#141b2b') }}"
                    title="Choisir une couleur"
                >
                <span>{{ old('couleur', $p->couleur ?? '#141b2b') }}</span>
            </div>
            @error('couleur')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="ceet-field-group">
        <label for="description" class="form-label">Description du seuil</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Décrivez les conditions d’utilisation de cette priorité."
        >{{ old('description', $p->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="ceet-priority-divider"></div>

    <section class="ceet-priority-sla-section" aria-label="Délais de réponse et résolution">
        <div>
            <h3>Délais de réponse & résolution</h3>
            <p>Référence opérationnelle affichée pour ce niveau. Les délais ne sont pas stockés dans la table actuelle.</p>
        </div>

        <div class="ceet-priority-sla-grid">
            <article>
                <span>Prise en charge max.</span>
                <strong>{{ $sla['prise'] }}</strong>
            </article>
            <article>
                <span>Résolution max.</span>
                <strong>{{ $sla['resolution'] }}</strong>
            </article>
            <article>
                <span>Classification</span>
                <strong>{{ $sla['label'] }}</strong>
            </article>
        </div>

        <div class="ceet-priority-info-box">
            <strong>Note système</strong>
            <span>
                Toute modification du libellé, du niveau ou de l’état impacte les prochains formulaires de déclaration d’incident.
            </span>
        </div>
    </section>
</div>
