@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'focusable' => false,
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl', '2xl' => 'modal-xl',
        default => '',
    };
@endphp

<dialog
    id="{{ $name }}"
    data-ceet-modal="{{ $name }}"
    @if($show) open @endif
    @if($focusable) autofocus @endif
    {{ $attributes->merge(['class' => 'ceet-modal border-0 p-0 bg-transparent']) }}
>
    <div class="modal-dialog modal-dialog-centered {{ $maxWidthClass }}">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</dialog>

@once
    <script>
        window.openCeetModal = window.openCeetModal || function (name) {
            const modal = document.querySelector('[data-ceet-modal="' + name + '"]');
            if (modal && typeof modal.showModal === 'function') modal.showModal();
        };

        window.closeCeetModal = window.closeCeetModal || function (name) {
            const modal = document.querySelector('[data-ceet-modal="' + name + '"]');
            if (modal && typeof modal.close === 'function') modal.close();
        };

        document.addEventListener('click', function (event) {
            const dialog = event.target.closest('dialog[data-ceet-modal]');
            if (dialog && event.target === dialog && typeof dialog.close === 'function') {
                dialog.close();
            }
        });
    </script>
@endonce
