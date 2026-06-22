@php
    $ceetToastMessages = [];

    foreach ([
        'success' => 'success',
        'status' => 'success',
        'message' => 'info',
        'info' => 'info',
        'warning' => 'warning',
        'error' => 'error',
        'danger' => 'error',
    ] as $key => $type) {
        $value = session($key);

        if ($value) {
            $ceetToastMessages[] = [
                'type' => $type,
                'message' => is_array($value) ? implode(' ', $value) : (string) $value,
            ];
        }
    }

    if ($errors->any()) {
        $ceetToastMessages[] = [
            'type' => 'error',
            'message' => 'Veuillez vérifier les champs du formulaire.',
        ];
    }
@endphp

@if(count($ceetToastMessages))
    <div class="ceet-flash-toast-seed" hidden aria-hidden="true">
        @foreach($ceetToastMessages as $toast)
            <div
                data-ceet-flash-toast
                data-type="{{ $toast['type'] }}"
                data-message="{{ e($toast['message']) }}"
            ></div>
        @endforeach
    </div>
@endif
