@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('page_css')
    @vite('resources/css/pages/users.css')
@endsection

@php
    use Illuminate\Pagination\AbstractPaginator;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $currentUser = auth()->user();

    $users = $users ?? collect();
    $roles = collect($roles ?? []);
    $departements = collect($departements ?? []);
    $stats = $stats ?? [];
    $filters = $filters ?? [];

    $userItems = $users instanceof AbstractPaginator ? $users->getCollection() : collect($users);

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $initialsFor = function (?string $name): string {
        $name = trim((string) $name);

        if ($name === '') {
            return 'CE';
        }

        return collect(preg_split('/\s+/', $name))
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('') ?: 'CE';
    };

    $roleLabelFor = function ($user): string {
        if (isset($user->roles) && $user->roles && method_exists($user->roles, 'pluck') && $user->roles->count()) {
            return $user->roles->pluck('name')->map(fn ($role) => Str::headline((string) $role))->join(', ');
        }

        $role = data_get($user, 'role')
            ?? data_get($user, 'role_name')
            ?? data_get($user, 'profil')
            ?? data_get($user, 'type');

        return $role ? Str::headline((string) $role) : 'Utilisateur';
    };

    $statusLabelFor = function ($user): string {
        $rawStatus = data_get($user, 'status') ?? data_get($user, 'statut');

        if ($rawStatus) {
            return Str::upper(Str::headline((string) $rawStatus));
        }

        if (data_get($user, 'is_active') === false) {
            return 'INACTIF';
        }

        if (data_get($user, 'invitation_sent_at') && ! data_get($user, 'email_verified_at')) {
            return 'EN ATTENTE';
        }

        if (data_get($user, 'is_active') || data_get($user, 'email_verified_at')) {
            return 'ACTIF';
        }

        return 'EN ATTENTE';
    };

    $createdAtFor = function ($user): string {
        $date = data_get($user, 'created_at');

        if ($date instanceof \Carbon\CarbonInterface) {
            return $date->translatedFormat('d M Y');
        }

        return $date ? (string) $date : '-';
    };

    $totalUsers = data_get($stats, 'totalUsers')
        ?? data_get($stats, 'total')
        ?? (method_exists($users, 'total') ? $users->total() : $userItems->count());

    $activeUsers = data_get($stats, 'active')
        ?? $userItems->filter(fn ($user) => (bool) data_get($user, 'is_active'))->count();

    $activeOperators = data_get($stats, 'activeOperators') ?? 0;
    $newThisWeek = data_get($stats, 'newThisWeek') ?? 0;

    $search = request('q', data_get($filters, 'q', ''));
    $selectedRole = request('role', data_get($filters, 'role', ''));
    $selectedStatus = request('is_active', data_get($filters, 'is_active', ''));

    if (method_exists($users, 'appends')) {
        $users->appends(request()->query());
    }

    $from = method_exists($users, 'firstItem') ? ($users->firstItem() ?? 0) : ($totalUsers > 0 ? 1 : 0);
    $to = method_exists($users, 'lastItem') ? ($users->lastItem() ?? $userItems->count()) : $userItems->count();
    $lastPage = method_exists($users, 'lastPage') ? $users->lastPage() : 1;
    $currentPage = method_exists($users, 'currentPage') ? $users->currentPage() : 1;
    $canManageUsers = $currentUser?->can('users.manage') ?? false;
@endphp

@section('content')

