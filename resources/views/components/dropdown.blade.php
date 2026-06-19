@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => '',
])

@php
    $alignmentClass = $align === 'left' ? 'dropdown-menu-start' : 'dropdown-menu-end';
    $widthStyle = is_numeric($width) ? 'min-width: '.((int) $width / 4).'rem;' : '';
@endphp

<details {{ $attributes->merge(['class' => 'ceet-dropdown dropdown']) }}>
    <summary class="ceet-dropdown-trigger dropdown-toggle list-unstyled">
        {{ $trigger ?? '' }}
    </summary>

    <div class="ceet-dropdown-menu dropdown-menu show position-absolute {{ $alignmentClass }} {{ $contentClasses }}" style="{{ $widthStyle }}">
        {{ $content }}
    </div>
</details>
