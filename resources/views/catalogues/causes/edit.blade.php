@extends('layouts.app')

@section('title', 'Modifier une cause probable')

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
    $isActive = (bool) old('is_active', $cause->is_active);
    $createdAt = optional($cause->created_at)->format('d M Y');
    $updatedAt = optional($cause->updated_at)->format('d M Y - H:i');

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
<div class="ceet-cause-edit-page">
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
                <img src="{{ asset('images/logo-ceet.png') }}" alt="Logo CEET">
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
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/app.js'
    ])
@endsection
