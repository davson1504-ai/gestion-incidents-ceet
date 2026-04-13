@php
    use Illuminate\Support\Str;

    $from = $actions->firstItem() ?? 0;
    $to = $actions->lastItem() ?? 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex w-100 flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
            <div>
                <h1 class="h3 mb-1">Journal d'audit</h1>
                <p class="text-muted mb-0">Tracez chaque action effectuée sur le réseau électrique et le système.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('historique.export', array_merge($filters, ['format' => 'excel'])) }}" class="btn btn-outline-success">
                    Exporter (Excel)
                </a>
                <a href="{{ route('historique.export', array_merge($filters, ['format' => 'pdf'])) }}" class="btn btn-outline-danger">
                    Rapport PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('historique.index') }}">
                <div class="col-12 col-xl-4">
                    <label class="form-label fw-semibold">Recherche</label>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="Rechercher un log, incident, agent"
                        value="{{ $filters['q'] }}"
                    >
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label fw-semibold">Utilisateur</label>
                    <select name="user_id" class="form-select js-tom-select" data-placeholder="Filtrer par utilisateur">
                        <option value="">Tous les utilisateurs</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label fw-semibold">Type d'action</label>
                    <select name="action_type" class="form-select js-tom-select" data-placeholder="Toutes les actions">
                        <option value="">Toutes les actions</option>
                        @foreach($actionTypes as $type)
                            <option value="{{ $type }}" @selected((string) $filters['action_type'] === (string) $type)>{{ strtoupper($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label fw-semibold">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label fw-semibold">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button class="btn btn-danger" type="submit">Appliquer les filtres</button>
                    <a href="{{ route('historique.index') }}" class="btn btn-outline-secondary">Effacer</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-1">Historique des activités récentes</h2>
                <p class="text-muted mb-0">Analyse détaillée des actions enregistrées dans l'application.</p>
            </div>
            <span class="badge rounded-pill text-bg-light">{{ number_format($actions->total()) }} événements</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date & heure</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Cible</th>
                            <th>Description des modifications</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actions as $action)
                            @php
                                $type = strtolower((string) $action->action_type);
                                $actionLabel = match ($type) {
                                    'create' => 'CREATION',
                                    'update' => 'MODIFICATION',
                                    'delete' => 'SUPPRESSION',
                                    'cloture' => 'CLOTURE',
                                    'connexion' => 'CONNEXION',
                                    default => strtoupper($action->action_type),
                                };

                                $actionClass = match ($type) {
                                    'update' => 'ceet-action-pill-modification',
                                    'create' => 'ceet-action-pill-creation',
                                    'cloture' => 'ceet-action-pill-cloture',
                                    'connexion' => 'ceet-action-pill-connexion',
                                    default => 'ceet-action-pill-systeme',
                                };

                                $targetTitle = 'Systeme';
                                $targetRef = '-';

                                if ($action->incident) {
                                    $targetTitle = 'Incident';
                                    $targetRef = $action->incident->code_incident;
                                } else {
                                    $descriptionLower = Str::lower((string) $action->description);
                                    if (Str::contains($descriptionLower, 'utilisateur')) {
                                        $targetTitle = 'Utilisateur';
                                    } elseif (Str::contains($descriptionLower, 'catalogue')) {
                                        $targetTitle = 'Catalogue';
                                    } elseif (Str::contains($descriptionLower, 'sauvegarde')) {
                                        $targetTitle = 'Sauvegarde';
                                    }

                                    if (preg_match('/([A-Z]{2,5}-[0-9]+)/', (string) $action->description, $matches) === 1) {
                                        $targetRef = $matches[1];
                                    }
                                }

                                $userName = $action->user?->name ?? 'System';
                                $userRole = $action->user?->roles?->first()?->name ?? 'Service';
                            @endphp
                            <tr>
                                <td class="text-nowrap">
                                    {{ $action->action_date?->format('Y-m-d H:i:s') ?? '-' }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ceet-avatar-circle ceet-avatar-circle-sm bg-primary-subtle text-primary-emphasis">
                                            {{ strtoupper(Str::substr($userName, 0, 2)) }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold">{{ $userName }}</div>
                                            <div class="small text-muted text-uppercase">{{ $userRole }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ceet-action-pill {{ $actionClass }}">{{ $actionLabel }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-danger">{{ $targetTitle }}</div>
                                    <div class="small text-muted">{{ $targetRef }}</div>
                                </td>
                                <td>{{ $action->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Aucune action trouvée pour ces critères.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="small text-muted">
                Affichage de {{ $from }} à {{ $to }} sur {{ number_format($actions->total()) }} événements enregistrés
            </span>
            <div>{{ $actions->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</x-app-layout>
