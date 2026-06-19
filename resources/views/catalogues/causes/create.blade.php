@php
    use Illuminate\Support\Str;

    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrator'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrator';
    $types = $types ?? collect();

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

    <title>Creer cause - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-cause-create-page">
    <aside class="ceet-cause-create-sidebar">
        <div class="ceet-cause-create-brand">
            <strong>CEET Incidents</strong>
            <span>Electrical Management</span>
        </div>

        <nav class="ceet-cause-create-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-cause-create-nav-link'])
            </nav>

        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="ceet-cause-create-user">
            <span>{{ $initials }}</span>
            <div>
                <strong>{{ $fullName }}</strong>
                <small>{{ $currentUser?->email ?? $roleName }}</small>
            </div>
        </a>
    </aside>

    <header class="ceet-cause-create-topbar">
        <nav class="ceet-cause-create-breadcrumb" aria-label="Fil d'Ariane">
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ Route::has('catalogues.index') ? route('catalogues.index') : '#' }}">Catalogs</a>
            @endunless
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            <a href="{{ route('catalogues.causes.index') }}">Causes probables</a>
        </nav>

        <div class="ceet-cause-create-toolbar">
            <button type="button" class="has-dot" aria-label="Notifications">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <button type="button" aria-label="Aide">
                <span class="material-symbols-outlined" aria-hidden="true">help</span>
            </button>
            <span></span>
            <strong>CEET Incidents</strong>
        </div>
    </header>

    <main class="ceet-cause-create-main">
        <section class="ceet-cause-create-head">
            <div>
                <h1>31. Cr&eacute;er cause</h1>
                <p>Enregistrement d'une nouvelle cause d'incident dans le r&eacute;f&eacute;rentiel syst&egrave;me.</p>
            </div>

            <aside>
                <span class="material-symbols-outlined" aria-hidden="true">info</span>
                <div>
                    <small>R&eacute;f&eacute;rentiel</small>
                    <strong>Standard CEET-2024</strong>
                </div>
            </aside>
        </section>

        @if($errors->any())
            <div class="ceet-alert ceet-alert-danger" role="alert">
                Veuillez corriger les champs signal&eacute;s avant d'enregistrer.
            </div>
        @endif

        <div class="ceet-cause-create-grid">
            <form method="POST" action="{{ route('catalogues.causes.store') }}" class="ceet-cause-create-card">
                @csrf

                <div class="ceet-cause-create-row">
                    <label>
                        <span>Code identifiant</span>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="ex: CP-001" required>
                        @error('code')<small>{{ $message }}</small>@enderror
                    </label>

                    <label>
                        <span>Statut</span>
                        <select name="is_active">
                            <option value="1" @selected(old('is_active', '1') === '1')>ACTIF</option>
                            <option value="0" @selected(old('is_active') === '0')>INACTIF</option>
                        </select>
                        @error('is_active')<small>{{ $message }}</small>@enderror
                    </label>
                </div>

                <label class="ceet-cause-create-full">
                    <span>Libell&eacute; de la cause</span>
                    <input type="text" name="libelle" value="{{ old('libelle') }}" placeholder="Nom descriptif court" required>
                    @error('libelle')<small>{{ $message }}</small>@enderror
                </label>

                <label class="ceet-cause-create-full">
                    <span>Type d'incident associ&eacute;</span>
                    <select name="type_incident_id" required>
                        <option value="">S&eacute;lectionner un type d'incident...</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected((string) old('type_incident_id') === (string) $type->id)>
                                {{ $type->libelle }}
                            </option>
                        @endforeach
                    </select>
                    @error('type_incident_id')<small>{{ $message }}</small>@enderror
                </label>

                <label class="ceet-cause-create-full">
                    <span>Description d&eacute;taill&eacute;e</span>
                    <textarea name="description" rows="7" placeholder="Expliquez en d&eacute;tail la nature de cette cause probable...">{{ old('description') }}</textarea>
                    @error('description')<small>{{ $message }}</small>@enderror
                </label>

                <footer>
                    <a href="{{ route('catalogues.causes.index') }}">Annuler</a>
                    <button type="submit">Enregistrer</button>
                </footer>
            </form>

            <aside class="ceet-cause-create-side">
                <section class="ceet-cause-create-photo">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCko-ox8L-S9kD86_-wzYNIJsbbbGfp66vI3kbw88KSQaBc-_Y7Au2HZDDuQRJCPfolmFbnjXXyar8ySwk_bh5SOvydj9k_HgXlfK_97o8RsnofO2QYojS80rZreNmSvuHI1rz69NE__rSSxm3fLMm3yVU5A3mVyynymDRP45Pnbg_iSjKUh9XntwGlNthQGAU0gIPF57pAeSWEr2HlAV_9KlypN8We8sV9pdxeXyuMbTB454lkOxD6KN5VknGAwesUjdH02DHGB-3r" alt="Infrastructure electrique">
                    <span>Infrastructure</span>
                </section>

                <section class="ceet-cause-create-guide">
                    <h2>
                        <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                        Guide de saisie
                    </h2>
                    <ul>
                        <li>Le <strong>Code</strong> doit &ecirc;tre unique et suivre la nomenclature standard.</li>
                        <li>Le <strong>Libell&eacute;</strong> appara&icirc;tra dans les menus d&eacute;roulants de saisie d'incidents.</li>
                        <li>Associez la cause au <strong>Type</strong> le plus pertinent pour affiner les statistiques.</li>
                    </ul>
                </section>

                <section class="ceet-cause-create-update">
                    <div>
                        <span class="material-symbols-outlined" aria-hidden="true">history</span>
                        <small>Derni&egrave;re mise &agrave; jour</small>
                    </div>
                    <strong>{{ now()->format('d M. Y - H:i') }}</strong>
                    <p>Par {{ $fullName }} ({{ $initials }})</p>
                </section>
            </aside>
        </div>
    </main>
</body>
</html>
