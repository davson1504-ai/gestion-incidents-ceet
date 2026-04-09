@php
    $isEdit = isset($incident);
    $selectedCauseId = old('cause_id', $incident->cause_id ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-6">
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

    <div class="col-md-6">
        <label class="form-label">Departement *</label>
        <select
            name="departement_id"
            class="form-select js-tom-select @error('departement_id') is-invalid @enderror"
            data-placeholder="Selectionner un departement"
            required
        >
            <option value="">Selectionner</option>
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
        <select
            id="incident-type-select"
            name="type_incident_id"
            class="form-select js-tom-select @error('type_incident_id') is-invalid @enderror"
            data-placeholder="Selectionner un type"
            required
        >
            <option value="">Selectionner</option>
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
        <select
            id="incident-cause-select"
            name="cause_id"
            class="form-select js-tom-select @error('cause_id') is-invalid @enderror"
            data-placeholder="Selectionner une cause"
            data-selected-cause="{{ $selectedCauseId }}"
            data-endpoint-template="{{ route('incidents.causes.by-type', ['type' => '__TYPE__']) }}"
        >
            <option value="">Aucune</option>
            @foreach($causes as $cause)
                <option value="{{ $cause->id }}" @selected($selectedCauseId == $cause->id)>
                    {{ $cause->libelle }}
                </option>
            @endforeach
        </select>
        @error('cause_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Statut *</label>
        <select
            name="status_id"
            class="form-select js-tom-select @error('status_id') is-invalid @enderror"
            data-placeholder="Selectionner un statut"
            required
        >
            @foreach($statuts as $statut)
                <option value="{{ $statut->id }}" @selected(old('status_id', $incident->status_id ?? '') == $statut->id)>
                    {{ $statut->libelle }}
                </option>
            @endforeach
        </select>
        @error('status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Priorite *</label>
        <select
            name="priorite_id"
            class="form-select js-tom-select @error('priorite_id') is-invalid @enderror"
            data-placeholder="Selectionner une priorite"
            required
        >
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
        <input
            type="text"
            name="localisation"
            class="form-control @error('localisation') is-invalid @enderror"
            value="{{ old('localisation', $incident->localisation ?? '') }}"
        >
        @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Date debut *</label>
        <input
            type="datetime-local"
            name="date_debut"
            class="form-control @error('date_debut') is-invalid @enderror"
            value="{{ old('date_debut', isset($incident) ? $incident->date_debut?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
            required
        >
        @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Date fin</label>
        <input
            type="datetime-local"
            name="date_fin"
            class="form-control @error('date_fin') is-invalid @enderror"
            value="{{ old('date_fin', isset($incident) && $incident->date_fin ? $incident->date_fin->format('Y-m-d\TH:i') : '') }}"
        >
        @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Responsable terrain</label>
        <select
            name="responsable_id"
            class="form-select js-tom-select @error('responsable_id') is-invalid @enderror"
            data-placeholder="Selectionner un responsable"
        >
            <option value="">Non assigne</option>
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
        <select
            name="superviseur_id"
            class="form-select js-tom-select @error('superviseur_id') is-invalid @enderror"
            data-placeholder="Selectionner un superviseur"
        >
            <option value="">Non assigne</option>
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
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $incident->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Actions menees</label>
        <textarea name="actions_menees" rows="3" class="form-control @error('actions_menees') is-invalid @enderror">{{ old('actions_menees', $incident->actions_menees ?? '') }}</textarea>
        @error('actions_menees')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Resume de resolution</label>
        <textarea name="resolution_summary" rows="3" class="form-control @error('resolution_summary') is-invalid @enderror">{{ old('resolution_summary', $incident->resolution_summary ?? '') }}</textarea>
        @error('resolution_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 pt-2 d-flex justify-content-between gap-2">
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Mettre a jour' : 'Creer incident' }}</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.getElementById('incident-type-select');
        const causeSelect = document.getElementById('incident-cause-select');

        if (!typeSelect || !causeSelect) {
            return;
        }

        const endpointTemplate = causeSelect.dataset.endpointTemplate;
        const initialCauseId = String(causeSelect.dataset.selectedCause || causeSelect.value || '');

        const setDisabled = (disabled) => {
            causeSelect.disabled = disabled;
            if (causeSelect.tomselect) {
                if (disabled) {
                    causeSelect.tomselect.disable();
                } else {
                    causeSelect.tomselect.enable();
                }
            }
        };

        const setOptions = (items, selectedId = '') => {
            const options = [{ value: '', text: 'Aucune' }, ...items.map((item) => ({
                value: String(item.id),
                text: item.libelle
            }))];

            if (causeSelect.tomselect) {
                const control = causeSelect.tomselect;
                control.clear(true);
                control.clearOptions();
                control.addOptions(options);
                control.refreshOptions(false);
                control.setValue(String(selectedId || ''), true);
                return;
            }

            causeSelect.innerHTML = '';
            options.forEach((option) => {
                const element = document.createElement('option');
                element.value = option.value;
                element.textContent = option.text;
                if (option.value === String(selectedId || '')) {
                    element.selected = true;
                }
                causeSelect.appendChild(element);
            });
        };

        const loadCausesByType = async (typeId, selectedId = '') => {
            if (!typeId) {
                setOptions([], '');
                setDisabled(true);
                return;
            }

            setDisabled(true);
            setOptions([{ id: '', libelle: 'Chargement...' }], '');

            try {
                const endpoint = endpointTemplate.replace('__TYPE__', encodeURIComponent(typeId));
                const response = await fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Unable to fetch causes');
                }

                const causes = await response.json();
                setOptions(causes, selectedId);
                setDisabled(false);
            } catch (error) {
                setOptions([], '');
                setDisabled(true);
            }
        };

        typeSelect.addEventListener('change', () => {
            loadCausesByType(typeSelect.value, '');
        });

        if (typeSelect.value) {
            loadCausesByType(typeSelect.value, initialCauseId);
        } else if (!initialCauseId) {
            setOptions([], '');
            setDisabled(true);
        }
    });
</script>
