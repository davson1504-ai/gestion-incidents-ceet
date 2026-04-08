@php $p = $priorite ?? null; @endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Code *</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $p->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-5">
        <label class="form-label">Libelle *</label>
        <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
               value="{{ old('libelle', $p->libelle ?? '') }}" required>
        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Niveau *</label>
        <input type="number" min="1" name="niveau" class="form-control @error('niveau') is-invalid @enderror"
               value="{{ old('niveau', $p->niveau ?? 3) }}" required>
        @error('niveau')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label class="form-label">Couleur</label>
        <input type="color" name="couleur" class="form-control form-control-color @error('couleur') is-invalid @enderror"
               value="{{ old('couleur', $p->couleur ?? '#6c757d') }}" title="Choisir une couleur">
        @error('couleur')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $p->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $p->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Actif</label>
        </div>
    </div>

    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="{{ route('catalogues.priorites.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</div>

