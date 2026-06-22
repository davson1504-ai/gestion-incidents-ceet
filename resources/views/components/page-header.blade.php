{{--
    CEET — En-tête de page standard
    
    Utilisation :
    <x-page-header
        title="Titre de la page"
        subtitle="Sous-titre optionnel"
    >
        <x-slot name="actions">
            <a href="..." class="ceet-btn ceet-btn-primary">Créer</a>
        </x-slot>
    </x-page-header>
--}}
@props([
    'title'    => '',
    'subtitle' => '',
    'kicker'   => '',
])

<div class="ceet-page-header">
    <div class="ceet-page-header-info">
        @if($kicker)
            <span class="ceet-page-kicker">{{ $kicker }}</span>
        @endif
        <h1 class="ceet-page-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="ceet-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="ceet-page-header-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
