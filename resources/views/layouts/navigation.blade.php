@php
    use Illuminate\Support\Str;

    $catalogueOpen = request()->routeIs('catalogues.*');
    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first() ?? 'Utilisateur';
    $userInitials = strtoupper(Str::substr($currentUser?->name ?? 'CEET', 0, 2));
@endphp

<style>
    :root {
        --ceet-red: #ef2433;
        --ceet-red-dark: #ce1220;
        --ceet-gold: #f59e0b;
        --ceet-blue-night: #0f172a;
        --ceet-blue-deep: #1e293b;
        --ceet-blue-light: #e0e7ff;
        --ceet-gray-light: #f8fafc;
        --ceet-border-light: #e2e8f0;
        --ceet-text-muted: #64748b;
        --ceet-success: #22c55e;
    }

    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .ceet-sidebar {
        width: var(--ceet-sidebar-width);
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1030;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(226, 232, 240, 0.5);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, rgba(241, 245, 249, 0.95) 100%);
        backdrop-filter: blur(8px);
        animation: fadeInLeft 0.5s ease both;
    }

    .ceet-sidebar-brand {
        min-height: var(--ceet-topbar-height);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.3);
        display: flex;
        align-items: center;
        gap: 0.85rem;
        color: var(--ceet-red);
        text-decoration: none;
        transition: all 0.3s;
        animation: slideInDown 0.5s ease 0.1s both;
    }

    .ceet-sidebar-brand:hover {
        color: var(--ceet-red);
        transform: translateY(-2px);
    }

    .ceet-sidebar-brand-badge {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s ease;
        position: relative;
        padding: 4px;
    }

    .ceet-sidebar-brand-badge img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.08));
        transition: filter 0.3s ease;
    }

    .ceet-sidebar-brand:hover .ceet-sidebar-brand-badge img {
        filter: drop-shadow(0 4px 12px rgba(239, 36, 51, 0.2));
    }

    .ceet-sidebar-brand:hover .ceet-sidebar-brand-badge {
        transform: translateY(-2px);
    }

    .ceet-sidebar-brand-text {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .ceet-sidebar-menu {
        padding: 1rem;
        display: grid;
        gap: 0.4rem;
        flex: 1;
        overflow-y: auto;
        animation: fadeInLeft 0.5s ease 0.15s both;
    }

    .ceet-sidebar-menu::-webkit-scrollbar {
        width: 6px;
    }

    .ceet-sidebar-menu::-webkit-scrollbar-track {
        background: transparent;
    }

    .ceet-sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, 0.1);
        border-radius: 8px;
    }

    .ceet-nav-link {
        border-radius: 10px;
        border: 1px solid transparent;
        color: #1f2937;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.9rem;
        font-weight: 500;
        font-size: 0.9rem;
        background: transparent;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .ceet-nav-link:hover {
        background: linear-gradient(90deg, rgba(239, 36, 51, 0.08), transparent);
        color: #111827;
        border-color: rgba(239, 36, 51, 0.2);
        transform: translateX(4px);
    }

    .ceet-nav-link.active {
        background: linear-gradient(90deg, rgba(239, 36, 51, 0.12), rgba(239, 36, 51, 0.04));
        color: var(--ceet-red);
        font-weight: 600;
        border-left: 3px solid var(--ceet-red);
        padding-left: 13px;
    }

    .ceet-nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--ceet-red), transparent);
        border-radius: 0 2px 2px 0;
    }

    .ceet-nav-icon {
        width: 20px;
        height: 20px;
        color: currentColor;
        flex-shrink: 0;
        stroke: currentColor;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .ceet-nav-link:hover .ceet-nav-icon,
    .ceet-nav-link.active .ceet-nav-icon {
        transform: scale(1.1);
    }

    .ceet-catalogue-toggle.is-catalogue-current,
    .ceet-catalogue-toggle[aria-expanded="true"] {
        background: linear-gradient(90deg, rgba(239, 36, 51, 0.12), rgba(239, 36, 51, 0.04));
        border-color: rgba(239, 36, 51, 0.2);
        color: var(--ceet-red);
        border-left: 3px solid var(--ceet-red);
        padding-left: 13px;
    }

    .ceet-catalogue-toggle .ceet-chevron {
        margin-left: auto;
        transition: transform 0.2s ease;
    }

    .ceet-catalogue-toggle[aria-expanded="true"] .ceet-chevron {
        transform: rotate(180deg);
    }

    .ceet-catalogue-menu {
        padding-top: 0.15rem;
        display: grid;
        gap: 0.25rem;
    }

    .ceet-catalogue-menu .ceet-nav-link {
        padding-left: 3rem;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .ceet-catalogue-menu .ceet-nav-link:hover {
        opacity: 1;
    }

    .ceet-catalogue-menu .ceet-nav-link.active {
        padding-left: 3rem;
    }

    .ceet-sidebar-footer {
        margin-top: auto;
        border-top: 1px solid rgba(226, 232, 240, 0.3);
        padding: 1rem;
        animation: fadeInLeft 0.5s ease 0.2s both;
    }

    .ceet-sidebar-action {
        width: 100%;
        border: 1px solid rgba(239, 36, 51, 0.2);
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(239, 36, 51, 0.08), rgba(239, 36, 51, 0.04));
        color: var(--ceet-red);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 0.95rem;
        font-size: 0.92rem;
        transition: all 0.2s ease;
    }

    .ceet-sidebar-action:hover {
        background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
        color: white;
        border-color: var(--ceet-red);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(239, 36, 51, 0.25);
    }

    .ceet-topbar {
        position: fixed;
        top: 0;
        right: 0;
        left: var(--ceet-sidebar-width);
        z-index: 1025;
        min-height: var(--ceet-topbar-height);
        padding: 0 1.5rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        animation: slideInDown 0.5s ease both;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    .ceet-topbar-logo {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(226, 232, 240, 0.4);
        border: none;
        flex-shrink: 0;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 4px;
    }

    .ceet-topbar-logo:hover {
        transform: scale(1.08);
        background: rgba(239, 36, 51, 0.1);
        box-shadow: 0 4px 12px rgba(239, 36, 51, 0.15);
    }

    .ceet-topbar-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .ceet-topbar-content-right {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 1rem;
        margin-left: auto;
    }

    .ceet-topbar-icon-btn {
        border: none;
        border-radius: 10px;
        background: rgba(226, 232, 240, 0.4);
        color: #1f2937;
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
    }

    .ceet-topbar-icon-btn:hover {
        background: rgba(239, 36, 51, 0.1);
        color: var(--ceet-red);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 36, 51, 0.15);
    }

    .ceet-topbar-icon-btn.notification-active::after {
        content: '';
        position: absolute;
        top: 6px;
        right: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--ceet-red);
        animation: pulse-dot 2s ease-in-out infinite;
    }

    .ceet-topbar-logo {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(239, 36, 51, 0.1), rgba(239, 36, 51, 0.05));
        border: 2px solid rgba(239, 36, 51, 0.2);
        flex-shrink: 0;
        transition: all 0.3s ease;
        padding: 6px;
    }

    .ceet-topbar-logo:hover {
        transform: scale(1.1);
        background: linear-gradient(135deg, rgba(239, 36, 51, 0.15), rgba(239, 36, 51, 0.08));
        border-color: rgba(239, 36, 51, 0.4);
        box-shadow: 0 8px 20px rgba(239, 36, 51, 0.2);
    }

    .ceet-topbar-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

