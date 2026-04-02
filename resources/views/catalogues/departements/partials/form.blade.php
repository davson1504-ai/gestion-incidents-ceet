@php
    $dep = $departement ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Code *</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $dep->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label">Nom *</label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $dep->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Périmètre / Zone</label>
        <input type="text" name="zone" class="form-control @error('zone') is-invalid @enderror"
               value="{{ old('zone', $dep->zone ?? '') }}">
        @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Direction d'exploitation</label>
        <input type="text" name="direction_exploitation" class="form-control"
               value="{{ old('direction_exploitation', $dep->direction_exploitation ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Poste de répartition</label>
        <input type="text" name="poste_repartition" class="form-control"
               value="{{ old('poste_repartition', $dep->poste_repartition ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Poste source</label>
        <input type="text" name="poste_source" class="form-control"
               value="{{ old('poste_source', $dep->poste_source ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Transformateur</label>
        <input type="text" name="transformateur" class="form-control"
               value="{{ old('transformateur', $dep->transformateur ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Arrivée</label>
        <input type="text" name="arrivee" class="form-control"
               value="{{ old('arrivee', $dep->arrivee ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Charge max</label>
        <input type="number" step="0.01" name="charge_maximale" class="form-control"
               value="{{ old('charge_maximale', $dep->charge_maximale ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Unité</label>
        <input type="text" name="charge_unite" class="form-control"
               value="{{ old('charge_unite', $dep->charge_unite ?? 'A') }}">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="is_active" id="is_active"
                   {{ old('is_active', $dep->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Actif
            </label>
        </div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a href="{{ route('catalogues.departements.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</div>
