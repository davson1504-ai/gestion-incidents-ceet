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
    $selectedSuperviseur = old('superviseur_id', $isSupervisor ? $currentUser?->id : null);

    $defaultStart = old('date_debut', now()->format('Y-m-d\\TH:i'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un incident - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/admin-dashboard.css',
        'resources/js/pages/admin-dashboard.js'
    ])
</head>

<body class="ceet-admin-dashboard-page ceet-incident-create-page">
    <div class="ceet-admin-shell" data-admin-dashboard>
        <div class="ceet-dashboard-overlay" data-dashboard-overlay></div>

        <aside class="ceet-admin-sidebar" data-dashboard-sidebar>
            <div class="ceet-admin-brand">
                <div class="ceet-admin-brand-logo">
                    <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
                </div>

                <div>
                    <h1>CEET Incidents</h1>
                    <p>Electrical Management</p>
                </div>
            </div>

            <nav class="ceet-admin-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-admin-nav-link'])
            </nav>

            <div class="ceet-admin-sidebar-user">
                <div class="ceet-admin-sidebar-user-main">
                    <div class="ceet-admin-avatar">{{ $initials }}</div>
                    <div>
                        <strong>{{ $roleName }}</strong>
                        <span>{{ $userEmail }}</span>
                    </div>
                </div>

                <form action="{{ $safeRoute('logout', [], '/logout') }}" method="POST" class="ceet-admin-logout-form">
                    @csrf
                    <button type="submit" class="ceet-admin-logout-button">
                        <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </aside>

        <header class="ceet-admin-topbar">
            <button type="button" class="ceet-admin-menu-btn" data-dashboard-sidebar-toggle aria-label="Ouvrir le menu" aria-expanded="false">
                <span class="material-symbols-outlined" aria-hidden="true">menu</span>
            </button>

            <form action="{{ $safeRoute('incidents.index', [], '/incidents') }}" method="GET" class="ceet-admin-search">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un incident, un départ..." autocomplete="off">
            </form>

            <div class="ceet-admin-top-actions">
                <a href="{{ $safeRoute('notifications.index', [], '/notifications') }}" class="ceet-admin-icon-btn" aria-label="Notifications">
                    <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                    <span class="ceet-admin-notification-dot"></span>
                </a>

                <a href="{{ $safeRoute('profile.edit', [], '/profile') }}" class="ceet-admin-icon-btn" aria-label="Profil">
                    <span class="material-symbols-outlined" aria-hidden="true">account_circle</span>
                </a>

                <div class="ceet-admin-top-divider"></div>

                <div class="ceet-admin-top-user">
                    <span>{{ $userName }}</span>
                    <div class="ceet-admin-avatar is-small">{{ $initials }}</div>
                </div>
            </div>
        </header>

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



                        <section class="ceet-incident-form-card is-status-card ceet-incident-status-preview">
                            <div class="ceet-incident-card-title">Statut initial</div>

                            <div class="ceet-incident-status-note">
                                <strong>OUVERT</strong>
                                <p>AFFECTE si un opérateur est affecté</p>
                            </div>
                        </section>
                        <section class="ceet-incident-form-card is-wide">
                            <div class="ceet-incident-card-title">Affectation</div>

                            <div class="ceet-incident-two-columns">
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

                                <div class="ceet-incident-field">
                                    <label for="superviseur_id">Superviseur</label>
                                    <select id="superviseur_id" name="superviseur_id">
                                        <option value="">Non affecté</option>
                                        @foreach($users as $agent)
                                            <option value="{{ $agent->id }}" @selected((string) $selectedSuperviseur === (string) $agent->id)>
                                                {{ $agent->name }} — {{ $agent->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('superviseur_id') <small>{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="ceet-incident-two-columns">
                                


                                

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
</body>
</html>
