@extends('layouts.app')

@section('title', 'Nouveau départ électrique')

@section('page_css')
    @vite([
        'resources/css/pages/catalogues.css',
    ])
@endsection

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

@section('content')
<div class="ceet-admin-dashboard-page ceet-depart-create-page" data-admin-dashboard data-depart-create-page>
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
@endsection

@section('page_js')
    @vite([
        'resources/js/pages/catalogues.js'
    ])
@endsection
