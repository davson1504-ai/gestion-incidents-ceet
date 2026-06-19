@php
    use Illuminate\Support\Str;

    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Jean Dupont'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'JD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrator';
    $selectedColor = old('couleur', $statut->couleur ?: '#3b82f6');
    $isFinal = (bool) old('is_final', $statut->is_final);
    $isActive = (bool) old('is_active', $statut->is_active);
    $badgeColors = collect(['#3b82f6', '#f59e0b', '#10b981', '#64748b', '#f43f5e'])
        ->when($selectedColor, fn ($items) => $items->contains($selectedColor) ? $items : $items->prepend($selectedColor))
        ->unique()
        ->values();

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
        ['label' => 'Users', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'System Status', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')],
        ['label' => 'Catalogs', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Modifier le Statut - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-status-edit-page">
    <aside class="ceet-status-edit-sidebar">
        <div class="ceet-status-edit-brand">
            <strong>CEET Incidents</strong>
            <span>Electrical Management</span>
        </div>

        <nav class="ceet-status-edit-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-status-edit-nav-link'])
            </nav>

        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="ceet-status-edit-user">
            <span>{{ $initials }}</span>
            <div>
                <strong>{{ $fullName }}</strong>
                <small>{{ $roleName }}</small>
            </div>
        </a>
    </aside>

    <header class="ceet-status-edit-topbar">
        <label class="ceet-status-edit-search">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <input type="search" placeholder="Rechercher...">
        </label>

        <div class="ceet-status-edit-toolbar">
            <button type="button" aria-label="Notifications">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <button type="button" aria-label="Aide">
                <span class="material-symbols-outlined" aria-hidden="true">help</span>
            </button>
            <span></span>
            <strong>Catalogs / Workflow</strong>
        </div>
    </header>

    <main class="ceet-status-edit-main">
        <form method="POST" action="{{ route('catalogues.statuts.update', $statut) }}" class="ceet-status-edit-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="code" value="{{ old('code', $statut->code) }}">

            <section class="ceet-status-edit-head">
                <div>
                    <h1>Modifier le Statut</h1>
                    <p>Configuration des &eacute;tapes du workflow op&eacute;rationnel.</p>
                </div>
                <div class="ceet-status-edit-actions">
                    <a href="{{ route('catalogues.statuts.index') }}">Annuler</a>
                    <button type="submit">Enregistrer</button>
                </div>
            </section>

            @if($errors->any())
                <div class="ceet-alert ceet-alert-danger" role="alert">
                    Veuillez corriger les champs signal&eacute;s avant d'enregistrer.
                </div>
            @endif

            <div class="ceet-status-edit-grid">
                <div class="ceet-status-edit-left">
                    <section class="ceet-status-edit-card">
                        <h2>Informations G&eacute;n&eacute;rales</h2>
                        <div class="ceet-status-edit-divider"></div>

                        <div class="ceet-status-edit-field-row">
                            <label>
                                <span>Libell&eacute; du statut</span>
                                <input type="text" name="libelle" value="{{ old('libelle', $statut->libelle) }}" required>
                                @error('libelle')<small>{{ $message }}</small>@enderror
                            </label>

                            <label>
                                <span>Ordre d'affichage</span>
                                <input type="number" min="0" name="ordre" value="{{ old('ordre', $statut->ordre) }}">
                                @error('ordre')<small>{{ $message }}</small>@enderror
                            </label>
                        </div>

                        <label class="ceet-status-edit-full">
                            <span>Description du statut</span>
                            <textarea name="description" rows="5">{{ old('description', $statut->description) }}</textarea>
                            @error('description')<small>{{ $message }}</small>@enderror
                        </label>
                    </section>

                    <section class="ceet-status-edit-card">
                        <h2>Permissions de Transition</h2>
                        <div class="ceet-status-edit-divider"></div>

                        <input type="hidden" name="is_active" value="{{ $isActive ? 1 : 0 }}">

                        <div class="ceet-status-edit-permission">
                            <span class="material-symbols-outlined" aria-hidden="true">lock_open</span>
                            <div>
                                <strong>Transition Automatique</strong>
                                <small>Activer le passage au statut suivant apr&egrave;s validation.</small>
                            </div>
                            <span class="ceet-status-edit-toggle-on" aria-hidden="true"></span>
                        </div>

                        <div class="ceet-status-edit-permission">
                            <span class="material-symbols-outlined" aria-hidden="true">mail</span>
                            <div>
                                <strong>Notification Email</strong>
                                <small>Informer le d&eacute;clarant du changement de statut.</small>
                            </div>
                            <span class="ceet-status-edit-toggle-off" aria-hidden="true"></span>
                        </div>
                    </section>
                </div>

                <aside class="ceet-status-edit-right">
                    <section class="ceet-status-edit-card">
                        <h2>Type de Statut</h2>
                        <div class="ceet-status-edit-divider"></div>

                        <div class="ceet-status-edit-radio-list">
                            <label>
                                <input type="radio" disabled>
                                <span>
                                    <strong>Initial</strong>
                                    <small>Premier statut &agrave; la cr&eacute;ation de l'incident.</small>
                                </span>
                            </label>

                            <label class="{{ ! $isFinal ? 'is-selected' : '' }}">
                                <input type="radio" name="is_final" value="0" @checked(! $isFinal)>
                                <span>
                                    <strong>Interm&eacute;diaire</strong>
                                    <small>&Eacute;tape de traitement ou d'analyse en cours.</small>
                                </span>
                            </label>

                            <label class="{{ $isFinal ? 'is-selected' : '' }}">
                                <input type="radio" name="is_final" value="1" @checked($isFinal)>
                                <span>
                                    <strong>Final / Cl&ocirc;ture</strong>
                                    <small>Marque la fin du traitement de l'incident.</small>
                                </span>
                            </label>
                        </div>
                    </section>

                    <section class="ceet-status-edit-card">
                        <h2>Aper&ccedil;u visuel</h2>
                        <p>Apparence dans le tableau de bord.</p>

                        <div class="ceet-status-edit-preview">
                            <span>{{ Str::upper(old('libelle', $statut->libelle)) }}</span>
                        </div>

                        <fieldset class="ceet-status-edit-colors">
                            <legend>Couleur du badge</legend>
                            @foreach($badgeColors as $color)
                                <label style="--badge-color: {{ $color }}">
                                    <input type="radio" name="couleur" value="{{ $color }}" @checked(Str::lower($selectedColor) === Str::lower($color))>
                                    <span></span>
                                </label>
                            @endforeach
                        </fieldset>
                        @error('couleur')<small class="ceet-status-edit-error">{{ $message }}</small>@enderror
                    </section>
                </aside>
            </div>

            <section class="ceet-status-edit-advice">
                <span class="material-symbols-outlined" aria-hidden="true">info</span>
                <div>
                    <strong>Conseil de configuration</strong>
                    <p>Les modifications sur un statut non final impactent les rapports d'incidents non encore cl&ocirc;tur&eacute;s.</p>
                </div>
                <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}">Documentation</a>
            </section>
        </form>
    </main>
</body>
</html>