.ceet-topbar-clock {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.05), rgba(51, 65, 85, 0.03));
        border: 1px solid rgba(226, 232, 240, 0.6);
        animation: slideInDown 0.5s ease 0.2s both;
    }

    .ceet-topbar-clock-icon {
        width: 16px;
        height: 16px;
        color: var(--ceet-text-muted);
        flex-shrink: 0;
    }

    .ceet-topbar-time {
        font-family: 'Monaco', 'Courier New', monospace;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--ceet-blue-deep);
        letter-spacing: 0.3px;
    }

    .ceet-topbar-date {
        font-size: 0.7rem;
        color: var(--ceet-text-muted);
        font-weight: 500;
        margin-top: -2px;
    }

    .ceet-user-chip {
        border-left: 1px solid rgba(226, 232, 240, 0.5);
        padding-left: 1rem;
        margin-left: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideInDown 0.5s ease 0.1s both;
    }

    .ceet-user-info {
        text-align: right;
        display: flex;
        flex-direction: column;
    }

    .ceet-user-name {
        font-size: 0.92rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.2;
    }

    .ceet-user-role {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--ceet-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .ceet-user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--ceet-gold), #f97316);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        transition: all 0.2s ease;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .ceet-user-chip:hover .ceet-user-avatar {
        transform: scale(1.08);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
    }

    .ceet-mobile-topbar {
        position: fixed;
        inset: 0 0 auto 0;
        z-index: 1040;
        min-height: var(--ceet-topbar-height);
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.3);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .ceet-mobile-brand {
        color: var(--ceet-red);
        text-decoration: none;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .ceet-mobile-brand:hover {
        color: var(--ceet-red);
        transform: scale(1.05);
    }

    .ceet-mobile-offcanvas {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, rgba(241, 245, 249, 0.95) 100%);
        backdrop-filter: blur(8px);
    }

    .ceet-mobile-offcanvas .offcanvas-header {
        min-height: var(--ceet-topbar-height);
        border-bottom: 1px solid rgba(226, 232, 240, 0.3);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
    }

    .ceet-mobile-offcanvas .offcanvas-title {
        color: var(--ceet-red);
        font-weight: 700;
        font-size: 1.1rem;
    }

    .ceet-mobile-offcanvas .btn-close {
        filter: brightness(0.6);
    }

    .ceet-mobile-menu {
        display: grid;
        gap: 0.4rem;
    }

    .ceet-mobile-menu .ceet-nav-link {
        background: transparent;
    }

    @media (max-width: 991.98px) {
        .ceet-topbar {
            display: none;
        }

        .ceet-mobile-topbar {
            display: flex;
            animation: slideInDown 0.5s ease both;
        }
    }
