{{--
    CEET — Carte statistique
    
    Utilisation :
    <x-stat-card
        label="Total incidents"
        value="142"
        icon="bolt"
        meta="12 résolus aujourd'hui"
    />
--}}
@props([
    'label'   => '',
    'value'   => '0',
    'icon'    => null,
    'meta'    => '',
    'variant' => '',  // '' | 'danger' | 'success' | 'warning'
])

<article class="ceet-stat-card {{ $variant ? 'is-' . $variant : '' }}">
    <div class="ceet-stat-card-head">
        <span class="ceet-stat-card-label">{{ $label }}</span>
        @if($icon)
            <span class="material-symbols-outlined ceet-stat-card-icon" aria-hidden="true">{{ $icon }}</span>
        @endif
    </div>
    <div class="ceet-stat-card-value">{{ $value }}</div>
    @if($meta)
        <div class="ceet-stat-card-meta">{{ $meta }}</div>
    @endif
    {{ $slot }}
</article>
