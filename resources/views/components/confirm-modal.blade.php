{{--
    CEET — Modal de confirmation générique
    
    Utilisation :
    <x-confirm-modal
        id="confirm-delete"
        title="Supprimer l'incident"
        message="Cette action est irréversible. Continuer ?"
        action="{{ route('incidents.destroy', $incident) }}"
        method="DELETE"
        confirmLabel="Supprimer"
        variant="danger"
    />
    
    Déclencheur :
    <button onclick="document.getElementById('confirm-delete').removeAttribute('hidden')">
--}}
@props([
    'id'           => 'ceet-confirm-modal',
    'title'        => 'Confirmer l\'action',
    'message'      => 'Voulez-vous continuer cette action ?',
    'action'       => '#',
    'method'       => 'POST',
    'confirmLabel' => 'Confirmer',
    'cancelLabel'  => 'Annuler',
    'variant'      => 'danger',   // danger | primary
])

<div id="{{ $id }}" class="ceet-modal-backdrop" hidden>
    <div class="ceet-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">

        <div class="ceet-modal-header">
            <span class="material-symbols-outlined" style="color: var(--ceet-{{ $variant }});" aria-hidden="true">
                {{ $variant === 'danger' ? 'warning' : 'help_outline' }}
            </span>
            <h2 class="ceet-modal-title" id="{{ $id }}-title">{{ $title }}</h2>
            <button
                type="button"
                class="ceet-modal-close"
                onclick="document.getElementById('{{ $id }}').setAttribute('hidden', '')"
                aria-label="Fermer">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="ceet-modal-body">
            <p>{{ $message }}</p>
            {{ $slot }}
        </div>

        <div class="ceet-modal-footer">
            <button
                type="button"
                class="ceet-btn ceet-btn-secondary"
                onclick="document.getElementById('{{ $id }}').setAttribute('hidden', '')">
                {{ $cancelLabel }}
            </button>

            <form method="POST" action="{{ $action }}">
                @csrf
                @if(!in_array(strtoupper($method), ['GET', 'POST']))
                    @method($method)
                @endif
                <button type="submit" class="ceet-btn ceet-btn-{{ $variant }}">
                    {{ $confirmLabel }}
                </button>
            </form>
        </div>

    </div>
</div>

{{-- Fermeture au clic sur le backdrop --}}
<script>
(function() {
    const el = document.getElementById('{{ $id }}');
    if (!el) return;
    el.addEventListener('click', function(e) {
        if (e.target === el) el.setAttribute('hidden', '');
    });
})();
</script>