<section class="page-section users-page">
    <header class="page-header">
        <div>
            <h2 class="page-title">Gestion des Utilisateurs</h2>

            <nav class="page-subtitle" aria-label="Fil d'Ariane">
                <span>Administration</span>
                <span>/</span>
                <strong>Utilisateurs</strong>
            </nav>
        </div>

        @if($canManageUsers && Route::has('users.create'))
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <span class="material-symbols-outlined" aria-hidden="true">add</span>
                Nouvel utilisateur
            </a>
        @endif
    </header>

    <section class="ceet-users-admin-stats" aria-label="Indicateurs utilisateurs">
        <article class="stat-card">
            <span>Total utilisateurs</span>
            <strong>{{ number_format((int) $totalUsers, 0, ',', ' ') }}</strong>
        </article>

        <article class="stat-card">
            <span>Comptes actifs</span>
            <strong>{{ number_format((int) $activeUsers, 0, ',', ' ') }}</strong>
        </article>

        <article class="stat-card">
            <span>Opérateurs actifs</span>
            <strong>{{ number_format((int) $activeOperators, 0, ',', ' ') }}</strong>
        </article>

        <article class="stat-card">
            <span>Nouveaux cette semaine</span>
            <strong>{{ number_format((int) $newThisWeek, 0, ',', ' ') }}</strong>
        </article>
    </section>

    <section class="filters-card">
        <form action="{{ $safeRoute('users.index', [], '/users') }}" method="GET" class="ceet-users-admin-filters">
            <div>
                <label for="q">Recherche</label>
                <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Nom, email, téléphone...">
            </div>

            <div>
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $role)
                        @php
                            $roleValue = is_object($role) ? ($role->name ?? $role->id) : $role;
                            $roleText = is_object($role) ? ($role->name ?? $role->libelle ?? $role->id) : $role;
                        @endphp
                        <option value="{{ $roleValue }}" @selected((string) $selectedRole === (string) $roleValue)>
                            {{ Str::headline((string) $roleText) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="is_active">Statut</label>
                <select id="is_active" name="is_active">
                    <option value="">Tous</option>
                    <option value="1" @selected((string) $selectedStatus === '1')>Actifs</option>
                    <option value="0" @selected((string) $selectedStatus === '0')>Inactifs</option>
                </select>
            </div>

            <div class="action-bar">
                <a href="{{ $safeRoute('users.index', [], '/users') }}" class="btn btn-outline">Réinitialiser</a>
                <button type="submit" class="btn btn-primary is-filter">Appliquer</button>
            </div>
        </form>
    </section>

    <section class="content-card">
        <div class="ceet-users-admin-table-wrap">
            <table class="data-table ceet-users-admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Téléphone</th>
                        <th class="is-center">Statut</th>
                        <th>Date de création</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        @php
                            $userName = trim((string) ($user->name ?? (($user->prenom ?? '').' '.($user->nom_famille ?? ''))));
                            $userName = $userName !== '' ? $userName : 'Utilisateur';
                            $userEmail = $user->email ?? '-';
                            $userPhone = $user->telephone ?? '-';
                            $userRole = $roleLabelFor($user);
                            $userStatus = $statusLabelFor($user);
                            $isActive = Str::contains(Str::lower(Str::ascii($userStatus)), ['actif', 'active']);
                        @endphp

                        <tr>
                            <td>
                                <div class="ceet-users-admin-identity">
                                    <div class="ceet-users-admin-user-avatar">{{ $initialsFor($userName) }}</div>
                                    <strong>{{ $userName }}</strong>
                                </div>
                            </td>

                            <td class="is-muted">{{ $userEmail }}</td>

                            <td>
                                <span class="ceet-users-admin-role-badge">{{ $userRole }}</span>
                            </td>

                            <td class="is-muted">{{ $userPhone }}</td>

                            <td class="is-center">
                                <span class="ceet-users-admin-status-badge {{ $isActive ? 'is-active' : 'is-inactive' }}">
                                    {{ $userStatus }}
                                </span>
                            </td>

                            <td class="is-muted">{{ $createdAtFor($user) }}</td>

                            <td class="is-right">
                                <div class="ceet-users-admin-row-actions">
                                    @if($canManageUsers && Route::has('users.edit'))
                                        <a href="{{ route('users.edit', $user) }}" class="ceet-users-admin-action-btn" aria-label="Modifier {{ $userName }}">
                                            <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                        </a>
                                    @endif

                                    @if($canManageUsers && Route::has('users.destroy') && (int) $user->id !== (int) auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer ou désactiver cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="ceet-users-admin-action-btn is-danger" aria-label="Supprimer {{ $userName }}">
                                                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">Aucun utilisateur trouvé.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="ceet-users-admin-table-footer">
            <span>Affichage de {{ $from }} à {{ $to }} sur {{ number_format((int) $totalUsers, 0, ',', ' ') }} utilisateur(s).</span>

            @if(method_exists($users, 'links') && $lastPage > 1)
                <div class="ceet-users-admin-pagination">
                    @if(method_exists($users, 'previousPageUrl') && $users->previousPageUrl())
                        <a href="{{ $users->previousPageUrl() }}">Précédent</a>
                    @else
                        <span class="is-disabled">Précédent</span>
                    @endif

                    <strong>{{ $currentPage }} / {{ $lastPage }}</strong>

                    @if(method_exists($users, 'nextPageUrl') && $users->nextPageUrl())
                        <a href="{{ $users->nextPageUrl() }}">Suivant</a>
                    @else
                        <span class="is-disabled">Suivant</span>
                    @endif
                </div>
            @endif
        </footer>
    </section>
</section>

@endsection

@section('page_js')
    @vite('resources/js/pages/users.js')
@endsection
