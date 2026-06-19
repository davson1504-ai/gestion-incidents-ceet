@php
    $cause = $cause ?? null;
    $types = $types ?? collect();
@endphp

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="code" class="form-label">Code *</label>
        <input
            id="code"
            name="code"
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $cause->code ?? '') }}"
            maxlength="50"
            required
        >
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-8">
        <label for="libelle" class="form-label">Libellé *</label>
        <input
            id="libelle"
            name="libelle"
            type="text"
            class="form-control @error('libelle') is-invalid @enderror"
            value="{{ old('libelle', $cause->libelle ?? '') }}"
            maxlength="150"
            required
        >
        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-8">
        <label for="type_incident_id" class="form-label">Type d’incident *</label>
        <select id="type_incident_id" name="type_incident_id" class="form-select @error('type_incident_id') is-invalid @enderror" required>
            <option value="">Sélectionner un type</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" @selected((string) old('type_incident_id', $cause->type_incident_id ?? '') === (string) $type->id)>
                    {{ $type->libelle }}
                </option>
            @endforeach
        </select>
        @error('type_incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="is_active" class="form-label">Statut *</label>
        <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
            <option value="1" @selected((string) old('is_active', $cause->is_active ?? '1') === '1')>Actif</option>
            <option value="0" @selected((string) old('is_active', $cause->is_active ?? '1') === '0')>Inactif</option>
        </select>
        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Décrivez la cause probable et son contexte d’utilisation."
        >{{ old('description', $cause->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