</style>

<aside class="ceet-sidebar d-none d-lg-flex">
    <a class="ceet-sidebar-brand" href="{{ route('dashboard') }}">
        <span class="ceet-sidebar-brand-badge">
            <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET Logo">
        </span>
        <span class="ceet-sidebar-brand-text">CEET Gestion des Incidents</span>
    </a>

    <nav class="ceet-sidebar-menu">

        {{-- LIEN COMMUN : Tableau de bord (tous les rôles) --}}
        <a class="ceet-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
            <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
            </svg>
            <span>Tableau de bord</span>
        </a>

        {{-- LIEN COMMUN : Incidents (tous les rôles avec incidents.view) --}}
        @can('incidents.view')
            <a class="ceet-nav-link {{ request()->routeIs('incidents.index', 'incidents.show', 'incidents.create', 'incidents.edit') ? 'active' : '' }}"
               href="{{ route('incidents.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 9V13M12 17H12.01M12 3L21 19H3L12 3Z"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Incidents</span>
            </a>

            <a class="ceet-nav-link {{ request()->routeIs('incidents.en-cours') ? 'active' : '' }}"
               href="{{ route('incidents.en-cours') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 7V12L15 15" stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Incidents en cours</span>
            </a>

            <a class="ceet-nav-link {{ request()->routeIs('incidents.mine') ? 'active' : '' }}"
               href="{{ route('incidents.mine') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12A4 4 0 1 0 12 4A4 4 0 0 0 12 12ZM5 20A7 7 0 0 1 19 20"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Mes incidents</span>
            </a>
        @endcan

        {{-- CATALOGUE : Administrateur et Superviseur uniquement --}}
        @can('catalogues.view')
            <a @class([
                    'ceet-nav-link',
                    'ceet-catalogue-toggle',
                    'active fw-semibold is-catalogue-current' => $catalogueOpen,
                ])
               data-bs-toggle="collapse"
               href="#desktopCatalogueMenu"
               role="button"
               aria-expanded="{{ $catalogueOpen ? 'true' : 'false' }}"
               aria-controls="desktopCatalogueMenu">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6H20M4 12H20M4 18H20"
                          stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <span>Catalogue</span>
                <svg class="ceet-nav-icon ceet-chevron" viewBox="0 0 24 24" fill="none">
                    <path d="M6 9L12 15L18 9"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

            <div class="collapse {{ $catalogueOpen ? 'show' : '' }}" id="desktopCatalogueMenu">
                <div class="ceet-catalogue-menu">
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}"
                       href="{{ route('catalogues.departements.index') }}">Départements</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}"
                       href="{{ route('catalogues.types.index') }}">Types d'incidents</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}"
                       href="{{ route('catalogues.causes.index') }}">Causes</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}"
                       href="{{ route('catalogues.statuts.index') }}">Statuts</a>
                    <a class="ceet-nav-link {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}"
                       href="{{ route('catalogues.priorites.index') }}">Priorités</a>
                </div>
            </div>
        @endcan

        {{-- REPORTING : Administrateur et Superviseur uniquement --}}
        @can('reporting.view')
            <a class="ceet-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
               href="{{ route('reports.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M5 19V5M10 19V9M15 19V13M20 19V7"
                          stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <span>Reporting</span>
            </a>
        @endcan

        {{-- AUDIT : Administrateur et Superviseur uniquement --}}
        @role('Administrateur|Superviseur')
            <a class="ceet-nav-link {{ request()->routeIs('historique.*') ? 'active' : '' }}"
               href="{{ route('historique.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M3 12A9 9 0 1 0 6 5.3M3 4V9H8M12 7V12L15 15"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Audit</span>
            </a>
        @endrole

        {{-- UTILISATEURS : Administrateur et Superviseur uniquement --}}
        @can('users.view')
            <a class="ceet-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
               href="{{ route('users.index') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M16 19V17A4 4 0 0 0 12 13H8A4 4 0 0 0 4 17V19M20 19V17A4 4 0 0 0 17 13.1M12 5A3 3 0 1 1 12 11A3 3 0 0 1 12 5Z"
                          stroke="currentColor" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Utilisateurs</span>
            </a>
        @endcan

        {{-- PROFIL : tous les rôles --}}
        <a class="ceet-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
           href="{{ route('profile.edit') }}">
            <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z"
                      stroke="currentColor" stroke-width="1.8"/>
                <path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.79 16.88A2 2 0 0 1 16.96 19.71L16.9 19.65A1.65 1.65 0 0 0 15.08 19.32A1.65 1.65 0 0 0 14 20.85V21A2 2 0 0 1 10 21V20.91A1.65 1.65 0 0 0 8.92 19.38A1.65 1.65 0 0 0 7.1 19.71L7.04 19.77A2 2 0 1 1 4.21 16.94L4.27 16.88A1.65 1.65 0 0 0 4.6 15.06A1.65 1.65 0 0 0 3.07 13.98H3A2 2 0 0 1 3 9.98H3.09A1.65 1.65 0 0 0 4.62 8.9A1.65 1.65 0 0 0 4.29 7.08L4.23 7.02A2 2 0 1 1 7.06 4.19L7.12 4.25A1.65 1.65 0 0 0 8.94 4.58H9A1.65 1.65 0 0 0 10.06 3.05V3A2 2 0 1 1 14.06 3V3.09A1.65 1.65 0 0 0 15.14 4.62A1.65 1.65 0 0 0 16.96 4.29L17.02 4.23A2 2 0 1 1 19.85 7.06L19.79 7.12A1.65 1.65 0 0 0 19.46 8.94V9A1.65 1.65 0 0 0 21 10.06H21.09A2 2 0 0 1 21.09 14.06H21A1.65 1.65 0 0 0 19.47 15.14Z"
                      stroke="currentColor" stroke-width="1.3"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Réglages</span>
        </a>

    </nav>

    @can('incidents.create')
        <div class="ceet-sidebar-footer">
            <a href="{{ route('incidents.create') }}" class="ceet-sidebar-action">
                <span>+</span>
                <span>Nouvel Incident</span>
            </a>
        </div>
    @endcan
