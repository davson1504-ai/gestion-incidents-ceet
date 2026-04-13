@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $from = $users->firstItem() ?? 0;
    $to = $users->lastItem() ?? 0;
    $advancedOpen = filled($filters['role']) || filled($filters['departement_id']) || ($filters['is_active'] !== null && $filters['is_active'] !== '');
    $createModalOpen = collect(['name', 'prenom', 'nom_famille', 'email', 'telephone', 'role', 'departement_id', 'password'])
        ->contains(fn ($field) => $errors->has($field));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex w-100 flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
            <div>
                <h1 class="h3 mb-1">Utilisateurs & rôles</h1>
                <p class="text-muted mb-0">Gérez les accès, assignez des rôles et contrôlez les permissions du personnel.</p>
            </div>
            @can('users.manage')
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    Nouvel utilisateur
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Total utilisateurs</span>
                    <div class="metric-value mt-2">{{ number_format($stats['totalUsers'] ?? 0) }}</div>
                    <div class="small text-muted mt-2">+{{ number_format($stats['newThisWeek'] ?? 0) }} nouveaux cette semaine</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Opérateurs actifs</span>
                    <div class="metric-value mt-2">{{ number_format($stats['activeOperators'] ?? 0) }}</div>
                    <div class="small text-muted mt-2">Personnel actuellement en service</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-xl-8">
                        <label class="form-label fw-semibold">Recherche</label>
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            value="{{ $filters['q'] }}"
                            placeholder="Rechercher un utilisateur par nom, email ou téléphone..."
                        >
                    </div>
                    <div class="col-12 col-xl-4 d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#usersAdvancedFilters" aria-expanded="{{ $advancedOpen ? 'true' : 'false' }}" aria-controls="usersAdvancedFilters">
                            Filtres avancés
                        </button>
                        <button type="submit" class="btn btn-danger">Appliquer</button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Effacer</a>
                    </div>
                </div>

                <div class="collapse {{ $advancedOpen ? 'show' : '' }}" id="usersAdvancedFilters">
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Rôle</label>
                            <select name="role" class="form-select">
                                <option value="">Tous les rôles</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected((string) $filters['role'] === (string) $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Département</label>
                            <select name="departement_id" class="form-select">
                                <option value="">Tous les départements</option>
                                @foreach ($departements as $dep)
                                    <option value="{{ $dep->id }}" @selected((string) $filters['departement_id'] === (string) $dep->id)>{{ $dep->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Statut</label>
                            <select name="is_active" class="form-select">
                                <option value="">Tous</option>
                                <option value="1" @selected((string) $filters['is_active'] === '1')>Actifs</option>
                                <option value="0" @selected((string) $filters['is_active'] === '0')>Inactifs</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Dernière activité</th>
                            <th>Département</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $fullName = $user->name ?: 'Utilisateur';
                                $avatar = strtoupper(Str::substr($fullName, 0, 2));
                                $userRole = $user->roles->first()?->name ?? 'Sans rôle';
                                $statusClass = $user->is_active
                                    ? ($user->email_verified_at ? 'text-bg-success' : 'text-bg-primary')
                                    : 'text-bg-secondary';
                                $statusLabel = $user->is_active
                                    ? ($user->email_verified_at ? 'Actif' : 'Invitation envoyée')
                                    : 'Hors ligne';
                                $lastSeen = $user->updated_at ? Carbon::parse($user->updated_at)->diffForHumans() : '-';
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ceet-avatar-circle ceet-avatar-circle-sm bg-primary-subtle text-primary-emphasis">{{ $avatar }}</span>
                                        <div>
                                            <div class="fw-semibold">{{ $fullName }}</div>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $userRole }}</td>
                                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td>{{ $lastSeen }}</td>
                                <td>{{ $user->departement?->nom ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @can('users.manage')
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-danger">Éditer</a>
                                            @if ((int) $user->id !== (int) auth()->id())
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Supprimer ou désactiver cet utilisateur ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Supprimer</button>
                                                </form>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Lecture seule</button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Aucun utilisateur trouvé pour ces critères.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="small text-muted">Affichage de {{ $from }} à {{ $to }} sur {{ $users->total() }} utilisateurs</span>
            <div>{{ $users->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>

    @can('users.manage')
        <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold" id="createUserModalLabel">Ajouter un nouvel utilisateur</h5>
                            <p class="text-muted mb-0 small">Créez un nouveau profil pour un membre de l'équipe CEET.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('users.store') }}" class="row g-3">
                            @csrf
                            <input type="hidden" name="is_active" value="1">
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input id="prenom" type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}" required>
                                @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nom_famille" class="form-label">Nom de famille</label>
                                <input id="nom_famille" type="text" name="nom_famille" class="form-control @error('nom_famille') is-invalid @enderror" value="{{ old('nom_famille') }}" required>
                                @error('nom_famille')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email professionnel</label>
                                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Rôle assigné</label>
                                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">Sélectionner un rôle</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="departement_id" class="form-label">Département / Équipe</label>
                                <select id="departement_id" name="departement_id" class="form-select @error('departement_id') is-invalid @enderror">
                                    <option value="">Aucun département</option>
                                    @foreach ($departements as $dep)
                                        <option value="{{ $dep->id }}" @selected((string) old('departement_id') === (string) $dep->id)>{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                                @error('departement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @error('name')
                                <div class="col-12">
                                    <div class="alert alert-danger py-2 mb-0">{{ $message }}</div>
                                </div>
                            @enderror
                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-danger">Créer le compte</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @can('users.manage')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const shouldOpen = @json($createModalOpen);
                const modalElement = document.getElementById('createUserModal');

                if (shouldOpen && modalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        </script>
    @endcan
</x-app-layout>
