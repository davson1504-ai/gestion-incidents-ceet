@php
    $departement = $departement ?? null;
@endphp

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="code" class="form-label">Code *</label>
        <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $departement->code ?? '') }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-8">
        <label for="nom" class="form-label">Nom du départ *</label>
        <input id="nom" name="nom" type="text" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $departement->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="zone" class="form-label">Zone</label>
        <input id="zone" name="zone" type="text" class="form-control @error('zone') is-invalid @enderror" value="{{ old('zone', $departement->zone ?? '') }}">
        @error('zone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="direction_exploitation" class="form-label">Direction d’exploitation</label>
        <input id="direction_exploitation" name="direction_exploitation" type="text" class="form-control @error('direction_exploitation') is-invalid @enderror" value="{{ old('direction_exploitation', $departement->direction_exploitation ?? '') }}">
        @error('direction_exploitation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="poste_repartition" class="form-label">Poste de répartition</label>
        <input id="poste_repartition" name="poste_repartition" type="text" class="form-control @error('poste_repartition') is-invalid @enderror" value="{{ old('poste_repartition', $departement->poste_repartition ?? '') }}">
        @error('poste_repartition')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="poste_source" class="form-label">Poste source</label>
        <input id="poste_source" name="poste_source" type="text" class="form-control @error('poste_source') is-invalid @enderror" value="{{ old('poste_source', $departement->poste_source ?? '') }}">
        @error('poste_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="transformateur" class="form-label">Transformateur</label>
        <input id="transformateur" name="transformateur" type="text" class="form-control @error('transformateur') is-invalid @enderror" value="{{ old('transformateur', $departement->transformateur ?? '') }}">
        @error('transformateur')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="arrivee" class="form-label">Arrivée</label>
        <input id="arrivee" name="arrivee" type="text" class="form-control @error('arrivee') is-invalid @enderror" value="{{ old('arrivee', $departement->arrivee ?? '') }}">
        @error('arrivee')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-8 col-md-3">
        <label for="charge_maximale" class="form-label">Charge maximale</label>
        <input id="charge_maximale" name="charge_maximale" type="number" step="0.01" min="0" class="form-control @error('charge_maximale') is-invalid @enderror" value="{{ old('charge_maximale', $departement->charge_maximale ?? '') }}">
        @error('charge_maximale')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-4 col-md-1">
        <label for="charge_unite" class="form-label">Unité</label>
        <input id="charge_unite" name="charge_unite" type="text" class="form-control @error('charge_unite') is-invalid @enderror" value="{{ old('charge_unite', $departement->charge_unite ?? 'MW') }}">
        @error('charge_unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $departement->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <input type="hidden" name="is_active" value="0">
        <label class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $departement->is_active ?? true))>
            <span class="form-check-label">Départ actif</span>
        </label>
        @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>
