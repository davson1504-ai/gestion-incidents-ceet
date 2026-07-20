@extends('layouts.app')

@section('title', 'Créer une cause')

@section('page_css')
    @vite([
        'resources/css/pages/catalogues.css'
    ])
@endsection

@php
    use Illuminate\Support\Str;

    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $currentUser = auth()->user();
    $fullName = trim((string) ($currentUser?->name ?? 'Administrateur'));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($nameParts)->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('') ?: 'AD';
    $roleName = $currentUser?->getRoleNames()?->first() ?? 'Administrateur';
    $types = $types ?? collect();

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Tous les incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
        ['label' => 'Utilisateurs', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'Statut du système', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')],
        ['label' => 'Catalogues', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Rapports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')],
    ];
@endphp

@section('content')
<div class="ceet-cause-create-page">
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
            <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
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
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/app.js'
    ])
@endsection
