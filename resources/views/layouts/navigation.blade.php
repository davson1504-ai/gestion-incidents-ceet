@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Utilisateur';
    $userInitials = Str::upper(Str::substr($currentUser?->name ?? 'CE', 0, 2));
    $isAdmin = $currentUser?->isAdmin() ?? false;
    $isSupervisor = $currentUser?->isSuperviseur() ?? false;
    $isOperator = $currentUser?->isOperateur() ?? false;
    $catalogueOpen = request()->routeIs('catalogues.*');

    $canViewIncidents = ($currentUser?->can('incidents.view') ?? false) || ($currentUser?->can('incidents.view.assigned') ?? false);
    $canCreateIncident = $currentUser?->can('incidents.create') ?? false;
    $canViewUsers = $currentUser?->can('users.view') ?? false;
    $canViewCatalogues = $currentUser?->can('catalogues.view') ?? false;
    $canViewReports = $currentUser?->can('reporting.view') ?? false;
    $canViewSystem = $isAdmin && Route::has('system.status');
    $canViewLogs = $isAdmin && Route::has('historique.index');

    $topbarTitle = match (true) {
        request()->routeIs('dashboard') => 'Tableau de bord',
        request()->routeIs('incidents.mine') => 'Mes incidents',
        request()->routeIs('incidents.en-cours') => 'Incidents en cours',
        request()->routeIs('incidents.create') => 'Créer incident',
        request()->routeIs('incidents.*') => 'Incidents',
        request()->routeIs('reports.*') => 'Rapports',
        request()->routeIs('historique.*') => 'Historique',
        request()->routeIs('users.*') => 'Administration',
        request()->routeIs('catalogues.*') => 'Catalogues',
        request()->routeIs('system.*') => 'System Status',
        request()->routeIs('profile.*') => 'Paramètres',
        default => 'Espace CEET',
    };

    $navItems = collect();

    $navItems->push([
        'label' => 'Tableau de bord',
        'route' => Route::has('dashboard') ? route('dashboard') : '#',
        'active' => request()->routeIs('dashboard'),
        'icon' => 'grid',
    ]);

    if ($isOperator) {
        $navItems->push([
            'label' => 'Mes incidents',
            'route' => Route::has('incidents.mine') ? route('incidents.mine') : '#',
            'active' => request()->routeIs('incidents.mine'),
            'icon' => 'users',
        ]);

        $navItems->push([
            'label' => 'Incidents en cours',
            'route' => Route::has('incidents.en-cours') ? route('incidents.en-cours') : '#',
            'active' => request()->routeIs('incidents.en-cours'),
            'icon' => 'clock',
        ]);
    } else {
        if ($canViewIncidents) {
            $navItems->push([
                'label' => 'Incidents',
                'route' => Route::has('incidents.index') ? route('incidents.index') : '#',
                'active' => request()->routeIs('incidents.index', 'incidents.show', 'incidents.edit'),
                'icon' => 'alert',
            ]);

            $navItems->push([
                'label' => 'Incidents en cours',
                'route' => Route::has('incidents.en-cours') ? route('incidents.en-cours') : '#',
                'active' => request()->routeIs('incidents.en-cours'),
                'icon' => 'clock',
            ]);
        }

        if ($canCreateIncident) {
            $navItems->push([
                'label' => 'Créer un incident',
                'route' => Route::has('incidents.create') ? route('incidents.create') : '#',
                'active' => request()->routeIs('incidents.create'),
                'icon' => 'plus',
            ]);
        }

        if ($canViewUsers) {
            $navItems->push([
                'label' => 'Users',
                'route' => Route::has('users.index') ? route('users.index') : '#',
                'active' => request()->routeIs('users.*'),
                'icon' => 'admin',
            ]);
        }

        if (($isAdmin ?? false) && ($canViewSystem ?? false)) {
            $navItems->push([
                'label' => 'System Status',
                'route' => route('system.status'),
                'active' => request()->routeIs('system.*'),
                'icon' => 'system',
            ]);
        }

        if (($isAdmin ?? false) && ($canViewCatalogues ?? false)) {
            $navItems->push([
                'label' => 'Catalogs',
                'route' => Route::has('catalogues.index') ? route('catalogues.index') : '#',
                'active' => request()->routeIs('catalogues.*'),
                'icon' => 'catalogue',
            ]);
        }

        if ($canViewReports) {
            $navItems->push([
                'label' => 'Reports',
                'route' => Route::has('reports.index') ? route('reports.index') : '#',
                'active' => request()->routeIs('reports.*'),
                'icon' => 'report',
            ]);
        }

        if ($canViewLogs) {
            $navItems->push([
                'label' => 'Logs système',
                'route' => route('historique.index'),
                'active' => request()->routeIs('historique.*'),
                'icon' => 'history',
            ]);
        }
    }


    // CEET supervisor clean navigation override
    // Superviseur : pas de Users, pas de Catalogs, pas de System Status, pas de Logs système.
    if (($isSupervisor ?? false) && !($isAdmin ?? false)) {
        $navItems = collect([
            [
                'label' => 'Tableau de bord',
                'route' => Route::has('dashboard') ? route('dashboard') : '#',
                'active' => request()->routeIs('dashboard'),
                'icon' => 'grid',
            ],
            [
                'label' => 'Incidents',
                'route' => Route::has('incidents.index') ? route('incidents.index') : '#',
                'active' => request()->routeIs('incidents.index', 'incidents.show', 'incidents.edit'),
                'icon' => 'alert',
            ],
            [
                'label' => 'Incidents en cours',
                'route' => Route::has('incidents.en-cours') ? route('incidents.en-cours') : '#',
                'active' => request()->routeIs('incidents.en-cours'),
                'icon' => 'clock',
            ],
        ]);

        if (Route::has('incidents.create')) {
            $navItems->push([
                'label' => 'Créer un incident',
                'route' => route('incidents.create'),
                'active' => request()->routeIs('incidents.create'),
                'icon' => 'plus',
            ]);
        }

        if (($canViewReports ?? false) && Route::has('reports.index')) {
            $navItems->push([
                'label' => 'Reports',
                'route' => route('reports.index'),
                'active' => request()->routeIs('reports.*'),
                'icon' => 'report',
            ]);
        }
    }

    $iconPath = [
        'grid' => '<rect x="4" y="4" width="6" height="6" rx="1.5"/><rect x="14" y="4" width="6" height="6" rx="1.5"/><rect x="4" y="14" width="6" height="6" rx="1.5"/><rect x="14" y="14" width="6" height="6" rx="1.5"/>',
        'alert' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16h.01"/>',
        'users' => '<path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1"/><circle cx="10" cy="7" r="3"/><path d="M20 19v-1a4 4 0 0 0-3-3.87"/><path d="M16 4.13a3 3 0 0 1 0 5.74"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'plus' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'report' => '<path d="M6 20V4h9l3 3v13H6Z"/><path d="M9 13h6M9 16h6M9 10h3"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 3-6.7M3 4v5h5"/><path d="M12 7v5l3 2"/>',
        'admin' => '<path d="M12 3l7 3v5c0 4.5-2.8 8.3-7 10-4.2-1.7-7-5.5-7-10V6l7-3Z"/><path d="M9 12l2 2 4-4"/>',
        'catalogue' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'system' => '<path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6V20a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1H4a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6V4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.36.4.7.6 1H20a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-.5 1Z"/>',
    ];
@endphp

<x-sidebar
    :nav-items="$navItems"
    :icon-path="$iconPath"
    :catalogue-open="$catalogueOpen"
    :is-operator="$isOperator"
/>

<x-topbar
    :topbar-title="$topbarTitle"
    :is-admin="$isAdmin"
    :is-operator="$isOperator"
    :current-user="$currentUser"
    :role-name="$roleName"
    :user-initials="$userInitials"
    :nav-items="$navItems"
    :icon-path="$iconPath"
    :catalogue-open="$catalogueOpen"
/>
