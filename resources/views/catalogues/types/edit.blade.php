@extends('layouts.app')

@section('title', "Modifier le type d'incident")

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
    $isActive = (bool) old('is_active', $type->is_active);
    $createdAt = optional($type->created_at)->format('d M Y, H:i') ?? 'Non renseigne';
    $updatedBy = $roleName ?: 'Admin_SYSTEM';
    $systemId = 'UID-'.Str::upper(Str::substr(md5((string) $type->id.$type->code), 0, 8));

    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => Route::has('dashboard') ? route('dashboard') : '#', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Incidents', 'icon' => 'bolt', 'route' => Route::has('incidents.index') ? route('incidents.index') : '#', 'active' => request()->routeIs('incidents.*')],
        ['label' => 'Users', 'icon' => 'group', 'route' => Route::has('users.index') ? route('users.index') : '#', 'active' => request()->routeIs('users.*')],
        ['label' => 'System Status', 'icon' => 'tune', 'route' => Route::has('system.status') ? route('system.status') : '#', 'active' => request()->routeIs('system.*')],
        ['label' => 'Catalogs', 'icon' => 'menu_book', 'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#', 'active' => request()->routeIs('catalogues.*')],
        ['label' => 'Reports', 'icon' => 'insert_chart', 'route' => Route::has('reports.index') ? route('reports.index') : '#', 'active' => request()->routeIs('reports.*')],
    ];
@endphp

@section('content')
<div class="ceet-type-edit-page">
<main class="ceet-type-edit-main">
<section class="ceet-type-edit-heading">
    <h1>Modifier Type d'Incident</h1>
    <p>Mettez &agrave; jour les sp&eacute;cifications techniques de la cat&eacute;gorie d'incident s&eacute;lectionn&eacute;e.</p>
</section>

@if($errors->any())
    <div class="ceet-alert ceet-alert-danger" role="alert">
        Veuillez corriger les champs signal&eacute;s avant d'enregistrer.
    </div>
@endif

<form method="POST" action="{{ route('catalogues.types.update', $type) }}" class="ceet-type-edit-form-card">
    @csrf
    @method('PUT')
    <input type="hidden" name="code" value="{{ old('code', $type->code) }}">

    <header>
        <h2>D&eacute;tails du type : {{ old('code', $type->code) }}</h2>
    </header>

    <div class="ceet-type-edit-form-body">
        <div class="ceet-type-edit-field-row">
            <label>
                <span>Code de r&eacute;f&eacute;rence</span>
                <input type="text" value="{{ old('code', $type->code) }}" disabled>
                <small class="is-muted">Le code d'incident est une constante syst&egrave;me et ne peut &ecirc;tre modifi&eacute;.</small>
                @error('code')<small>{{ $message }}</small>@enderror
            </label>

            <label>
                <span>Libell&eacute; du type</span>
                <input type="text" name="libelle" value="{{ old('libelle', $type->libelle) }}" required>
                @error('libelle')<small>{{ $message }}</small>@enderror
            </label>
        </div>

        <label class="ceet-type-edit-full-field">
            <span>Description technique d&eacute;taill&eacute;e</span>
            <textarea name="description" rows="5">{{ old('description', $type->description) }}</textarea>
            @error('description')<small>{{ $message }}</small>@enderror
        </label>

        <div class="ceet-type-edit-field-row">
            <label>
                <span>Niveau de priorit&eacute; d&eacute;faut</span>
                <select disabled>
                    <option>Critique - P1</option>
                    <option>Haute - P2</option>
                    <option>Normale - P3</option>
                </select>
            </label>

            <label>
                <span>Cat&eacute;gorie d'intervention</span>
                <select disabled>
                    <option>Urgence R&eacute;seau</option>
                    <option>Maintenance</option>
                    <option>Contr&ocirc;le terrain</option>
                </select>
            </label>
        </div>

        <div class="ceet-type-edit-separator"></div>

        <footer class="ceet-type-edit-form-footer">
            <label class="ceet-type-edit-toggle">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked($isActive)>
                <i aria-hidden="true"></i>
                <span>Type Actif</span>
            </label>

            <div class="ceet-type-edit-actions">
                <a href="{{ route('catalogues.types.index') }}">Annuler</a>
                <button type="submit">Enregistrer</button>
            </div>
        </footer>
    </div>
</form>

<section class="ceet-type-edit-meta-grid" aria-label="Informations systeme">
    <article>
        <span class="material-symbols-outlined" aria-hidden="true">history</span>
        <div>
            <strong>Cr&eacute;&eacute; le</strong>
            <p>{{ $createdAt }}</p>
        </div>
    </article>

    <article>
        <span class="material-symbols-outlined" aria-hidden="true">manage_accounts</span>
        <div>
            <strong>Derni&egrave;re modif par</strong>
            <p>{{ $updatedBy }}</p>
        </div>
    </article>

    <article>
        <span class="material-symbols-outlined" aria-hidden="true">database</span>
        <div>
            <strong>ID Syst&egrave;me</strong>
            <p>{{ $systemId }}</p>
        </div>
    </article>
</section>
    </main>
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/app.js'
    ])
@endsection
