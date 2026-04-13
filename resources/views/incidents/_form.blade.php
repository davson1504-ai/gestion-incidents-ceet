@php
    $isEdit = isset($incident);
    $selectedCauseId = old('cause_id', $incident->cause_id ?? '');
    $selectedStatusId = old('status_id', $incident->status_id ?? $statuts->first()?->id);
    $finalStatusIds = $statuts->where('is_final', true)->pluck('id')->values();
    $activeCauses = $causes->map(fn ($cause) => ['id' => $cause->id, 'libelle' => $cause->libelle])->values();
@endphp

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <label class="form-label">Titre *</label>
        <input
            type="text"
            name="titre"
            class="form-control @error('titre') is-invalid @enderror"
            value="{{ old('titre', $incident->titre ?? '') }}"
            required
        >
        @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-6">
        <label class="form-label">Départ *</label>
        <select
            name="departement_id"
            class="form-select js-tom-select @error('departement_id') is-invalid @enderror"
            data-placeholder="Sélectionner un départ"
            required
        >
            <option value="">Sélectionner</option>
            @foreach($departements as $departement)
                <option value="{{ $departement->id }}" @selected(old('departement_id', $incident->departement_id ?? '') == $departement->id)>{{ $departement->nom }}</option>
            @endforeach
        </select>
        @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-6">
        <label class="form-label">Type d’incident *</label>
        <select
            id="incident-type-select"
            name="type_incident_id"
            class="form-select js-tom-select @error('type_incident_id') is-invalid @enderror"
            data-placeholder="Sélectionner un type"
            data-causes-url="{{ route('incidents.causes.by-type', ['type' => '__TYPE__']) }}"
            required
        >
            <option value="">Sélectionner</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" @selected(old('type_incident_id', $incident->type_incident_id ?? '') == $type->id)>{{ $type->libelle }}</option>
            @endforeach
        </select>
        @error('type_incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-6">
        <div class="d-flex justify-content-between align-items-center">
            <label class="form-label mb-0">Cause</label>
            <span id="incident-cause-loading" class="d-none small text-muted">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Chargement
            </span>
        </div>
        <select
            id="incident-cause-select"
            name="cause_id"
            class="form-select js-tom-select @error('cause_id') is-invalid @enderror"
            data-placeholder="Sélectionner une cause"
            data-selected-cause="{{ $selectedCauseId }}"
            data-all-causes='@json($activeCauses)'
        >
            <option value="">Aucune</option>
            @foreach($causes as $cause)
                <option value="{{ $cause->id }}" @selected($selectedCauseId == $cause->id)>{{ $cause->libelle }}</option>
            @endforeach
        </select>
        @error('cause_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-4">
        <label class="form-label">Statut *</label>
        <select
            id="incident-status-select"
            name="status_id"
            class="form-select js-tom-select @error('status_id') is-invalid @enderror"
            data-placeholder="Sélectionner un statut"
            data-final-ids='@json($finalStatusIds)'
            required
        >
            @foreach($statuts as $statut)
                <option value="{{ $statut->id }}" @selected($selectedStatusId == $statut->id)>{{ $statut->libelle }}</option>
            @endforeach
        </select>
        @error('status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-4">
        <label class="form-label">Priorité *</label>
        <select
            name="priorite_id"
            class="form-select js-tom-select @error('priorite_id') is-invalid @enderror"
            data-placeholder="Sélectionner une priorité"
            required
        >
            @foreach($priorites as $priorite)
                <option value="{{ $priorite->id }}" @selected(old('priorite_id', $incident->priorite_id ?? '') == $priorite->id)>{{ $priorite->libelle }}</option>
            @endforeach
        </select>
        @error('priorite_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-4">
        <label class="form-label">Localisation</label>
        <input
            type="text"
            name="localisation"
            class="form-control @error('localisation') is-invalid @enderror"
            value="{{ old('localisation', $incident->localisation ?? '') }}"
        >
        @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-6">
        <label class="form-label">Date début *</label>
        <input
            id="incident-date-debut"
            type="datetime-local"
            name="date_debut"
            class="form-control @error('date_debut') is-invalid @enderror"
            value="{{ old('date_debut', isset($incident) ? $incident->date_debut?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
            required
        >
        @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-6">
        <label class="form-label">Date fin</label>
        <input
            id="incident-date-fin"
            type="datetime-local"
            name="date_fin"
            class="form-control @error('date_fin') is-invalid @enderror"
            value="{{ old('date_fin', isset($incident) && $incident->date_fin ? $incident->date_fin->format('Y-m-d\TH:i') : '') }}"
        >
        @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="alert alert-light border small mb-0" id="incident-duration-preview">Durée estimée : -</div>
    </div>

    <div class="col-12 col-lg-6">
        <label class="form-label">Responsable terrain</label>
        <select
            name="responsable_id"
            class="form-select js-tom-select @error('responsable_id') is-invalid @enderror"
            data-placeholder="Sélectionner un responsable"
        >
            <option value="">Non assigné</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('responsable_id', $incident->responsable_id ?? '') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
        @error('responsable_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-lg-6">
        <label class="form-label">Superviseur</label>
        <select
            name="superviseur_id"
            class="form-select js-tom-select @error('superviseur_id') is-invalid @enderror"
            data-placeholder="Sélectionner un superviseur"
        >
            <option value="">Non assigné</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('superviseur_id', $incident->superviseur_id ?? '') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
            @endforeach
        </select>
        @error('superviseur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $incident->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Actions menées</label>
        <textarea name="actions_menees" rows="4" class="form-control @error('actions_menees') is-invalid @enderror">{{ old('actions_menees', $incident->actions_menees ?? '') }}</textarea>
        @error('actions_menees')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Résumé de résolution</label>
        <textarea name="resolution_summary" rows="4" class="form-control @error('resolution_summary') is-invalid @enderror">{{ old('resolution_summary', $incident->resolution_summary ?? '') }}</textarea>
        @error('resolution_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 d-flex justify-content-between gap-2">
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-danger">{{ $isEdit ? 'Mettre à jour l’incident' : 'Créer l’incident' }}</button>
    </div>
</div>

@once
    @vite('resources/js/incident-form.js')
@endonce
