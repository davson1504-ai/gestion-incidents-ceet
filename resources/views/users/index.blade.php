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
    <style>
        /* ========================================
           VARIABLES & BASE
           ======================================== */
        :root {
            --ceet-red: #ef2433;
            --ceet-red-dark: #ce1220;
            --ceet-gold: #f59e0b;
            --ceet-blue-night: #0f172a;
            --ceet-blue-deep: #1e293b;
            --ceet-gray-light: #f8fafc;
            --ceet-border-light: #e2e8f0;
            --ceet-text-muted: #64748b;
            --ceet-success: #22c55e;
        }

        /* ========================================
           ANIMATIONS
           ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-light {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.85;
            }
        }

        /* ========================================
           CARDS KPI - Modern Design
           ======================================== */
        .row.g-3.mb-4:first-of-type .card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.88));
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.07);
            animation: fadeInUp 0.6s ease both;
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(1) .card {
            animation-delay: 0.1s;
            border-left: 4px solid var(--ceet-red);
        }

        .row.g-3.mb-4:first-of-type .col-12:nth-child(2) .card {
            animation-delay: 0.2s;
            border-left: 4px solid var(--ceet-gold);
        }

        .row.g-3.mb-4:first-of-type .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.15);
            border-color: rgba(226, 232, 240, 0.8);
        }

        /* ========================================
           GENERAL CARDS
           ======================================== */
        .card {
            border-radius: 16px;
            border: 1px solid var(--ceet-border-light);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            animation: fadeInUp 0.6s ease both;
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        }

        /* ========================================
           FORMS & INPUTS
           ======================================== */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--ceet-border-light);
            transition: all 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--ceet-red);
            box-shadow: 0 0 0 3px rgba(239, 36, 51, 0.1);
        }

        /* ========================================
           TABLE ANIMATION
           ======================================== */
        table tbody tr {
            animation: fadeInUp 0.6s ease backwards;
        }

        table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        table tbody tr:nth-child(2) { animation-delay: 0.15s; }
        table tbody tr:nth-child(3) { animation-delay: 0.2s; }
        table tbody tr:nth-child(4) { animation-delay: 0.25s; }
        table tbody tr:nth-child(5) { animation-delay: 0.3s; }
        table tbody tr:nth-child(n+6) { animation-delay: 0.35s; }

        table tbody tr:hover {
            background-color: rgba(239, 36, 51, 0.03);
            transition: all 0.2s ease;
        }

        /* ========================================
           BUTTONS
           ======================================== */
        .btn-danger {
            background: linear-gradient(135deg, var(--ceet-red), var(--ceet-red-dark));
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 36, 51, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 36, 51, 0.4);
        }

        .btn-outline-secondary {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            transform: translateY(-2px);
        }

        .btn-outline-danger, .btn-sm {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover, .btn-sm:hover {
            transform: translateY(-2px);
        }
    </style>
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
