@php
    use Illuminate\Support\Str;

    $dbDriver = strtoupper((string) config('database.default', 'mysql'));
    $timezoneLabel = config('app.timezone', 'Africa/Lome');
    $defaultFooterText = 'CEET - Direction Technique & Distribution';
@endphp

<x-app-layout>
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
