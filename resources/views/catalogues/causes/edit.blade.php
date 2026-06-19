@php
    use Illuminate\Support\Str;

    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrator'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrator';
    $types = $types ?? collect();
    $isActive = (bool) old('is_active', $cause->is_active);
    $createdAt = optional($cause->created_at)->format('d M Y');
    $updatedAt = optional($cause->updated_at)->format('d M Y - H:i');

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

    <title>Modifier cause probable - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ceet-cause-edit-page">
    <aside class="ceet-cause-edit-sidebar">
        <div class="ceet-cause-edit-brand">
            <strong>CEET Incidents</strong>
            <span>Electrical Management</span>
        </div>

        <nav class="ceet-cause-edit-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-cause-edit-nav-link'])
            </nav>

        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="ceet-cause-edit-user">
            <span>{{ $initials }}</span>
            <div>
                <strong>{{ $fullName }}</strong>
                <small>ID: {{ str_pad((string) ($currentUser?->id ?? 0), 5, '0', STR_PAD_LEFT) }}</small>
            </div>
            <span class="material-symbols-outlined" aria-hidden="true">logout</span>
        </a>
    </aside>

    <header class="ceet-cause-edit-topbar">
        <nav class="ceet-cause-edit-breadcrumb" aria-label="Fil d'Ariane">
            @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
            <a href="{{ Route::has('catalogues.index') ? route('catalogues.index') : '#' }}">Catalogs</a>
            @endunless
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            <a href="{{ route('catalogues.causes.index') }}">Causes Probables</a>
            <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
            <strong>Modifier {{ $cause->code }}</strong>
        </nav>

        <label class="ceet-cause-edit-search">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <input type="search" placeholder="Rechercher...">
        </label>

        <div class="ceet-cause-edit-toolbar">
            <button type="button" aria-label="Notifications">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <button type="button" aria-label="Aide">
                <span class="material-symbols-outlined" aria-hidden="true">help</span>
            </button>
        </div>
    </header>

    <main class="ceet-cause-edit-main">
        <form method="POST" action="{{ route('catalogues.causes.update', $cause) }}" class="ceet-cause-edit-form">
            @csrf
            @method('PUT')

            <section class="ceet-cause-edit-head">
                <div>
                    <h1>Modifier cause probable</h1>
                    <p>Mise &agrave; jour des param&egrave;tres de classification pour l'analyse des incidents.</p>
                </div>
                <div class="ceet-cause-edit-actions">
                    <a href="{{ route('catalogues.causes.index') }}">Annuler</a>
                    <button type="submit">Enregistrer les modifications</button>
                </div>
            </section>

            @if($errors->any())
                <div class="ceet-alert ceet-alert-danger" role="alert">
                    Veuillez corriger les champs signal&eacute;s avant d'enregistrer.
                </div>
            @endif

            <div class="ceet-cause-edit-grid">
                <div class="ceet-cause-edit-left">
                    <section class="ceet-cause-edit-card">
                        <h2>
                            <span class="material-symbols-outlined" aria-hidden="true">info</span>
                            Informations G&eacute;n&eacute;rales
                        </h2>
                        <div class="ceet-cause-edit-divider"></div>

                        <div class="ceet-cause-edit-row">
                            <label>
                                <span>Code de la cause</span>
                                <input type="text" name="code" value="{{ old('code', $cause->code) }}" required>
                                @error('code')<small>{{ $message }}</small>@enderror
                            </label>

                            <label>
                                <span>Statut</span>
                                <select name="is_active">
                                    <option value="1" @selected($isActive)>Actif</option>
                                    <option value="0" @selected(! $isActive)>Inactif</option>
                                </select>
                                @error('is_active')<small>{{ $message }}</small>@enderror
                            </label>
                        </div>

                        <label class="ceet-cause-edit-full">
                            <span>Libell&eacute; de la cause</span>
                            <input type="text" name="libelle" value="{{ old('libelle', $cause->libelle) }}" required>
                            @error('libelle')<small>{{ $message }}</small>@enderror
                        </label>

                        <label class="ceet-cause-edit-full">
                            <span>Description d&eacute;taill&eacute;e</span>
                            <textarea name="description" rows="5">{{ old('description', $cause->description) }}</textarea>
                            @error('description')<small>{{ $message }}</small>@enderror
                        </label>
                    </section>

                    <section class="ceet-cause-edit-card">
                        <h2>
                            <span class="material-symbols-outlined" aria-hidden="true">account_tree</span>
                            Classification &amp; Association
                        </h2>
                        <div class="ceet-cause-edit-divider"></div>

                        <div class="ceet-cause-edit-row">
                            <label>
                                <span>Type associ&eacute;</span>
                                <select name="type_incident_id" required>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" @selected((string) old('type_incident_id', $cause->type_incident_id) === (string) $type->id)>
                                            {{ $type->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_incident_id')<small>{{ $message }}</small>@enderror
                            </label>

                            <label>
                                <span>Cat&eacute;gorie d'incident</span>
                                <select disabled>
                                    <option>&Eacute;quipement</option>
                                    <option>R&eacute;seau</option>
                                    <option>Exploitation</option>
                                </select>
                            </label>
                        </div>

                        <label class="ceet-cause-edit-check">
                            <input type="checkbox" checked disabled>
                            <span>Inclure dans les rapports d'analyse automatique de performance</span>
                        </label>
                    </section>
                </div>

                <aside class="ceet-cause-edit-side">
                    <section class="ceet-cause-edit-photo">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCko-ox8L-S9kD86_-wzYNIJsbbbGfp66vI3kbw88KSQaBc-_Y7Au2HZDDuQRJCPfolmFbnjXXyar8ySwk_bh5SOvydj9k_HgXlfK_97o8RsnofO2QYojS80rZreNmSvuHI1rz69NE__rSSxm3fLMm3yVU5A3mVyynymDRP45Pnbg_iSjKUh9XntwGlNthQGAU0gIPF57pAeSWEr2HlAV_9KlypN8We8sV9pdxeXyuMbTB454lkOxD6KN5VknGAwesUjdH02DHGB-3r" alt="Infrastructure electrique">
                        <span>Visual ref: {{ Str::upper(Str::limit($cause->typeIncident?->libelle ?? 'distribution', 18, '')) }}</span>
                    </section>

                    <section class="ceet-cause-edit-meta">
                        <h2>M&eacute;tadonn&eacute;es du syst&egrave;me</h2>
                        <dl>
                            <div>
                                <dt>Cr&eacute;&eacute; le :</dt>
                                <dd>{{ $createdAt ?: 'Non renseigne' }}</dd>
                            </div>
                            <div>
                                <dt>Par :</dt>
                                <dd>{{ $fullName }}</dd>
                            </div>
                            <div>
                                <dt>Derni&egrave;re modif :</dt>
                                <dd>{{ $updatedAt ?: 'Non renseigne' }}</dd>
                            </div>
                            <div>
                                <dt>Impact par d&eacute;faut :</dt>
                                <dd><span>Mod&eacute;r&eacute;</span></dd>
                            </div>
                        </dl>
                    </section>

                    <section class="ceet-cause-edit-note">
                        <span class="material-symbols-outlined" aria-hidden="true">tips_and_updates</span>
                        <div>
                            <h2>Note administrative</h2>
                            <p>Toute modification du libell&eacute; affectera les rapports historiques g&eacute;n&eacute;r&eacute;s &agrave; partir de cette date. Assurez-vous que la cause reste compatible avec les entr&eacute;es pr&eacute;c&eacute;dentes.</p>
                        </div>
                    </section>

                    @can('catalogues.manage')
                        <button type="submit" form="delete-cause-form-{{ $cause->id }}" class="ceet-cause-edit-delete">
                            <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                            Supprimer cette cause
                        </button>
                    @endcan
                </aside>
            </div>
        </form>

        @can('catalogues.manage')
            <form id="delete-cause-form-{{ $cause->id }}" method="POST" action="{{ route('catalogues.causes.destroy', $cause) }}" onsubmit="return confirm('Supprimer cette cause ?')">
                @csrf
                @method('DELETE')
            </form>
        @endcan
    </main>
</body>
</html>
