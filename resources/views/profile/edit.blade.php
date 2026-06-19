@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $user = $user ?? auth()->user();
    $fullName = trim((string) ($user?->name ?? 'Utilisateur CEET'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $firstName = old('first_name', $nameParts[0] ?? $fullName);
    $lastName = old('last_name', count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '');
    $initials = collect($nameParts)
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('') ?: 'CE';
    $roleName = $user?->getRoleNames()?->first() ?? 'Utilisateur';
    $roleLabel = match (Str::lower(Str::ascii($roleName))) {
        'administrateur', 'admin' => 'Administrateur Principal',
        'superviseur' => 'Superviseur',
        'operateur', 'operator' => 'Opérateur',
        default => $roleName,
    };

    $isOperator = $user?->isOperateur() ?? false;
    $canViewUsers = (! $isOperator) && ($user?->can('users.view') ?? false);
    $canViewCatalogues = (! $isOperator) && ($user?->can('catalogues.view') ?? false);
    $canViewReports = (! $isOperator) && ($user?->can('reporting.view') ?? false);
    $canViewSystem = (! $isOperator) && (($user?->isAdmin() ?? false) || ($user?->isSuperviseur() ?? false));

    $navItems = [
        ['label' => 'Tableau de bord', 'icon' => 'dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
    ];

    if ($isOperator) {
        $navItems[] = ['label' => 'Mes incidents', 'icon' => 'groups', 'route' => route('incidents.mine'), 'active' => request()->routeIs('incidents.mine')];
        $navItems[] = ['label' => 'Incidents en cours', 'icon' => 'schedule', 'route' => route('incidents.en-cours'), 'active' => request()->routeIs('incidents.en-cours')];
    } else {
        $navItems[] = ['label' => 'Incidents', 'icon' => 'bolt', 'route' => route('incidents.index'), 'active' => request()->routeIs('incidents.*')];
    }

    if ($canViewUsers) {
        $navItems[] = ['label' => 'Users', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')];
    }

    if (($isAdmin ?? false) && ($canViewSystem ?? false)) {
        $navItems[] = ['label' => 'System Status', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')];
    }

    if (($isAdmin ?? false) && ($canViewCatalogues ?? false)) {
        $navItems[] = ['label' => 'Catalogs', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')];
    }

    if ($canViewReports) {
        $navItems[] = ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')];
    }

// CEET profile supervisor nav override
    $isAdmin = $isAdmin ?? ($user?->isAdmin() ?? false);
    $isSupervisor = $isSupervisor ?? ($user?->isSuperviseur() ?? false);

    if (($isSupervisor ?? false) && !($isAdmin ?? false)) {
        $navItems = [
            ['label' => 'Tableau de bord', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '/dashboard', 'active' => request()->routeIs('dashboard')],
            ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '/incidents', 'active' => request()->routeIs('incidents.index') || request()->routeIs('incidents.show') || request()->routeIs('incidents.edit')],
            ['label' => 'Incidents en cours', 'icon' => 'schedule', 'route' => Route::has('incidents.en-cours') ? route('incidents.en-cours') : '/incidents/en-cours', 'active' => request()->routeIs('incidents.en-cours')],
            ['label' => 'Créer un incident', 'icon' => 'add_circle', 'route' => Route::has('incidents.create') ? route('incidents.create') : '/incidents/create', 'active' => request()->routeIs('incidents.create')],
            ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '/reports', 'active' => request()->routeIs('reports.*')],
        ];
    }
    // END CEET profile supervisor nav override

    // CEET profile supervisor nav override
    if (($isSupervisor ?? false) && !($isAdmin ?? false)) {
        $navItems = [
            ['label' => 'Tableau de bord', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '/dashboard', 'active' => request()->routeIs('dashboard')],
            ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '/incidents', 'active' => request()->routeIs('incidents.index') || request()->routeIs('incidents.show') || request()->routeIs('incidents.edit')],
            ['label' => 'Incidents en cours', 'icon' => 'schedule', 'route' => Route::has('incidents.en-cours') ? route('incidents.en-cours') : '/incidents/en-cours', 'active' => request()->routeIs('incidents.en-cours')],
            ['label' => 'Créer un incident', 'icon' => 'add_circle', 'route' => Route::has('incidents.create') ? route('incidents.create') : '/incidents/create', 'active' => request()->routeIs('incidents.create')],
            ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '/reports', 'active' => request()->routeIs('reports.*')],
        ];
    }
    // END CEET profile supervisor nav override
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Profil - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-profile-page">
    <aside class="ceet-profile-sidebar">
        <div class="ceet-profile-brand">
            <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET" class="ceet-profile-brand-logo">
            <div class="ceet-profile-brand-copy">
                <h1>CEET Incidents</h1>
                <p>Electrical Management</p>
            </div>
        </div>

        <nav class="ceet-profile-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-profile-nav-link'])
            </nav>

        <div class="ceet-profile-sidebar-user">
            <div class="ceet-profile-sidebar-user-main">
                <div class="ceet-profile-avatar is-small">{{ $initials }}</div>

                <div>
                    <strong>{{ $fullName }}</strong>
                    <span>{{ Str::upper($roleName) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="ceet-profile-logout-form">
                @csrf

                <button type="submit" class="ceet-profile-logout-button">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                    Se déconnecter
                </button>
            </form>
        </div>
    </aside>

    <header class="ceet-profile-topbar">
        <div class="ceet-profile-breadcrumb">Profil</div>

        <div class="ceet-profile-top-actions">
            <button type="button" class="ceet-profile-icon-btn" aria-label="Notifications" data-ceet-profile-notifications>
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
                <i aria-hidden="true"></i>
            </button>
            <button type="button" class="ceet-profile-icon-btn" aria-label="Aide">
                <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
            </button>
            <span class="ceet-profile-divider" aria-hidden="true"></span>
            <div class="ceet-profile-top-user">
                <span>{{ $fullName }}</span>
                <div class="ceet-profile-avatar is-top">{{ $initials }}</div>
            </div>
        </div>
    </header>

    <main class="ceet-profile-main">
        <section class="ceet-profile-heading">
            <h2>Profil</h2>
            <p>Gérez vos informations personnelles et vos paramètres de sécurité.</p>
        </section>

        @if (session('status') === 'profile-updated')
            <div class="ceet-alert ceet-alert-success" role="status">Informations personnelles mises à jour.</div>
        @endif

        @if (session('status') === 'password-updated')
            <div class="ceet-alert ceet-alert-success" role="status">Mot de passe mis à jour.</div>
        @endif

        <div class="ceet-profile-grid">
            <section class="ceet-profile-card ceet-profile-identity-card">
                <header class="ceet-profile-card-header">
                    <h3>Informations personnelles</h3>
                    <span>Identité</span>
                </header>

                <form method="POST" action="{{ route('profile.update') }}" class="ceet-profile-form">
                    @csrf
                    @method('PATCH')

                    <div class="ceet-profile-avatar-block">
                        <div class="ceet-profile-avatar">{{ $initials }}</div>
                        <button type="button" class="ceet-profile-avatar-edit">
                            <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                            Modifier l'avatar
                        </button>
                    </div>

                    <div class="ceet-profile-fields">
                        <div class="ceet-profile-field-row">
                            <label>
                                <span>Prénom</span>
                                <input type="text" name="first_name" value="{{ $firstName }}" autocomplete="given-name">
                            </label>

                            <label>
                                <span>Nom</span>
                                <input type="text" name="last_name" value="{{ $lastName }}" autocomplete="family-name">
                            </label>
                        </div>

                        <input type="hidden" name="name" data-full-name value="{{ old('name', $fullName) }}">

                        <label>
                            <span>Email professionnel</span>
                            <input type="email" name="email" value="{{ old('email', $user?->email) }}" autocomplete="email" required>
                            @error('email')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="ceet-profile-field-row">
                            <label>
                                <span>Téléphone</span>
                                <input type="text" name="telephone" value="{{ old('telephone', $user?->telephone) }}" autocomplete="tel" placeholder="+228 90 00 00 00">
                                @error('telephone')
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>

                            <label>
                                <span>Rôle système</span>
                                <div class="ceet-profile-locked-field">
                                    <span>{{ $roleLabel }}</span>
                                    <span class="material-symbols-outlined" aria-hidden="true">lock</span>
                                </div>
                            </label>
                        </div>

                        @error('name')
                            <small class="ceet-profile-error">{{ $message }}</small>
                        @enderror

                        <div class="ceet-profile-actions">
                            <button type="submit" class="ceet-profile-primary-btn">Enregistrer les modifications</button>
                        </div>
                    </div>
                </form>
            </section>

            <aside class="ceet-profile-side">
                <section class="ceet-profile-card ceet-profile-security-card">
                    <header class="ceet-profile-card-header">
                        <h3>
                            <span class="material-symbols-outlined" aria-hidden="true">shield</span>
                            Sécurité
                        </h3>
                    </header>

                    <p>Modifiez votre mot de passe pour sécuriser votre accès aux infrastructures.</p>

                    <form method="POST" action="{{ route('password.update') }}" class="ceet-profile-password-form">
                        @csrf
                        @method('PUT')

                        <label>
                            <span>Ancien mot de passe</span>
                            <input type="password" name="current_password" autocomplete="current-password">
                            @error('current_password', 'updatePassword')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <hr>

                        <label>
                            <span>Nouveau mot de passe</span>
                            <input type="password" name="password" autocomplete="new-password">
                            @error('password', 'updatePassword')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <label>
                            <span>Confirmation</span>
                            <input type="password" name="password_confirmation" autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword')
                                <small>{{ $message }}</small>
                            @enderror
                        </label>

                        <button type="submit" class="ceet-profile-secondary-btn">Mettre à jour le mot de passe</button>
                    </form>
                </section>

                <section class="ceet-profile-card ceet-profile-activity-card">
                    <h3>Activité récente</h3>
                    <div class="ceet-profile-activity-list">
                        <article>
                            <span class="material-symbols-outlined" aria-hidden="true">login</span>
                            <div>
                                <strong>Connexion réussie</strong>
                                <p>Il y a 2 heures • IP: {{ request()->ip() }}</p>
                            </div>
                        </article>

                        <article>
                            <span class="material-symbols-outlined" aria-hidden="true">warning</span>
                            <div>
                                <strong class="is-danger">Changement de privilège</strong>
                                <p>Hier • Par Administrateur Système</p>
                            </div>
                        </article>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    <script>
        const profileForm = document.querySelector('.ceet-profile-form');
        const fullNameField = document.querySelector('[data-full-name]');

        if (profileForm && fullNameField) {
            profileForm.addEventListener('submit', () => {
                const firstName = profileForm.querySelector('[name="first_name"]').value.trim();
                const lastName = profileForm.querySelector('[name="last_name"]').value.trim();
                fullNameField.value = [firstName, lastName].filter(Boolean).join(' ');
            });
        }
    </script>
</body>
</html>
