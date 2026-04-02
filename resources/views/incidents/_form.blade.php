@php
    $isEdit = isset($incident);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Titre *</label>
        <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
               value="{{ old('titre', $incident->titre ?? '') }}" required>
        @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Département *</label>
        <select name="departement_id" class="form-select @error('departement_id') is-invalid @enderror" required>
            <option value="">Sélectionner</option>
            @foreach($departements as $dep)
                <option value="{{ $dep->id }}" @selected(old('departement_id', $incident->departement_id ?? '') == $dep->id)>
                    {{ $dep->nom }}
                </option>
            @endforeach
        </select>
        @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Type d'incident *</label>
        <select name="type_incident_id" class="form-select @error('type_incident_id') is-invalid @enderror" required>
            <option value="">Sélectionner</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" @selected(old('type_incident_id', $incident->type_incident_id ?? '') == $type->id)>
                    {{ $type->libelle }}
                </option>
            @endforeach
        </select>
        @error('type_incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Cause</label>
        <select name="cause_id" class="form-select @error('cause_id') is-invalid @enderror">
            <option value="">-- Aucune --</option>
            @foreach($causes as $cause)
                <option value="{{ $cause->id }}" @selected(old('cause_id', $incident->cause_id ?? '') == $cause->id)>
                    {{ $cause->libelle }}
                </option>
            @endforeach
        </select>
        @error('cause_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Statut *</label>
        <select name="status_id" class="form-select @error('status_id') is-invalid @enderror" required>
            @foreach($statuts as $statut)
                <option value="{{ $statut->id }}" @selected(old('status_id', $incident->status_id ?? '') == $statut->id)>
                    {{ $statut->libelle }}
                </option>
            @endforeach
        </select>
        @error('status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Priorité *</label>
        <select name="priorite_id" class="form-select @error('priorite_id') is-invalid @enderror" required>
            @foreach($priorites as $priorite)
                <option value="{{ $priorite->id }}" @selected(old('priorite_id', $incident->priorite_id ?? '') == $priorite->id)>
                    {{ $priorite->libelle }}
                </option>
            @endforeach
        </select>
        @error('priorite_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Localisation</label>
        <input type="text" name="localisation" class="form-control @error('localisation') is-invalid @enderror"
               value="{{ old('localisation', $incident->localisation ?? '') }}">
        @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Date début *</label>
        <input type="datetime-local" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
               value="{{ old('date_debut', isset($incident) ? $incident->date_debut?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
        @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Date fin</label>
        <input type="datetime-local" name="date_fin" class="form-control @error('date_fin') is-invalid @enderror"
               value="{{ old('date_fin', isset($incident) && $incident->date_fin ? $incident->date_fin->format('Y-m-d\TH:i') : '') }}">
        @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Responsable terrain</label>
        <select name="responsable_id" class="form-select @error('responsable_id') is-invalid @enderror">
            <option value="">-- Non assigné --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('responsable_id', $incident->responsable_id ?? '') == $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        @error('responsable_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Superviseur</label>
        <select name="superviseur_id" class="form-select @error('superviseur_id') is-invalid @enderror">
            <option value="">-- Non assigné --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('superviseur_id', $incident->superviseur_id ?? '') == $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        @error('superviseur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $incident->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Actions menées</label>
        <textarea name="actions_menees" rows="3" class="form-control @error('actions_menees') is-invalid @enderror">{{ old('actions_menees', $incident->actions_menees ?? '') }}</textarea>
        @error('actions_menees')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Résumé de résolution</label>
        <textarea name="resolution_summary" rows="3" class="form-control @error('resolution_summary') is-invalid @enderror">{{ old('resolution_summary', $incident->resolution_summary ?? '') }}</textarea>
        @error('resolution_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 d-flex justify-content-between align-items-center pt-3">
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Mettre à jour' : 'Créer l\'incident' }}
        </button>
    </div>
</div>
