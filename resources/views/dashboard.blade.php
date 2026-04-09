@php
    $statusLabels = collect($byStatus)->pluck('label');
    $statusData = collect($byStatus)->pluck('total');
    $statusColors = collect($byStatus)->pluck('color');

    $priorityLabels = collect($byPriorite)->pluck('label');
    $priorityData = collect($byPriorite)->pluck('total');
    $priorityColors = collect($byPriorite)->pluck('color');

    $timeseriesLabels = collect($timeseries)->pluck('d');
    $timeseriesData = collect($timeseries)->pluck('total');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-0">Dashboard incidents</h1>
                <p class="text-muted mb-0">Vue globale des incidents et indicateurs operationnels</p>
            </div>
            <span id="realtime-status" class="badge text-bg-secondary">Temps reel: inactif</span>
        </div>
    </x-slot>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('dashboard') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Date debut</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-12 col-md-6 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">Appliquer</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Reinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Incidents total</p>
                    <div class="metric-value text-primary">{{ number_format($kpis['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Incidents ouverts</p>
                    <div class="metric-value text-warning">{{ number_format($kpis['openCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Incidents clotures</p>
                    <div class="metric-value text-success">{{ number_format($kpis['closedCount']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Duree moyenne (min)</p>
                    <div class="metric-value text-info">{{ number_format($kpis['avgDuration'] ?? 0, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card chart-card h-100">
                <div class="card-header bg-white fw-semibold">Incidents sur la periode</div>
                <div class="card-body">
                    <canvas id="timeseriesChart" height="130"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card chart-card h-100">
                <div class="card-header bg-white fw-semibold">Repartition par statut</div>
                <div class="card-body">
                    <canvas id="statusChart" height="130"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Repartition par priorite</div>
                <div class="card-body">
                    <canvas id="priorityChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Top 5 departements impactes</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Departement</th>
                                    <th class="text-end">Incidents</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDepart as $entry)
                                    <tr>
                                        <td>{{ $entry['label'] }}</td>
                                        <td class="text-end fw-semibold">{{ $entry['total'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">Aucune donnee disponible</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Top causes</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($byCause as $entry)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $entry['label'] }}</span>
                                <span class="badge badge-soft rounded-pill">{{ $entry['total'] }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucune cause enregistree</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Repartition par type d'incident</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($byType as $entry)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $entry['label'] }}</span>
                                <span class="badge text-bg-secondary rounded-pill">{{ $entry['total'] }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun type disponible</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white fw-semibold">Exports rapides</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <form class="row g-2" action="{{ route('reports.daily') }}" method="GET">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Rapport journalier</label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Format</label>
                            <select name="format" class="form-select">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 d-grid">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button class="btn btn-outline-primary" type="submit">Telecharger</button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-lg-6">
                    <form class="row g-2" action="{{ route('reports.monthly') }}" method="GET">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Rapport mensuel</label>
                            <input type="month" name="month" class="form-control" value="{{ now()->format('Y-m') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Format</label>
                            <select name="format" class="form-select">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 d-grid">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button class="btn btn-outline-primary" type="submit">Telecharger</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const statusEl = document.getElementById('realtime-status');
        const setRealtimeBadge = (connected) => {
            if (!statusEl) {
                return;
            }
            statusEl.className = connected ? 'badge text-bg-success' : 'badge text-bg-secondary';
            statusEl.textContent = connected ? 'Temps reel: connecte' : 'Temps reel: inactif';
        };

        if (window.Echo && window.EchoReady) {
            let reloadTimer = null;
            window.Echo.channel('incidents').listen('.incident.changed', () => {
                if (!reloadTimer) {
                    reloadTimer = setTimeout(() => window.location.reload(), 900);
                }
            });

            const connection = window.Echo.connector?.pusher?.connection;
            if (connection) {
                connection.bind('connected', () => setRealtimeBadge(true));
                connection.bind('disconnected', () => setRealtimeBadge(false));
                connection.bind('failed', () => setRealtimeBadge(false));
                setRealtimeBadge(connection.state === 'connected');
            } else {
                setRealtimeBadge(false);
            }
        } else {
            setRealtimeBadge(false);
        }

        const createChart = (elementId, config) => {
            const element = document.getElementById(elementId);
            if (element) {
                new Chart(element, config);
            }
        };

        createChart('timeseriesChart', {
            type: 'line',
            data: {
                labels: @json($timeseriesLabels),
                datasets: [{
                    label: 'Incidents',
                    data: @json($timeseriesData),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.15)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        createChart('statusChart', {
            type: 'bar',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    data: @json($statusData),
                    backgroundColor: @json($statusColors),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        createChart('priorityChart', {
            type: 'doughnut',
            data: {
                labels: @json($priorityLabels),
                datasets: [{
                    data: @json($priorityData),
                    backgroundColor: @json($priorityColors)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</x-app-layout>
