{{--
    CEET — Badge de priorité d'incident
    
    Utilisation :
    <x-priority-badge :priorite="$incident->priorite" />
--}}
@props([
    'priorite' => null,
    'code'     => null,
    'label'    => null,
])

@php
    $priorityCode  = $code ?? ($priorite?->code ?? 'MEDIUM');
    $priorityLabel = $label ?? ($priorite?->libelle ?? $priorityCode);

    $cssClass = match(strtoupper($priorityCode)) {
        'CRITIQUE' => 'ceet-priority-critique',
        'HAUTE'    => 'ceet-priority-haute',
        'MEDIUM'   => 'ceet-priority-medium',
        'BASSE'    => 'ceet-priority-basse',
        default    => 'ceet-badge-neutral',
    };
@endphp

<span class="ceet-badge {{ $cssClass }}">
    {{ $priorityLabel }}
</span>
