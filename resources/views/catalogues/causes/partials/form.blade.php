@php
    $c = $cause ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Code *</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $c->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label">Libellé *</label>
        <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
               value="{{ old('libelle', $c->libelle ?? '') }}" required>
        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Type d'incident *</label>
        <select name="type_incident_id" class="form-select @error('type_incident_id') is-invalid @enderror" required>
            <option value="">Sélectionner</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" @selected(old('type_incident_id', $c->type_incident_id ?? '') == $type->id)>
                    {{ $type->libelle }}
                </option>
            @endforeach
        </select>
        @error('type_incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $c->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $c->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Actif</label>
        </div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="{{ route('catalogues.causes.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</div>
