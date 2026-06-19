@props([
    'title' => 'CEET Incidents',
    'subtitle' => 'Electrical Management',
    'user' => auth()->user(),
    'navItems' => collect(),
    'iconPath' => [],
    'catalogueOpen' => false,
    'isOperator' => false,
])

@php
    $items = collect($navItems ?? []);
    $icons = is_array($iconPath) ? $iconPath : [];
    $fullName = trim((string) ($user?->name ?? 'Utilisateur'));
    $email = (string) ($user?->email ?? '');
    $roleName = $user && method_exists($user, 'getRoleNames')
        ? (string) ($user->getRoleNames()->first() ?? 'Utilisateur')
        : 'Utilisateur';

    $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = collect($parts)
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'CE';
@endphp

<aside {{ $attributes->merge(['class' => 'ceet-sidebar']) }}>
    <div class="ceet-sidebar-brand">
        <x-application-logo class="ceet-sidebar-logo" />
        <div>
            <strong>{{ $title }}</strong>
            <span>{{ $subtitle }}</span>
        </div>
    </div>

    <nav class="ceet-sidebar-nav" aria-label="Navigation principale">
        @if($items->isNotEmpty())
            @foreach($items as $item)
                @php
                    $label = data_get($item, 'label', 'Menu');
                    $href = data_get($item, 'route', '#') ?: '#';
                    $active = (bool) data_get($item, 'active', false);
                    $iconKey = data_get($item, 'icon');
                    $iconSvg = $icons[$iconKey] ?? null;
                @endphp

                <a href="{{ $href }}" class="ceet-sidebar-nav-link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif data-ceet-link>
                    @if($iconSvg)
                        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            {!! $iconSvg !!}
                        </svg>
                    @else
                        <span class="ceet-sidebar-nav-dot" aria-hidden="true"></span>
                    @endif
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </nav>

    @isset($footer)
        {{ $footer }}
    @else
        <div class="ceet-sidebar-user">
            <div class="ceet-sidebar-user-main">
                <span class="ceet-sidebar-avatar">{{ $initials }}</span>
                <div>
                    <strong>{{ $fullName }}</strong>
                    <small>{{ $roleName }}</small>
                    @if($email !== '')
                        <em>{{ $email }}</em>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : '#' }}" class="ceet-sidebar-logout-form">
                @csrf
                <button type="submit" class="ceet-sidebar-logout-button">
                    <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    Se déconnecter
                </button>
            </form>
        </div>
    @endisset
</aside>