</aside>

<header class="ceet-topbar d-none d-lg-flex">
    <a href="{{ route('dashboard') }}" class="ceet-topbar-logo" title="Retour à l'accueil">
        <img src="{{ asset('images/logo-ceet.png') }}" alt="CEET Logo">
    </a>

    <div class="ceet-topbar-divider"></div>

    <div class="ceet-topbar-clock">
        <svg class="ceet-topbar-clock-icon" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
        <div>
            <div class="ceet-topbar-time" id="topbar-time">00:00</div>
        </div>
    </div>

    <button class="ceet-topbar-icon-btn" type="button" aria-label="Notifications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 17H20L18.6 15.6C18.2 15.2 18 14.7 18 14.2V10.5C18 7.4 15.9 4.8 13 4V3.5A1.5 1.5 0 0 0 10 3.5V4C7.1 4.8 5 7.4 5 10.5V14.2C5 14.7 4.8 15.2 4.4 15.6L3 17H8M9 17C9 18.7 10.3 20 12 20C13.7 20 15 18.7 15 17H9Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    @auth
        <div class="ceet-user-chip">
            <div class="ceet-user-info">
                <div class="ceet-user-name">{{ $currentUser?->name ?? 'Utilisateur CEET' }}</div>
                <div class="ceet-user-role">{{ $roleName }}</div>
            </div>
            <span class="ceet-user-avatar">{{ $userInitials }}</span>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="ceet-topbar-icon-btn" aria-label="Se deconnecter">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5A2 2 0 0 1 3 19V5A2 2 0 0 1 5 3H9M16 17L21 12L16 7M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>
    @endauth
