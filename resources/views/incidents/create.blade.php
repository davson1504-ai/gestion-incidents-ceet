@extends('layouts.app')

@section('title', 'Créer un incident')

@section('page_css')
    @vite([
        'resources/css/app.css',
        'resources/css/pages/incidents-create.css'
    ])
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    $isAdmin = $currentUser?->isAdmin() ?? false;
    $isSupervisor = $currentUser?->isSuperviseur() ?? false;

    $userName = $currentUser?->name ?? 'Utilisateur CEET';
    $userEmail = $currentUser?->email ?? 'agent@ceet.tg';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames')
        ? strtoupper($currentUser->getRoleNames()->first() ?: 'UTILISATEUR')
        : 'UTILISATEUR';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'CE');

    $safeRoute = function (string $name, array $params = [], string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $departements = collect($departements ?? []);
    $types = collect($types ?? ($typeIncidents ?? []));
    $causes = collect($causes ?? []);
    $priorites = collect($priorites ?? []);
    $users = collect($users ?? ($operateurs ?? []));

    $selectedType = old('type_incident_id');
    $selectedCause = old('cause_id');
    $selectedDepartement = old('departement_id');
    $selectedPriorite = old('priorite_id');
    $selectedResponsable = old('responsable_id');
$defaultStart = old('date_debut', now()->format('Y-m-d\\TH:i'));
@endphp

@section('content')
<div class="ceet-admin-dashboard-page ceet-incident-create-page" data-admin-dashboard>
<main class="ceet-admin-main ceet-incident-create-main">
    <div class="ceet-incident-create-container">
        <nav class="ceet-user-create-breadcrumb" aria-label="Fil d'Ariane">
            <a href="{{ $safeRoute('dashboard', [], '/dashboard') }}">Dashboard</a>
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            <a href="{{ $safeRoute('incidents.index', [], '/incidents') }}">Incidents</a>
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            <strong>Nouveau</strong>
        </nav>

        <section class="ceet-incident-create-header">
            <h2>Créer un incident</h2>
            <p>
                Enregistrez toutes les informations utiles : départ concerné, type d’incident,
                cause probable, priorité, localisation, chronologie, affectation et premières actions terrain.
            </p>
        </section>

        @if(session('success'))
            <div class="ceet-incident-alert is-success">
                <strong>Succès.</strong>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="ceet-incident-alert">
                <strong>Formulaire incomplet.</strong>
                <span>Corrigez les champs signalés avant d’enregistrer l’incident.</span>
            </div>
        @endif

        <form action="{{ $safeRoute('incidents.store', [], '/incidents') }}" method="POST" class="ceet-incident-create-form" data-incident-create-form>
            @csrf

            <div class="ceet-incident-form-grid">
                <section class="ceet-incident-form-card">
                    <div class="ceet-incident-card-title">Identification</div>

                    <div class="ceet-incident-field">
                        <label for="titre">Titre de l’incident *</label>
                        <input id="titre" name="titre" type="text" value="{{ old('titre') }}" placeholder="Ex: Coupure BT au poste Adamavo" required>
                        @error('titre') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="ceet-incident-field">
                        <label for="departement_id">Départ / secteur *</label>
                        <select id="departement_id" name="departement_id" required>
                            <option value="">Sélectionner un départ</option>
                            @foreach($departements as $departement)
                                <option value="{{ $departement->id }}" @selected((string) $selectedDepartement === (string) $departement->id)>
                                    {{ $departement->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('departement_id') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="ceet-incident-field">
                        <label for="localisation">Localisation précise *</label>
                        <input id="localisation" name="localisation" type="text" value="{{ old('localisation') }}" placeholder="Quartier, poste, ligne, repère terrain" required>
                        @error('localisation') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="ceet-incident-field">
                        <label for="description">Description détaillée *</label>
                        <textarea id="description" name="description" rows="6" placeholder="Décrivez les symptômes, impacts clients, zone touchée, observations terrain..." required>{{ old('description') }}</textarea>
                        @error('description') <small>{{ $message }}</small> @enderror
                    </div>
                </section>

                <section class="ceet-incident-form-card">
                    <div class="ceet-incident-card-title">Classification</div>

                    <div class="ceet-incident-field">
                        <label for="type_incident_id">Type d’incident *</label>
                        <select id="type_incident_id" name="type_incident_id" required data-type-select>
                            <option value="">Sélectionner un type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" @selected((string) $selectedType === (string) $type->id)>
                                    {{ $type->libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_incident_id') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="ceet-incident-field">
                        <label for="cause_id">Cause probable *</label>
                        <select id="cause_id" name="cause_id" required data-cause-select data-selected-cause="{{ $selectedCause }}">
                            <option value="">Sélectionner une cause</option>
                            @foreach($causes as $cause)
                                <option value="{{ $cause->id }}" data-type-id="{{ $cause->type_incident_id }}" @selected((string) $selectedCause === (string) $cause->id)>
                                    {{ $cause->libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('cause_id') <small>{{ $message }}</small> @enderror
                        <span class="ceet-incident-help">La cause est filtrée selon le type sélectionné.</span>
                    </div>

                    <div class="ceet-incident-field">
                        <label for="priorite_id">Priorité *</label>
                        <select id="priorite_id" name="priorite_id" required>
                            <option value="">Sélectionner une priorité</option>
                            @foreach($priorites as $priorite)
                                <option value="{{ $priorite->id }}" @selected((string) $selectedPriorite === (string) $priorite->id)>
                                    {{ $priorite->libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('priorite_id') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="ceet-incident-two-columns">
                        <div class="ceet-incident-field">
                            <label for="date_debut">Début incident *</label>
                            <input id="date_debut" name="date_debut" type="datetime-local" value="{{ $defaultStart }}" required>
                            @error('date_debut') <small>{{ $message }}</small> @enderror
                        </div>

                        <div class="ceet-incident-field">
                            <label for="date_fin">Fin incident</label>
                            <input id="date_fin" name="date_fin" type="datetime-local" value="{{ old('date_fin') }}">
                            @error('date_fin') <small>{{ $message }}</small> @enderror
                        </div>
                    </div>
                </section>
<section class="ceet-incident-form-card is-wide">
                    <div class="ceet-incident-card-title">Affectation</div>

                    <div class=\"ceet-incident-two-columns ceet-incident-affectation-single\">
                        <div class="ceet-incident-field">
                            <label for="responsable_id">Responsable terrain</label>
                            <select id="responsable_id" name="responsable_id">
                                <option value="">Non affecté</option>
                                @foreach($users as $agent)
                                    <option value="{{ $agent->id }}" @selected((string) $selectedResponsable === (string) $agent->id)>
                                        {{ $agent->name }} — {{ $agent->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('responsable_id') <small>{{ $message }}</small> @enderror
                            <span class="ceet-incident-help">Si un responsable est choisi, l’incident passe directement au statut affecté.</span>
                        </div>
</div>
</section>
            </div>

            <footer class="ceet-incident-form-actions">
                <a href="{{ $safeRoute('incidents.index', [], '/incidents') }}" class="ceet-incident-btn is-secondary">Annuler</a>
                <button type="submit" class="ceet-incident-btn is-primary">
                    <span class="material-symbols-outlined" aria-hidden="true">save</span>
                    Créer l’incident
                </button>
            </footer>
        </form>
    </div>
</main>
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/pages/incidents-create.js'
    ])

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.querySelector('[data-type-select]');
            const causeSelect = document.querySelector('[data-cause-select]');

            if (!typeSelect || !causeSelect) {
                return;
            }

            const placeholder = causeSelect.querySelector('option[value=""]');
            const options = Array.from(causeSelect.querySelectorAll('option[data-type-id]'));

            function filterCauses() {
                const typeId = typeSelect.value;
                let visibleCount = 0;

                options.forEach(function (option) {
                    const match = !typeId || option.dataset.typeId === typeId;
                    option.hidden = !match;
                    option.disabled = !match;
                    if (match) visibleCount++;
                });

                if (causeSelect.value) {
                    const selected = causeSelect.options[causeSelect.selectedIndex];
                    if (selected && selected.disabled) {
                        causeSelect.value = '';
                    }
                }

                if (placeholder) {
                    placeholder.textContent = visibleCount
                        ? 'Sélectionner une cause'
                        : 'Aucune cause disponible pour ce type';
                }
            }

            typeSelect.addEventListener('change', filterCauses);
            filterCauses();
        });
    </script>
@endsection
