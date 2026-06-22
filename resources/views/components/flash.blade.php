{{--
    CEET — Messages flash (succès, erreur, warning, info)
    Utilisation : <x-flash /> dans le layout
--}}
@if(session()->hasAny(['success', 'error', 'warning', 'info', 'status']))

    @if(session('success'))
        <div class="ceet-alert ceet-alert-success" role="alert">
            <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
            <div class="ceet-alert-content">
                <span class="ceet-alert-title">Succès</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="ceet-alert ceet-alert-danger" role="alert">
            <span class="material-symbols-outlined" aria-hidden="true">error</span>
            <div class="ceet-alert-content">
                <span class="ceet-alert-title">Erreur</span>
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="ceet-alert ceet-alert-warning" role="alert">
            <span class="material-symbols-outlined" aria-hidden="true">warning</span>
            <div class="ceet-alert-content">
                <span class="ceet-alert-title">Attention</span>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="ceet-alert ceet-alert-info" role="alert">
            <span class="material-symbols-outlined" aria-hidden="true">info</span>
            <div class="ceet-alert-content">{{ session('info') }}</div>
        </div>
    @endif

    @if(session('status'))
        <div class="ceet-alert ceet-alert-info" role="status">
            <span class="material-symbols-outlined" aria-hidden="true">info</span>
            <div class="ceet-alert-content">{{ session('status') }}</div>
        </div>
    @endif

@endif

{{-- Erreurs de validation --}}
@if($errors->any())
    <div class="ceet-alert ceet-alert-danger" role="alert">
        <span class="material-symbols-outlined" aria-hidden="true">error</span>
        <div class="ceet-alert-content">
            <span class="ceet-alert-title">Erreurs de validation</span>
            <ul style="margin: 4px 0 0; padding-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
