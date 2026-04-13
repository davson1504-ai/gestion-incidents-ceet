@php
    $dep = $departement ?? null;
@endphp

<div class="row g-3">
    <div class="col-12 col-md-3">
        <label class="form-label">Code *</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $dep->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-5">
        <label class="form-label">Nom *</label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $dep->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Périmètre / zone</label>
        <input type="text" name="zone" class="form-control @error('zone') is-invalid @enderror" value="{{ old('zone', $dep->zone ?? '') }}">
        @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Direction d'exploitation</label>
        <input type="text" name="direction_exploitation" class="form-control @error('direction_exploitation') is-invalid @enderror" value="{{ old('direction_exploitation', $dep->direction_exploitation ?? '') }}">
        @error('direction_exploitation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Poste de répartition</label>
        <input type="text" name="poste_repartition" class="form-control @error('poste_repartition') is-invalid @enderror" value="{{ old('poste_repartition', $dep->poste_repartition ?? '') }}">
        @error('poste_repartition')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Poste source</label>
        <input type="text" name="poste_source" class="form-control @error('poste_source') is-invalid @enderror" value="{{ old('poste_source', $dep->poste_source ?? '') }}">
        @error('poste_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Charge maximale</label>
        <input type="number" step="0.01" name="charge_maximale" class="form-control @error('charge_maximale') is-invalid @enderror" value="{{ old('charge_maximale', $dep->charge_maximale ?? '') }}">
        @error('charge_maximale')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Unité de charge</label>
        <select name="charge_unite" class="form-select @error('charge_unite') is-invalid @enderror">
            @foreach(['A', 'kW', 'MVA'] as $unite)
                <option value="{{ $unite }}" @selected(old('charge_unite', $dep->charge_unite ?? 'A') === $unite)>{{ $unite }}</option>
            @endforeach
        </select>
        @error('charge_unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Transformateur</label>
        <input type="text" name="transformateur" class="form-control @error('transformateur') is-invalid @enderror" value="{{ old('transformateur', $dep->transformateur ?? '') }}">
        @error('transformateur')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Arrivée</label>
        <input type="text" name="arrivee" class="form-control @error('arrivee') is-invalid @enderror" value="{{ old('arrivee', $dep->arrivee ?? '') }}">
        @error('arrivee')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label class="form-label">Description technique</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $dep->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="is_active" id="is_active" {{ old('is_active', $dep->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Départ actif</label>
        </div>
    </div>
    <div class="col-12 d-flex justify-content-between gap-2">
        <a href="{{ route('catalogues.departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-danger" type="submit">Enregistrer</button>
    </div>
</div>
