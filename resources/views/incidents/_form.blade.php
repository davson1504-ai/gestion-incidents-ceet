@php
    $incident = $incident ?? null;
    $departements = $departements ?? collect();
    $types = $types ?? ($typeIncidents ?? collect());
    $causes = $causes ?? collect();
    $priorites = $priorites ?? collect();
    $users = $users ?? ($operateurs ?? collect());

    $formatDateTime = function ($value): string {
        if (! $value) {
            return '';
        }

        try {
            return $value instanceof \Carbon\CarbonInterface
                ? $value->format('Y-m-d\TH:i')
                : \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    };

    $value = fn (string $field, $default = '') => old($field, $incident?->{$field} ?? $default);
@endphp

<div class="ceet-incident-form">
    <section class="ceet-form-section">
        <div class="ceet-form-section-header">
            <h2>Informations générales</h2>
            <p>Identification, classification et localisation de l’incident.</p>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <label for="titre" class="form-label">Titre *</label>
                <input
                    id="titre"
                    name="titre"
                    type="text"
                    class="form-control @error('titre') is-invalid @enderror"
                    value="{{ $value('titre') }}"
                    required
                >
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="priorite_id" class="form-label">Priorité *</label>
                <select id="priorite_id" name="priorite_id" class="form-select @error('priorite_id') is-invalid @enderror" required>
                    <option value="">Sélectionner</option>
                    @foreach ($priorites as $priorite)
                        <option value="{{ $priorite->id }}" @selected((string) $value('priorite_id') === (string) $priorite->id)>
                            {{ $priorite->libelle }}
                        </option>
                    @endforeach
                </select>
                @error('priorite_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="departement_id" class="form-label">Départ *</label>
                <select id="departement_id" name="departement_id" class="form-select @error('departement_id') is-invalid @enderror" required>
                    <option value="">Sélectionner un départ</option>
                    @foreach ($departements as $departement)
                        <option value="{{ $departement->id }}" @selected((string) $value('departement_id') === (string) $departement->id)>
                            {{ $departement->nom }}
                        </option>
                    @endforeach
                </select>
                @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="type_incident_id" class="form-label">Type d’incident *</label>
                <select id="type_incident_id" name="type_incident_id" class="form-select @error('type_incident_id') is-invalid @enderror" required>
                    <option value="">Sélectionner un type</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected((string) $value('type_incident_id') === (string) $type->id)>
                            {{ $type->libelle }}
                        </option>
                    @endforeach
                </select>
                @error('type_incident_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="cause_id" class="form-label">Cause probable *</label>
                <select id="cause_id" name="cause_id" class="form-select @error('cause_id') is-invalid @enderror" required>
                    <option value="">Sélectionner une cause</option>
                    @foreach ($causes as $cause)
                        <option value="{{ $cause->id }}" @selected((string) $value('cause_id') === (string) $cause->id)>
                            {{ $cause->libelle }}
                        </option>
                    @endforeach
                </select>
                @error('cause_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="localisation" class="form-label">Localisation</label>
                <input
                    id="localisation"
                    name="localisation"
                    type="text"
                    class="form-control @error('localisation') is-invalid @enderror"
                    value="{{ $value('localisation') }}"
                    placeholder="Poste, quartier, ligne ou repère terrain"
                >
                @error('localisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ $value('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </section>

    <section class="ceet-form-section">
        <div class="ceet-form-section-header">
            <h2>Chronologie et affectation</h2>
            <p>Dates d’exploitation et agents rattachés au traitement.</p>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="date_debut" class="form-label">Date de début *</label>
                <input
                    id="date_debut"
                    name="date_debut"
                    type="datetime-local"
                    class="form-control @error('date_debut') is-invalid @enderror"
                    value="{{ old('date_debut', $formatDateTime($incident?->date_debut)) }}"
                    required
                >
                @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="date_fin" class="form-label">Date de fin</label>
                <input
                    id="date_fin"
                    name="date_fin"
                    type="datetime-local"
                    class="form-control @error('date_fin') is-invalid @enderror"
                    value="{{ old('date_fin', $formatDateTime($incident?->date_fin)) }}"
                >
                @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="responsable_id" class="form-label">Responsable terrain</label>
                <select id="responsable_id" name="responsable_id" class="form-select @error('responsable_id') is-invalid @enderror">
                    <option value="">Non affecté</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) $value('responsable_id') === (string) $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('responsable_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>


            @if ($incident)
                <div class="col-12">
                    <div class="ceet-readonly-status">
                        <span>Statut actuel</span>
                        <strong>{{ $incident->status?->libelle ?? 'Non défini' }}</strong>
                    </div>
                    <input type="hidden" name="status_id" value="{{ old('status_id', $incident->status_id) }}">
                </div>
            @endif
        </div>
    </section>


    <footer class="ceet-form-actions ceet-incident-form-actions">
        <a href="{{ route('incidents.index') }}" class="ceet-incident-btn is-secondary">Annuler</a>
        <button type="submit" class="ceet-incident-btn is-primary">
            {{ $incident ? 'Enregistrer les modifications' : 'Créer l’incident' }}
        </button>
    </footer>
</div>
