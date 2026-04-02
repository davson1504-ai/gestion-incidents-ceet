@php
    $statusLabels   = $byStatus->pluck('label');
    $statusData     = $byStatus->pluck('total');
    $statusColors   = $byStatus->pluck('color');

    $prioLabels     = $byPriorite->pluck('label');
    $prioData       = $byPriorite->pluck('total');
    $prioColors     = $byPriorite->pluck('color');

    $typeLabels     = $byType->pluck('label');
    $typeData       = $byType->pluck('total');

    $topDepLabels   = $topDepart->pluck('label');
    $topDepData     = $topDepart->pluck('total');

    $tsLabels       = $timeseries->pluck('d');
    $tsData         = $timeseries->pluck('total');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">Tableau de bord incidents</h1>
                <small class="text-muted">Vue synthèse (30 derniers jours par défaut)</small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <form class="d-flex gap-2" method="GET" action="{{ route('dashboard') }}">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                    <button class="btn btn-sm btn-outline-primary">Filtrer</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('reports.daily') }}">
                        <input type="hidden" name="date" value="{{ $filters['date_from'] ?? now()->toDateString() }}">
                        <input type="hidden" name="format" value="pdf">
                        <button class="btn btn-sm btn-primary">PDF jour</button>
                    </form>
                    <form method="GET" action="{{ route('reports.daily') }}">
                        <input type="hidden" name="date" value="{{ $filters['date_from'] ?? now()->toDateString() }}">
                        <input type="hidden" name="format" value="excel">
                        <button class="btn btn-sm btn-outline-primary">Excel jour</button>
                    </form>
                    <form method="GET" action="{{ route('reports.monthly') }}">
                        <input type="hidden" name="month" value="{{ now()->format('Y-m') }}">
                        <input type="hidden" name="format" value="pdf">
                        <button class="btn btn-sm btn-primary">PDF mois</button>
                    </form>
                    <form method="GET" action="{{ route('reports.monthly') }}">
                        <input type="hidden" name="month" value="{{ now()->format('Y-m') }}">
                        <input type="hidden" name="format" value="excel">
                        <button class="btn btn-sm btn-outline-primary">Excel mois</button>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total incidents</div>
                    <div class="h4 mb-0">{{ $kpis['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Ouverts</div>
                    <div class="h4 mb-0">{{ $kpis['openCount'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Clôturés</div>
                    <div class="h4 mb-0">{{ $kpis['closedCount'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Durée moyenne (min)</div>
                    <div class="h4 mb-0">{{ number_format($kpis['avgDuration'] ?? 0, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Répartition par statut</div>
                <div class="card-body">
                    <canvas id="chartStatus" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Répartition par priorité</div>
                <div class="card-body">
                    <canvas id="chartPriorite" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Top 5 départs (volume)</div>
                <div class="card-body">
                    <canvas id="chartDepart" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Répartition par type</div>
                <div class="card-body">
                    <canvas id="chartType" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">Tendance 30 jours (incidents créés)</div>
        <div class="card-body">
            <canvas id="chartTs" height="120"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('chartStatus'), {
            type: 'bar',
            data: {
                labels: {!! $statusLabels->toJson() !!},
                datasets: [{
                    data: {!! $statusData->toJson() !!},
                    backgroundColor: {!! $statusColors->toJson() !!},
                }]
            },
            options: {plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, ticks:{precision:0}}}}
        });

        new Chart(document.getElementById('chartPriorite'), {
            type: 'doughnut',
            data: {
                labels: {!! $prioLabels->toJson() !!},
                datasets: [{data: {!! $prioData->toJson() !!}, backgroundColor: {!! $prioColors->toJson() !!} }]
            },
            options: {plugins:{legend:{position:'bottom'}}}
        });

        new Chart(document.getElementById('chartDepart'), {
            type: 'horizontalBar' in Chart.defaults ? 'horizontalBar' : 'bar',
            data: {
                labels: {!! $topDepLabels->toJson() !!},
                datasets: [{data: {!! $topDepData->toJson() !!}, backgroundColor: '#3b82f6'}]
            },
            options: {indexAxis: 'y', plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true, ticks:{precision:0}}}}
        });

        new Chart(document.getElementById('chartType'), {
            type: 'pie',
            data: {
                labels: {!! $typeLabels->toJson() !!},
                datasets: [{data: {!! $typeData->toJson() !!}, backgroundColor: ['#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#6366f1','#6b7280','#111827']}]
            },
            options: {plugins:{legend:{position:'bottom'}}}
        });

        new Chart(document.getElementById('chartTs'), {
            type: 'line',
            data: {
                labels: {!! $tsLabels->toJson() !!},
                datasets: [{label:'Incidents', data: {!! $tsData->toJson() !!}, borderColor:'#0ea5e9', fill:false, tension:0.2}]
            },
            options: {plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, ticks:{precision:0}}}}
        });
    </script>
</x-app-layout>