</header>

<header class="ceet-mobile-topbar">
    <button class="ceet-topbar-icon-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Afficher la navigation">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
    <a class="ceet-mobile-brand" href="{{ route('dashboard') }}">CEET Incidents</a>
    <button class="ceet-topbar-icon-btn" type="button" aria-label="Notifications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 17H20L18.6 15.6C18.2 15.2 18 14.7 18 14.2V10.5C18 7.4 15.9 4.8 13 4V3.5A1.5 1.5 0 0 0 10 3.5V4C7.1 4.8 5 7.4 5 10.5V14.2C5 14.7 4.8 15.2 4.4 15.6L3 17H8M9 17C9 18.7 10.3 20 12 20C13.7 20 15 18.7 15 17H9Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
</header>

<div class="offcanvas offcanvas-start ceet-mobile-offcanvas d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">CEET Incidents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <nav class="ceet-mobile-menu">

            {{-- LIEN COMMUN : Tableau de bord (tous les rôles) --}}
            <a class="ceet-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.8"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            {{-- LIEN COMMUN : Incidents (tous les rôles avec incidents.view) --}}
            @can('incidents.view')
                <a class="ceet-nav-link {{ request()->routeIs('incidents.index', 'incidents.show', 'incidents.create', 'incidents.edit') ? 'active' : '' }}"
                   href="{{ route('incidents.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 9V13M12 17H12.01M12 3L21 19H3L12 3Z"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Incidents</span>
                </a>

                <a class="ceet-nav-link {{ request()->routeIs('incidents.en-cours') ? 'active' : '' }}"
                   href="{{ route('incidents.en-cours') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 7V12L15 15" stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Incidents en cours</span>
                </a>

                <a class="ceet-nav-link {{ request()->routeIs('incidents.mine') ? 'active' : '' }}"
                   href="{{ route('incidents.mine') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12A4 4 0 1 0 12 4A4 4 0 0 0 12 12ZM5 20A7 7 0 0 1 19 20"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Mes incidents</span>
                </a>
            @endcan

            {{-- CATALOGUE : Administrateur et Superviseur uniquement --}}
            @can('catalogues.view')
                <a @class([
                        'ceet-nav-link',
                        'ceet-catalogue-toggle',
                        'active fw-semibold is-catalogue-current' => $catalogueOpen,
                    ])
                   data-bs-toggle="collapse"
                   href="#mobileCatalogueMenu"
                   role="button"
                   aria-expanded="{{ $catalogueOpen ? 'true' : 'false' }}"
                   aria-controls="mobileCatalogueMenu">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M4 6H20M4 12H20M4 18H20"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <span>Catalogue</span>
                    <svg class="ceet-nav-icon ceet-chevron" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9L12 15L18 9"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <div class="collapse {{ $catalogueOpen ? 'show' : '' }}" id="mobileCatalogueMenu">
                    <div class="ceet-catalogue-menu">
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.departements.*') ? 'active' : '' }}"
                           href="{{ route('catalogues.departements.index') }}">Départements</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.types.*') ? 'active' : '' }}"
                           href="{{ route('catalogues.types.index') }}">Types d'incidents</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.causes.*') ? 'active' : '' }}"
                           href="{{ route('catalogues.causes.index') }}">Causes</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.statuts.*') ? 'active' : '' }}"
                           href="{{ route('catalogues.statuts.index') }}">Statuts</a>
                        <a class="ceet-nav-link {{ request()->routeIs('catalogues.priorites.*') ? 'active' : '' }}"
                           href="{{ route('catalogues.priorites.index') }}">Priorités</a>
                    </div>
                </div>
            @endcan

            {{-- REPORTING : Administrateur et Superviseur uniquement --}}
            @can('reporting.view')
                <a class="ceet-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   href="{{ route('reports.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M5 19V5M10 19V9M15 19V13M20 19V7"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <span>Reporting</span>
                </a>
            @endcan

            {{-- AUDIT : Administrateur et Superviseur uniquement --}}
            @role('Administrateur|Superviseur')
                <a class="ceet-nav-link {{ request()->routeIs('historique.*') ? 'active' : '' }}"
                   href="{{ route('historique.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12A9 9 0 1 0 6 5.3M3 4V9H8M12 7V12L15 15"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Audit</span>
                </a>
            @endrole

            {{-- UTILISATEURS : Administrateur et Superviseur uniquement --}}
            @can('users.view')
                <a class="ceet-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                   href="{{ route('users.index') }}">
                    <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                        <path d="M16 19V17A4 4 0 0 0 12 13H8A4 4 0 0 0 4 17V19M20 19V17A4 4 0 0 0 17 13.1M12 5A3 3 0 1 1 12 11A3 3 0 0 1 12 5Z"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Utilisateurs</span>
                </a>
            @endcan

            {{-- PROFIL : tous les rôles --}}
            <a class="ceet-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
               href="{{ route('profile.edit') }}">
                <svg class="ceet-nav-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z"
                          stroke="currentColor" stroke-width="1.8"/>
                    <path d="M19.4 15A1.65 1.65 0 0 0 19.73 16.82L19.79 16.88A2 2 0 0 1 16.96 19.71L16.9 19.65A1.65 1.65 0 0 0 15.08 19.32A1.65 1.65 0 0 0 14 20.85V21A2 2 0 0 1 10 21V20.91A1.65 1.65 0 0 0 8.92 19.38A1.65 1.65 0 0 0 7.1 19.71L7.04 19.77A2 2 0 1 1 4.21 16.94L4.27 16.88A1.65 1.65 0 0 0 4.6 15.06A1.65 1.65 0 0 0 3.07 13.98H3A2 2 0 0 1 3 9.98H3.09A1.65 1.65 0 0 0 4.62 8.9A1.65 1.65 0 0 0 4.29 7.08L4.23 7.02A2 2 0 1 1 7.06 4.19L7.12 4.25A1.65 1.65 0 0 0 8.94 4.58H9A1.65 1.65 0 0 0 10.06 3.05V3A2 2 0 1 1 14.06 3V3.09A1.65 1.65 0 0 0 15.14 4.62A1.65 1.65 0 0 0 16.96 4.29L17.02 4.23A2 2 0 1 1 19.85 7.06L19.79 7.12A1.65 1.65 0 0 0 19.46 8.94V9A1.65 1.65 0 0 0 21 10.06H21.09A2 2 0 0 1 21.09 14.06H21A1.65 1.65 0 0 0 19.47 15.14Z"
                          stroke="currentColor" stroke-width="1.3"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Réglages</span>
            </a>
        </nav>

        @can('incidents.create')
            <div class="mt-auto pt-3">
                <a href="{{ route('incidents.create') }}" class="ceet-sidebar-action">
                    <span>+</span>
                    <span>Nouvel Incident</span>
                </a>
            </div>
        @endcan
    </div>
</div>
