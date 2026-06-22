@php
    use Illuminate\Support\Facades\Route;

    $ceetCurrentUser = auth()->user();
    $ceetIsAdmin = $ceetCurrentUser?->isAdmin() ?? false;
    $ceetIsSupervisor = $ceetCurrentUser?->isSuperviseur() ?? false;
    $ceetIsOperator = $ceetCurrentUser?->isOperateur() ?? false;
    $ceetLinkClass = $linkClass ?? 'ceet-sidebar-nav-link';

    $ceetRoute = function (string $name, $params = [], string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $ceetNavItems = [];

    $ceetAddNavItem = function (string $label, string $icon, string $route, array $activePatterns) use (&$ceetNavItems, $ceetRoute) {
        $ceetNavItems[] = [
            'label' => $label,
            'icon' => $icon,
            'url' => $ceetRoute($route, [], '/' . str_replace('.', '/', $route)),
            'active' => request()->routeIs(...$activePatterns),
        ];
    };

    if ($ceetIsOperator) {
        $ceetAddNavItem('Tableau de bord', 'dashboard', 'dashboard', ['dashboard']);
        $ceetAddNavItem('Mes incidents', 'assignment_ind', 'incidents.mine', ['incidents.mine']);
        $ceetAddNavItem('Incidents en cours', 'schedule', 'incidents.en-cours', ['incidents.en-cours']);
    } elseif ($ceetIsSupervisor && ! $ceetIsAdmin) {
        $ceetAddNavItem('Tableau de bord', 'dashboard', 'dashboard', ['dashboard']);
        $ceetAddNavItem('Incidents', 'bolt', 'incidents.index', ['incidents.index', 'incidents.show', 'incidents.edit']);
        $ceetAddNavItem('Incidents en cours', 'schedule', 'incidents.en-cours', ['incidents.en-cours']);
        $ceetAddNavItem('Créer un incident', 'add_circle', 'incidents.create', ['incidents.create']);
        $ceetAddNavItem('Reports', 'assessment', 'reports.index', ['reports.*']);
    } else {
        $ceetAddNavItem('Tableau de bord', 'dashboard', 'dashboard', ['dashboard']);
        $ceetAddNavItem('Incidents', 'bolt', 'incidents.index', ['incidents.index', 'incidents.show', 'incidents.edit']);
        $ceetAddNavItem('Users', 'group', 'users.index', ['users.*']);
        $ceetAddNavItem('System Status', 'settings_input_component', 'system.status', ['system.*']);
        $ceetAddNavItem('Catalogs', 'menu_book', 'catalogues.index', ['catalogues.*']);
        $ceetAddNavItem('Reports', 'assessment', 'reports.index', ['reports.*']);
        $ceetAddNavItem('Logs système', 'history', 'historique.index', ['historique.*']);
    }

    /* Lot 3I - CEET precise sidebar active routes */
    foreach ($ceetNavItems as &$ceetNavItem) {
        if (($ceetNavItem['label'] ?? null) === 'Incidents') {
            $ceetNavItem['active'] = request()->routeIs('incidents.index', 'incidents.show', 'incidents.edit')
                && ! request()->routeIs('incidents.create', 'incidents.en-cours', 'incidents.mine');
        }

        if (($ceetNavItem['label'] ?? null) === 'Créer un incident') {
            $ceetNavItem['active'] = request()->routeIs('incidents.create');
        }

        if (($ceetNavItem['label'] ?? null) === 'Incidents en cours') {
            $ceetNavItem['active'] = request()->routeIs('incidents.en-cours');
        }

        if (($ceetNavItem['label'] ?? null) === 'Mes incidents') {
            $ceetNavItem['active'] = request()->routeIs('incidents.mine');
        }
    }
    unset($ceetNavItem);

@endphp

@foreach($ceetNavItems as $ceetItem)
    <a href="{{ $ceetItem['url'] }}" class="{{ $ceetLinkClass }} {{ $ceetItem['active'] ? 'is-active' : '' }}" @if($ceetItem['active']) aria-current="page" @endif data-ceet-link>
        <span class="material-symbols-outlined" aria-hidden="true">{{ $ceetItem['icon'] }}</span>
        <span>{{ $ceetItem['label'] }}</span>
    </a>
@endforeach
