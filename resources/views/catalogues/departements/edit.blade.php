@php
    use Illuminate\Support\Str;

    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Jean Dupont'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'JD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Admin Reseau';
    $selectedVoltage = old('charge_unite', $departement->charge_unite ?: '20kV');
    $voltageOptions = collect(['15kV', '20kV', '33kV', '66kV'])
        ->when($selectedVoltage, fn ($items) => $items->contains($selectedVoltage) ? $items : $items->prepend($selectedVoltage))
        ->unique()
        ->values();

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
        ['label' => 'Catalog', 'icon' => 'inventory_2', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Admin Tools', 'icon' => 'admin_panel_settings', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'Settings', 'icon' => 'settings', 'route' => Route::has('profile.edit') ? route('profile.edit') : '#', 'active' => request()->routeIs('profile.*')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f8f9fb">

    <title>Modifier depart - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-depart-edit-page">
    <aside class="ceet-depart-edit-sidebar">
        <div class="ceet-depart-edit-brand">
            <span class="ceet-depart-edit-brand-mark" aria-hidden="true">
                <img src="{{ asset('images/logo-ceet.png') }}" alt="">
            </span>
            <div>
                <strong>CEET Incidents</strong>
                <span>Infrastructure Management</span>
            </div>
        </div>

        <nav class="ceet-depart-edit-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-depart-edit-nav-link'])
            </nav>

        <div class="ceet-depart-edit-sidebar-bottom">
            @if(Route::has('incidents.create'))
                <a href="{{ route('incidents.create') }}" class="ceet-depart-edit-side-action">
                    <span class="material-symbols-outlined" aria-hidden="true">add_circle</span>
                    <span>New Incident</span>
                </a>
            @endif

            <a href="#" class="ceet-depart-edit-side-action">
                <span class="material-symbols-outlined" aria-hidden="true">support_agent</span>
                <span>Support</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ceet-depart-edit-side-action is-button">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <header class="ceet-depart-edit-topbar">
        <label class="ceet-depart-edit-search">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <input type="search" placeholder="Search catalog...">
        </label>

        <div class="ceet-depart-edit-tabs" aria-label="Raccourcis">
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ Route::has('catalogues.index') ? route('catalogues.index') : '#' }}">Overview</a>
            @endunless
            <a href="{{ Route::has('historique.index') ? route('historique.index') : '#' }}">History</a>
            <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}">Reports</a>
        </div>

        <div class="ceet-depart-edit-userbar">
            <button type="button" class="ceet-depart-edit-icon-btn" aria-label="Notifications">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <div class="ceet-depart-edit-user">
                <span>{{ $initials }}</span>
                <div>
                    <strong>{{ $fullName }}</strong>
                    <small>{{ $roleName }}</small>
                </div>
            </div>
        </div>
    </header>

    <main class="ceet-depart-edit-main">
        <div class="ceet-depart-edit-breadcrumb">
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ Route::has('catalogues.index') ? route('catalogues.index') : '#' }}">Catalogs</a>
            @endunless
            <span>/</span>
            <a href="{{ route('catalogues.departements.index') }}">Departs Electriques</a>
            <span>/</span>
            <strong>Modifier</strong>
        </div>

        <section class="ceet-depart-edit-head">
            <div>
                <span class="ceet-depart-edit-page-number">PAGE 26</span>
                <h1>26. Modifier d&eacute;part</h1>
            </div>
            <a href="{{ route('catalogues.departements.index') }}" class="ceet-depart-edit-back">
                <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                <span>Retour liste</span>
            </a>
        </section>

        @if(session('success'))
            <div class="ceet-alert ceet-alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="ceet-alert ceet-alert-danger" role="alert">
                Veuillez corriger les champs signal&eacute;s avant d'enregistrer.
            </div>
        @endif

        <form method="POST" action="{{ route('catalogues.departements.update', $departement) }}" class="ceet-depart-edit-layout">
            @csrf
            @method('PUT')

            <section class="ceet-depart-edit-card ceet-depart-edit-form-card">
                <div class="ceet-depart-edit-section-title">
                    <span></span>
                    <h2>Identification du d&eacute;part</h2>
                </div>

                <div class="ceet-depart-edit-field-grid">
                    <label>
                        <span>Code d&eacute;part</span>
                        <input type="text" name="code" value="{{ old('code', $departement->code) }}" required>
                        @error('code')<small>{{ $message }}</small>@enderror
                    </label>

                    <label>
                        <span>Nom du d&eacute;part</span>
                        <input type="text" name="nom" value="{{ old('nom', $departement->nom) }}" required>
                        @error('nom')<small>{{ $message }}</small>@enderror
                    </label>
                </div>

                <label class="ceet-depart-edit-full-field">
                    <span>Zone g&eacute;ographique</span>
                    <input type="text" name="zone" value="{{ old('zone', $departement->zone) }}" placeholder="Ex: Lome District 5">
                    @error('zone')<small>{{ $message }}</small>@enderror
                </label>

                <div class="ceet-depart-edit-section-title is-spaced">
                    <span></span>
                    <h2>Sp&eacute;cifications techniques</h2>
                </div>

                <label class="ceet-depart-edit-full-field">
                    <span>Tension nominale</span>
                    <select name="charge_unite">
                        @foreach($voltageOptions as $option)
                            <option value="{{ $option }}" @selected($selectedVoltage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('charge_unite')<small>{{ $message }}</small>@enderror
                </label>

                <label class="ceet-depart-edit-full-field">
                    <span>Description technique</span>
                    <textarea name="description" rows="8" placeholder="Caracteristiques et notes techniques...">{{ old('description', $departement->description) }}</textarea>
                    @error('description')<small>{{ $message }}</small>@enderror
                </label>
            </section>

            <aside class="ceet-depart-edit-side">
                <section class="ceet-depart-edit-card ceet-depart-edit-status-card">
                    <h2>Etat de mise en service</h2>
                    <input type="hidden" name="is_active" value="0">
                    <label class="ceet-depart-edit-toggle">
                        <span>{{ old('is_active', $departement->is_active) ? 'Actif' : 'Inactif' }}</span>
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $departement->is_active))>
                        <i aria-hidden="true"></i>
                    </label>
                    @error('is_active')<small>{{ $message }}</small>@enderror
                </section>

                <section class="ceet-depart-edit-context">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBv8L6DlEdNC4Np8X0jRyk-I_PeNqdORZ_BQLZ5T91sU4qY7Ca1J834yse3Xfxk0nuxW9pmEi4JdXKW7H68HeMbj0bWITxD_ngIrhwoNp1NrQfb1t7UpB-EEWDf9_9mvS6CRbBLWu9LMwvmhIBZY-8Y7vx_RpogvMFyD6Phe0x-Y12g8jWj0dr-VTc2pfqDJQkI9-LghZuyIzkHC4pzok2xQ7rJzONW4FTViJHOzhs1C3qTvFqWs7KTsDl7qhqmn4kyMrKKYTPdZxm3" alt="Infrastructure electrique">
                    <div>
                        <h2>Contexte r&eacute;seau</h2>
                        <p>Les modifications sont synchronis&eacute;es avec le catalogue d'incidents pour garder les affectations coh&eacute;rentes.</p>
                    </div>
                </section>
            </aside>

            <footer class="ceet-depart-edit-actions">
                <a href="{{ route('catalogues.departements.index') }}">Annuler</a>
                <button type="submit">Enregistrer les modifications</button>
            </footer>
        </form>
    </main>
</body>
</html>
