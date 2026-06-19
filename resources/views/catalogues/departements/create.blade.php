@php
    $currentUser = auth()->user();
    $userName = $currentUser?->name ?? 'Administrateur';
    $userEmail = $currentUser?->email ?? 'admin@ceet.tg';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames')
        ? ($currentUser->getRoleNames()->first() ?: 'Administrateur')
        : 'Administrateur';

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    $tensions = [
        'BT 230/400 V' => 'BT 230/400 V',
        'HTA 20 kV' => 'HTA 20 kV',
        'HTA 33 kV' => 'HTA 33 kV',
        'HTB 63 kV' => 'HTB 63 kV',
        'HTB 161 kV' => 'HTB 161 kV',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau départ électrique - CEET Incidents</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/admin-dashboard.css',
        'resources/js/pages/admin-dashboard.js'
    ])
</head>

<body class="ceet-admin-dashboard-page ceet-depart-create-page">
    <div class="ceet-admin-shell" data-admin-dashboard data-depart-create-page>
        <div class="ceet-dashboard-overlay" data-dashboard-overlay></div>

        <aside class="ceet-admin-sidebar ceet-depart-create-sidebar" data-dashboard-sidebar>
            <div class="ceet-admin-brand">
                <h1>CEET Incidents</h1>
                <p>Gestion électrique</p>
            </div>

            <nav class="ceet-admin-nav" aria-label="Navigation principale">
                @include('partials.ceet-role-nav', ['linkClass' => 'ceet-admin-nav-link'])
            </nav>

            <div class="ceet-depart-create-sidebar-user">
                <span>{{ $initials }}</span>
                <div>
                    <strong>{{ $roleName }}</strong>
                    <em>{{ $userEmail }}</em>
                </div>
            </div>
        </aside>

        <header class="ceet-admin-topbar ceet-depart-create-topbar">
            <button type="button" class="ceet-admin-menu-btn" data-dashboard-sidebar-toggle aria-label="Ouvrir le menu" aria-expanded="false">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <nav class="ceet-depart-create-breadcrumb" aria-label="Fil d’Ariane">
                @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                <a href="{{ route('catalogues.index') }}">Catalogues</a>
                @endunless
                <span class="material-symbols-outlined">chevron_right</span>
                <a href="{{ route('catalogues.departements.index') }}">Départs électriques</a>
                <span class="material-symbols-outlined">chevron_right</span>
                <strong>Nouveau</strong>
            </nav>

            <div class="ceet-admin-top-actions">
                <a href="{{ route('notifications.index') }}" class="ceet-admin-icon-btn" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="ceet-admin-icon-btn" aria-label="Aide et profil">
                    <span class="material-symbols-outlined">help_outline</span>
                </a>

                <div class="ceet-admin-top-divider"></div>

                <strong class="ceet-depart-create-top-user">{{ $roleName }}</strong>
            </div>
        </header>

        <main class="ceet-admin-main ceet-depart-create-main">
            <form action="{{ route('catalogues.departements.store') }}" method="POST" class="ceet-depart-create-form" data-depart-create-form>
                @csrf

                <section class="ceet-depart-create-heading">
                    <div>
                        <h2>Nouveau départ électrique</h2>
                        <p>Configuration des paramètres techniques du réseau haute et basse tension.</p>
                    </div>

                    <div class="ceet-depart-create-heading-actions">
                        <a href="{{ route('catalogues.departements.index') }}">Annuler</a>
                        <button type="submit" data-depart-submit-top>Enregistrer</button>
                    </div>
                </section>

                @if ($errors->any())
                    <div class="ceet-depart-create-alert">
                        <strong>Formulaire incomplet.</strong>
                        <span>Corrigez les champs signalés avant d’enregistrer le départ électrique.</span>
                    </div>
                @endif

                <section class="ceet-depart-create-layout">
                    <div class="ceet-depart-create-left">
                        <article class="ceet-depart-create-card">
                            <header>
                                <h3>Identification du départ</h3>
                                <span class="material-symbols-outlined">info</span>
                            </header>

                            <div class="ceet-depart-create-grid">
                                <div class="ceet-depart-create-field">
                                    <label for="code">Code du départ</label>
                                    <input
                                        id="code"
                                        type="text"
                                        name="code"
                                        value="{{ old('code') }}"
                                        placeholder="Ex : LOME-A"
                                        autocomplete="off"
                                        required
                                    >
                                    <em>Identifiant unique système</em>
                                    @error('code') <small>{{ $message }}</small> @enderror
                                </div>

                                <div class="ceet-depart-create-field">
                                    <label for="nom">Nom du départ</label>
                                    <input
                                        id="nom"
                                        type="text"
                                        name="nom"
                                        value="{{ old('nom') }}"
                                        placeholder="Nom descriptif"
                                        autocomplete="off"
                                        required
                                    >
                                    @error('nom') <small>{{ $message }}</small> @enderror
                                </div>

                                <div class="ceet-depart-create-field is-wide">
                                    <label for="zone">Zone géographique</label>
                                    <div class="ceet-depart-create-input-icon">
                                        <input
                                            id="zone"
                                            type="text"
                                            name="zone"
                                            value="{{ old('zone') }}"
                                            placeholder="Quartier, district ou commune"
                                            autocomplete="off"
                                        >
                                        <span class="material-symbols-outlined">location_on</span>
                                    </div>
                                    @error('zone') <small>{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </article>

                        <article class="ceet-depart-create-card">
                            <header>
                                <h3>Spécifications techniques</h3>
                                <span class="material-symbols-outlined">settings</span>
                            </header>

                            <div class="ceet-depart-create-stack">
                                <div class="ceet-depart-create-field">
                                    <label for="arrivee">Tension nominale</label>
                                    <select id="arrivee" name="arrivee">
                                        <option value="">Sélectionner la tension</option>
                                        @foreach ($tensions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('arrivee') === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('arrivee') <small>{{ $message }}</small> @enderror
                                </div>

                                <div class="ceet-depart-create-field">
                                    <label for="description">Description technique</label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="6"
                                        placeholder="Détails sur les transformateurs, la charge maximale, le type de câblage..."
                                    >{{ old('description') }}</textarea>
                                    @error('description') <small>{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </article>
                    </div>

                    <aside class="ceet-depart-create-right">
                        <article class="ceet-depart-create-card ceet-depart-create-status-card">
                            <header>
                                <h3>État de mise en service</h3>
                            </header>

                            <input type="hidden" name="is_active" value="0">

                            <label class="ceet-depart-create-status-box">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    @checked(old('is_active', '1') == '1')
                                    data-depart-active-toggle
                                >
                                <span>
                                    <strong data-depart-active-label>Statut actif</strong>
                                    <em>Permet l’allocation immédiate aux incidents.</em>
                                </span>
                            </label>
                        </article>

                        <article class="ceet-depart-create-network-card">
                            <div>
                                <span>Contexte réseau</span>
                                <strong>Maintenir l’intégrité structurelle du réseau CEET par une nomenclature rigoureuse.</strong>
                            </div>
                        </article>

                        <article class="ceet-depart-create-compliance-card">
                            <header>
                                <span class="material-symbols-outlined">priority_high</span>
                                <h3>Rappels de conformité</h3>
                            </header>

                            <ul>
                                <li>Le code doit respecter la nomenclature interne des zones.</li>
                                <li>La tension HT nécessite une validation par l’ingénieur de garde.</li>
                                <li>Toute modification est enregistrée dans le journal d’audit.</li>
                            </ul>
                        </article>
                    </aside>
                </section>

                <footer class="ceet-depart-create-footer">
                    <div class="ceet-depart-create-session">
                        <span><i></i>Système opérationnel</span>
                        <b>Session : <time data-depart-session-clock>{{ now()->format('H:i:s') }}</time></b>
                    </div>

                    <div class="ceet-depart-create-footer-actions">
                        <button type="reset" class="is-light" data-depart-reset>Réinitialiser</button>
                        <button type="submit" class="is-dark" data-depart-submit-bottom>Enregistrer le départ</button>
                    </div>
                </footer>
            </form>
        </main>
    </div>
</body>
</html>
