@php $t = $type ?? null; @endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Code *</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $t->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label">Libellé *</label>
        <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
               value="{{ old('libelle', $t->libelle ?? '') }}" required>
        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Actif</label><br>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $t->is_active ?? true) ? 'checked' : '' }}>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $t->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="{{ route('catalogues.types.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</div>
