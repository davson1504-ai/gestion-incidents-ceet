{{--
    CEET — Boutons d'actions pour lignes de tableau
    
    Utilisation :
    <x-table-actions
        show="{{ route('incidents.show', $incident) }}"
        edit="{{ route('incidents.edit', $incident) }}"
        :deleteAction="route('incidents.destroy', $incident)"
        deleteLabel="Supprimer l'incident"
    />
--}}
@props([
    'show'        => null,
    'edit'        => null,
    'deleteAction' => null,
    'deleteLabel'  => 'Supprimer',
    'deleteMethod' => 'DELETE',
    'extraActions' => null,
])

<div class="ceet-table-actions">

    @if($show)
        <a href="{{ $show }}"
           class="ceet-btn ceet-btn-secondary ceet-btn-icon ceet-btn-sm"
           aria-label="Voir les détails">
            <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
        </a>
    @endif

    @if($edit)
        <a href="{{ $edit }}"
           class="ceet-btn ceet-btn-secondary ceet-btn-icon ceet-btn-sm"
           aria-label="Modifier">
            <span class="material-symbols-outlined" aria-hidden="true">edit</span>
        </a>
    @endif

    {{ $slot }}

    @if($deleteAction)
        <form method="POST"
              action="{{ $deleteAction }}"
              onsubmit="return confirm('{{ $deleteLabel }} ?\n\nCette action est irréversible.')">
            @csrf
            @method($deleteMethod)
            <button type="submit"
                    class="ceet-btn ceet-btn-danger-outline ceet-btn-icon ceet-btn-sm"
                    aria-label="{{ $deleteLabel }}">
                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
            </button>
        </form>
    @endif

</div>
