<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">📋 Historique des actions</h1>
                <small class="text-muted">Traçabilité complète de toutes les opérations</small>
            </div>
            <div class="d-flex gap-2">
                {{-- Export CSV --}}
                <a href="{{ route('historique.export', array_merge($filters, ['format' => 'excel'])) }}"
                   class="btn btn-sm btn-outline-success">
                    📊 Export CSV
                </a>
                {{-- Export PDF --}}
                <a href="{{ route('historique.export', array_merge($filters, ['format' => 'pdf'])) }}"
                   class="btn btn-sm btn-outline-danger">
                    📄 Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ── Filtres ──────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('historique.index') }}">

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Utilisateur</label>
                    <select name="user_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($filters['user_id'] == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Type d'action</label>
                    <select name="action_type" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($actionTypes as $type)
                            <option value="{{ $type }}" @selected($filters['action_type'] === $type)>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Du</label>
                    <input type="date" name="date_from" class="form-control"
                           value="{{ $filters['date_from'] }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Au</label>
                    <input type="date" name="date_to" class="form-control"
                           value="{{ $filters['date_to'] }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Recherche</label>
                    <input type="text" name="q" class="form-control"
                           placeholder="Code, titre, description…"
                           value="{{ $filters['q'] }}">
                </div>

                <div class="col-md-1 d-flex align-items-end gap-1">
                    <button class="btn btn-primary w-100">🔍</button>
                    <a href="{{ route('historique.index') }}" class="btn btn-outline-secondary w-100">✕</a>
                </div>

            </form>
        </div>
    </div>

    {{-- ── Compteur --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small">
            {{ $actions->total() }} action(s) trouvée(s)
        </span>
        <span class="text-muted small">
            Page {{ $actions->currentPage() }} / {{ $actions->lastPage() }}
        </span>
    </div>

    {{-- ── Tableau ──────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                <thead class="table-dark">
                    <tr>
                        <th style="width:150px;">Date / Heure</th>
                        <th style="width:160px;">Utilisateur</th>
                        <th style="width:110px;">Action</th>
                        <th style="width:160px;">Incident</th>
                        <th>Description</th>
                        <th style="width:80px;">Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actions as $action)
                    <tr>
                        {{-- Date --}}
                        <td class="text-nowrap">
                            <span class="fw-semibold">
                                {{ optional($action->action_date)?->format('d/m/Y') }}
                            </span><br>
                            <small class="text-muted">
                                {{ optional($action->action_date)?->format('H:i:s') }}
                            </small>
                        </td>

                        {{-- Utilisateur --}}
                        <td>
                            <span class="fw-semibold">{{ optional($action->user)?->name ?? '—' }}</span>
                        </td>

                        {{-- Badge action --}}
                        <td>
                            @php
                                $badgeClass = match($action->action_type) {
                                    'create' => 'bg-success',
                                    'update' => 'bg-primary',
                                    'delete' => 'bg-danger',
                                    default  => 'bg-secondary',
                                };
                                $icon = match($action->action_type) {
                                    'create' => '➕',
                                    'update' => '✏️',
                                    'delete' => '🗑️',
                                    default  => '•',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ $icon }} {{ ucfirst($action->action_type) }}
                            </span>
                        </td>

                        {{-- Incident --}}
                        <td>
                            @if($action->incident)
                                <a href="{{ route('incidents.show', $action->incident) }}"
                                   class="text-decoration-none fw-semibold">
                                    {{ $action->incident->code_incident }}
                                </a><br>
                                <small class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($action->incident->titre, 30) }}
                                </small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Description --}}
                        <td>{{ $action->description }}</td>

                        {{-- Bouton détails --}}
                        <td>
                            @if($action->old_values || $action->new_values)
                                <button class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-{{ $action->id }}">
                                    Diff
                                </button>

                                {{-- Modal diff --}}
                                <div class="modal fade" id="modal-{{ $action->id }}"
                                     tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title">
                                                    Détails — {{ $action->incident?->code_incident }}
                                                    ({{ ucfirst($action->action_type) }})
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    @if($action->old_values)
                                                    <div class="col-md-6">
                                                        <h6 class="text-danger">⬅️ Avant</h6>
                                                        <pre class="bg-light p-2 rounded small" style="max-height:300px;overflow:auto;">{{ json_encode($action->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                    @endif
                                                    @if($action->new_values)
                                                    <div class="col-md-6">
                                                        <h6 class="text-success">➡️ Après</h6>
                                                        <pre class="bg-light p-2 rounded small" style="max-height:300px;overflow:auto;">{{ json_encode($action->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Fermer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Aucune action trouvée pour ces critères.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white">
            {{ $actions->links('pagination::bootstrap-5') }}
        </div>
    </div>

</x-app-layout>