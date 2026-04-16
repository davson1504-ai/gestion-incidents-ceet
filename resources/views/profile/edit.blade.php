@php
    use Illuminate\Support\Str;

    $dbDriver = strtoupper((string) config('database.default', 'mysql'));
    $timezoneLabel = config('app.timezone', 'Africa/Lome');
    $defaultFooterText = 'CEET - Direction Technique & Distribution';
@endphp

<x-app-layout>
    <style>
        /* ========================================
           VARIABLES & BASE
           ======================================== */
        :root {
            --ceet-red: #ef2433;
            --ceet-red-dark: #ce1220;
            --ceet-gold: #f59e0b;
            --ceet-blue-night: #0f172a;
            --ceet-blue-deep: #1e293b;
            --ceet-gray-light: #f8fafc;
            --ceet-border-light: #e2e8f0;
            --ceet-text-muted: #64748b;
            --ceet-success: #22c55e;
        }

        /* ========================================
           ANIMATIONS
           ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-light {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.85;
            }
        }

        /* ========================================
           CARDS - Modern Design
           ======================================== */
        .card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.88));
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.07);
            animation: fadeInUp 0.6s ease both;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.15);
            border-color: rgba(226, 232, 240, 0.8);
        }

        /* ========================================
           FORMS & INPUTS
           ======================================== */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--ceet-border-light);
            transition: all 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--ceet-red);
            box-shadow: 0 0 0 3px rgba(239, 36, 51, 0.1);
        }

        /* ========================================
           BUTTONS
           ======================================== */
        .btn-danger {
            background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 36, 51, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 36, 51, 0.4);
        }

        .btn-outline-secondary, .btn-outline-danger {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover, .btn-outline-danger:hover {
            transform: translateY(-2px);
        }

        /* ========================================
           ALERT MESSAGES
           ======================================== */
        .alert {
            border-radius: 10px;
            border: 1px solid rgba(239, 36, 51, 0.2);
            animation: slideInDown 0.4s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.05), rgba(34, 197, 94, 0.02));
            border-color: rgba(34, 197, 94, 0.2);
            color: var(--ceet-success);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 36, 51, 0.05), rgba(239, 36, 51, 0.02));
            border-color: rgba(239, 36, 51, 0.2);
            color: var(--ceet-red);
        }

        /* ========================================
           LIST GROUP
           ======================================== */
        .list-group-item {
            border-radius: 10px;
            border: 1px solid var(--ceet-border-light);
            transition: all 0.2s ease;
        }

        .list-group-item-action:hover {
            background-color: rgba(239, 36, 51, 0.05);
            transform: translateX(4px);
        }

        .list-group-item-action.active {
            background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
            border-color: var(--ceet-red);
        }

        /* ========================================
           BADGE - Modern Style
           ======================================== */
        .badge {
            border-radius: 8px;
            padding: 6px 12px;
            transition: all 0.2s ease;
            animation: pulse-light 3s ease-in-out infinite;
        }

        .text-bg-light {
            background: linear-gradient(135deg, rgba(226, 232, 240, 0.3), rgba(226, 232, 240, 0.1));
            color: var(--ceet-text-muted);
        }
    </style>
    <x-slot name="header">
        <div class="d-flex w-100 flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
            <div>
                <h1 class="h3 mb-1">Réglages système</h1>
                <p class="text-muted mb-0">Configurez les paramètres globaux, les intégrations et les préférences de l'application.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge rounded-pill text-bg-light px-3 py-2">
                    Base connectée : {{ $dbDriver }} {{ $dbVersion ?? 'N/A' }}
                </span>
                <button type="submit" form="generalSettingsForm" class="btn btn-danger">Enregistrer les modifications</button>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success">Réglages enregistrés avec succès.</div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success">Mot de passe mis à jour avec succès.</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="text-center mb-4">
        <h2 class="h3 fw-bold mb-1">Configuration générale</h2>
        <p class="text-muted mb-0">Paramètres d'identité et de comportement de l'instance locale de l'application.</p>
    </div>

    <form id="generalSettingsForm" method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="email" value="{{ old('email', Str::lower($user->email)) }}">

        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-3">
                <div class="list-group">
                    <div class="list-group-item list-group-item-action active">Général</div>
                    <div class="list-group-item">Notifications</div>
                    <div class="list-group-item">Intégration & DB</div>
                    <div class="list-group-item">Exports & Rapports</div>
                </div>
            </div>

            <div class="col-12 col-xl-9">
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="h4 mb-1">Identité de l'application</h3>
                        <p class="text-muted mb-3">Informations affichées sur les rapports et l'interface utilisateur.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Nom de l'instance</label>
                                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="org_unit">Unité organisationnelle</label>
                                <input id="org_unit" name="org_unit" type="text" class="form-control" value="{{ old('org_unit', $user->departement?->nom ?? 'Cellule de Transformation Digitale') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="footer_text">Texte de bas de page personnalisé</label>
                                <input id="footer_text" name="footer_text" type="text" class="form-control" value="{{ old('footer_text', $defaultFooterText) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="h4 mb-3">Région et langue</h3>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="app_locale">Langue de l'interface</label>
                                <select id="app_locale" class="form-select" name="app_locale">
                                    <option value="fr_TG" selected>Français (Bénin/Togo)</option>
                                    <option value="en_US">English (US)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="app_timezone">Fuseau horaire</label>
                                <input id="app_timezone" type="text" class="form-control" name="app_timezone" value="{{ old('app_timezone', '(GMT+00:00) ' . $timezoneLabel) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-danger-subtle">
                    <div class="card-body">
                        <h3 class="h5 text-danger mb-1">Maintenance & archives</h3>
                        <p class="text-muted mb-3">Actions critiques sur les données historiques.</p>
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">Archivage automatique</div>
                                <div class="small text-muted">Déplacer les incidents clôturés depuis plus de 24 mois vers la base d'archives.</div>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="archive_enabled" name="archive_enabled" value="1" {{ old('archive_enabled', '1') ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <h3 class="h4 mb-1">Sécurité du compte</h3>
            <p class="text-muted mb-3">Mettez à jour votre mot de passe administrateur.</p>

            <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label" for="current_password">Mot de passe actuel</label>
                    <input id="current_password" name="current_password" type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" autocomplete="current-password">
                    @if($errors->updatePassword->has('current_password'))
                        <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password">Nouveau mot de passe</label>
                    <input id="password" name="password" type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" autocomplete="new-password">
                    @if($errors->updatePassword->has('password'))
                        <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" autocomplete="new-password">
                    @if($errors->updatePassword->has('password_confirmation'))
                        <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                    @endif
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-outline-danger">Mettre à jour le mot de passe</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
