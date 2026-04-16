@php
    use Illuminate\Support\Carbon;

    $formatDuration = static function (?int $minutes): string {
        if ($minutes === null) {
            return '-';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remainingMinutes = $minutes % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'j';
        }
        if ($hours > 0 || $days > 0) {
            $parts[] = $hours . 'h';
        }
        $parts[] = $remainingMinutes . 'min';

        return implode(' ', $parts);
    };

    $plusAncienLabel = $plusAncien ? $formatDuration($plusAncien->duree_en_attente) : '-';
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

        .row.g-3.mb-4:first-of-type .col-12:nth-child(3) .card {
            animation-delay: 0.3s;
            border-left: 4px solid var(--ceet-success);
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

        .btn-outline-danger {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover {
            transform: translateY(-2px);
        }

        /* ========================================
           METRIC VALUE ANIMATION
           ======================================== */
        .metric-value {
            animation: pulse-light 3s ease-in-out infinite;
        }
    </style>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Incidents en cours</h1>
            <p class="text-muted mb-0">Tableau de bord opérationnel des incidents non résolus, triés par criticité puis ancienneté.</p>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Total en cours</span>
                    <div class="metric-value mt-2" id="en-cours-total">{{ number_format($totalEnCours) }}</div>
                    <div class="small text-muted mt-2">Compteur mis à jour automatiquement toutes les 60 secondes.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Incidents critiques</span>
                    <div class="metric-value mt-2" id="en-cours-critiques">{{ number_format($critiquesCount) }}</div>
                    <div class="small text-muted mt-2">Priorité de niveau 1 actuellement ouverte.</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="text-uppercase small text-muted fw-semibold">Plus ancien</span>
                    <div class="metric-value mt-2" id="en-cours-plus-ancien">{{ $plusAncienLabel }}</div>
                    <div class="small text-muted mt-2" id="en-cours-plus-ancien-code">{{ $plusAncien?->code_incident ?? 'Aucun incident ouvert' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('incidents.en-cours') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Départ</label>
                    <select name="departement_id" class="form-select js-tom-select" data-placeholder="Tous les départs">
                        <option value="">Tous les départs</option>
                        @foreach($departements as $departement)
                            <option value="{{ $departement->id }}" @selected((string) $filters['departement_id'] === (string) $departement->id)>{{ $departement->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Priorité</label>
                    <select name="priorite_id" class="form-select js-tom-select" data-placeholder="Toutes les priorités">
                        <option value="">Toutes les priorités</option>
                        @foreach($priorites as $priorite)
                            <option value="{{ $priorite->id }}" @selected((string) $filters['priorite_id'] === (string) $priorite->id)>{{ $priorite->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Recherche</label>
                    <input type="text" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Code incident ou titre">
                </div>
                <div class="col-12 col-md-6 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-danger">Appliquer les filtres</button>
                    <a href="{{ route('incidents.en-cours') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-danger">Retour à la liste complète</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Départ</th>
                            <th>Type</th>
                            <th>Priorité</th>
                            <th>Date début</th>
                            <th>Durée attente</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                            @php
                                $waitingMinutes = $incident->duree_en_attente;
                                $waitingClass = $waitingMinutes > 120
                                    ? 'text-danger'
                                    : ($waitingMinutes > 60 ? 'text-warning' : 'text-success');
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $incident->code_incident }}</td>
                                <td>{{ $incident->departement?->nom ?? '-' }}</td>
                                <td>{{ $incident->typeIncident?->libelle ?? '-' }}</td>
                                <td>
                                    <span class="badge text-bg-light">{{ $incident->priorite?->libelle ?? '-' }}</span>
                                </td>
                                <td>{{ $incident->date_debut?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="fw-semibold {{ $waitingClass }}">{{ $formatDuration($waitingMinutes) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-outline-danger btn-sm">Prendre en charge</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Aucun incident ouvert ne correspond aux filtres.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
            <span class="small text-muted">Affichage de {{ $incidents->firstItem() ?? 0 }} à {{ $incidents->lastItem() ?? 0 }} sur {{ $incidents->total() }} incidents ouverts</span>
            {{ $incidents->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        (() => {
            const endpoint = @json(request()->fullUrl());
            const totalNode = document.getElementById('en-cours-total');
            const criticalNode = document.getElementById('en-cours-critiques');
            const oldestNode = document.getElementById('en-cours-plus-ancien');
            const oldestCodeNode = document.getElementById('en-cours-plus-ancien-code');

            if (!totalNode || !criticalNode || !oldestNode || !oldestCodeNode) {
                return;
            }

            const refreshCounters = async () => {
                try {
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Impossible de rafraîchir les compteurs.');
                    }

                    const payload = await response.json();
                    totalNode.textContent = payload.totalEnCours ?? '0';
                    criticalNode.textContent = payload.critiquesCount ?? '0';
                    oldestNode.textContent = payload.plusAncien?.label ?? '-';
                    oldestCodeNode.textContent = payload.plusAncien?.code_incident ?? 'Aucun incident ouvert';
                } catch (_error) {
                }
            };

            window.setInterval(refreshCounters, 60000);
        })();
    </script>
</x-app-layout>
