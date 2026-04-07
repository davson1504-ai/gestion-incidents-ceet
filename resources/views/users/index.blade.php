<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">Equipes terrain</h1>
                <small class="text-muted">Gestion des operateurs, superviseurs et administrateurs</small>
            </div>
            @can('users.manage')
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Nouvel utilisateur</a>
            @endcan
        </div>
    </x-slot>

    <div class="card shadow-sm mb-3 filter-card">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('users.index') }}">
                <div class="col-12 col-md-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Nom, email, telephone"
                           value="{{ $filters['q'] }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Departement</label>
                    <select name="departement_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($departements as $dep)
                            <option value="{{ $dep->id }}" @selected((string) $filters['departement_id'] === (string) $dep->id)>
                                {{ $dep->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="1" @selected((string) $filters['is_active'] === '1')>Actif</option>
                        <option value="0" @selected((string) $filters['is_active'] === '0')>Inactif</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary btn-sm flex-fill">Filtrer</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-mobile-cards">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th class="d-none d-md-table-cell">Telephone</th>
                            <th class="d-none d-md-table-cell">Departement</th>
                            <th>Role</th>
                            <th>Etat</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td data-label="Nom">{{ $user->name }}</td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Telephone" class="d-none d-md-table-cell">{{ $user->telephone ?? '—' }}</td>
                                <td data-label="Departement" class="d-none d-md-table-cell">{{ $user->departement?->nom ?? '—' }}</td>
                                <td data-label="Role">
                                    {{ $user->getRoleNames()->implode(', ') ?: '—' }}
                                </td>
                                <td data-label="Etat">
                                    @if($user->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td data-label="Actions" class="text-end">
                                    @can('users.manage')
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">Editer</a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">Supprimer</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</x-app-layout>

