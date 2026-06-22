{{--
    CEET — Badge de statut d'incident
    
    Utilisation :
    <x-status-badge :statut="$incident->status" />
    ou : <x-status-badge code="OUVERT" label="Ouvert" />
--}}
@props([
    'statut' => null,
    'code'   => null,
    'label'  => null,
])

@php
    $statusCode  = $code ?? ($statut?->code ?? 'INCONNU');
    $statusLabel = $label ?? ($statut?->libelle ?? $statusCode);

    $cssClass = match(strtoupper($statusCode)) {
        'OUVERT'    => 'ceet-status-ouvert',
        'AFFECTE'   => 'ceet-status-affecte',
        'EN_COURS'  => 'ceet-status-en-cours',
        'RESOLU'    => 'ceet-status-resolu',
        'RAPPORTE'  => 'ceet-status-rapporte',
        'VALIDE'    => 'ceet-status-valide',
        'CLOTURE'   => 'ceet-status-cloture',
        default     => 'ceet-badge-neutral',
    };
@endphp

<span class="ceet-badge {{ $cssClass }}">
    <span class="ceet-badge-dot" aria-hidden="true"></span>
    {{ $statusLabel }}
</span>
