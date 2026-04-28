<?php

namespace Tests\Feature\Performance;

use App\Models\Incident;
use App\Services\DashboardService;
use App\Services\IncidentQueryService;
use App\Services\IncidentReportService;
use App\Services\ReportPageService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class PerformanceMeasurementTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_write_performance_measurements_for_core_flows(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');

        for ($i = 1; $i <= 450; $i++) {
            $isClosed = $i % 3 === 0;
            $dateDebut = Carbon::parse('2026-04-01 08:00:00')->addHours($i);
            $dateFin = $isClosed ? $dateDebut->copy()->addMinutes((($i % 5) + 1) * 20) : null;

            Incident::create([
                'code_incident' => sprintf('INC-PERF-%04d', $i),
                'titre' => 'Incident '.$i,
                'description' => 'Description '.$i,
                'departement_id' => $context['departement']->id,
                'type_incident_id' => $context['type']->id,
                'cause_id' => $context['cause']->id,
                'status_id' => $isClosed ? $context['statusFinal']->id : $context['statusOpen']->id,
                'priorite_id' => $i % 4 === 0 ? $context['priorite']->id : $context['priorite']->id,
                'localisation' => 'Zone '.$i,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'duree_minutes' => $dateFin ? $dateDebut->diffInMinutes($dateFin) : null,
                'operateur_id' => $operator->id,
                'responsable_id' => $i % 2 === 0 ? $operator->id : null,
                'superviseur_id' => $supervisor->id,
                'actions_menees' => $isClosed ? 'Action '.$i : null,
                'resolution_summary' => $isClosed ? 'Resolution '.$i : null,
            ]);
        }

        Cache::flush();

        $dashboard = app(DashboardService::class);
        $incidentQuery = app(IncidentQueryService::class);
        $incidentReport = app(IncidentReportService::class);
        $reportPage = app(ReportPageService::class);
        $reportService = app(ReportService::class);

        $measurements = [
            $this->measure('dashboard_admin_cold', fn () => $dashboard->buildForUser($admin, [])),
            $this->measure('dashboard_admin_warm', fn () => $dashboard->buildForUser($admin, [])),
            $this->measure(
                'incident_list_operator',
                fn () => $incidentQuery->listIncidents($incidentQuery->defaultIncidentFilters([]), $operator, 15)
            ),
            $this->measure(
                'open_incidents_operator',
                fn () => $incidentQuery->listOpenIncidents($incidentQuery->defaultOpenIncidentFilters([]), $operator, 20)
            ),
            $this->measure('report_monthly_pdf_data', fn () => $incidentReport->monthlyData(Carbon::create(2026, 4, 1), [])),
            $this->measure('report_page_monthly_index', fn () => $reportPage->buildMonthlyIndexData(Carbon::create(2026, 4, 1), [
                'period' => '2026-04',
                'departement_id' => null,
                'cause_id' => null,
            ])),
            $this->measure('report_export_rows', fn () => $reportService->exportRows([], $operator)),
            $this->measure('report_overview_api', fn () => $reportService->overview([])),
            $this->measure('console_api_incidents_page', function () use ($operator) {
                return $this->actingAs($operator)->getJson('/api/v1/incidents?sort_by=date_debut&sort_dir=desc&per_page=15');
            }),
            $this->measure('console_web_open_incidents_json', function () use ($operator) {
                return $this->actingAs($operator)->getJson('/incidents/en-cours');
            }),
        ];

        file_put_contents(
            storage_path('app/perf-measurements.json'),
            json_encode($measurements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->assertFileExists(storage_path('app/perf-measurements.json'));
    }

    private function measure(string $label, callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $start = microtime(true);
        $result = $callback();
        $durationMs = round((microtime(true) - $start) * 1000, 2);
        $queries = DB::getQueryLog();

        return [
            'label' => $label,
            'duration_ms' => $durationMs,
            'query_count' => count($queries),
            'sample_sql' => collect($queries)
                ->pluck('query')
                ->map(fn (string $sql) => preg_replace('/\s+/', ' ', $sql))
                ->take(4)
                ->values()
                ->all(),
            'result_meta' => $this->extractMeta($result),
        ];
    }

    private function extractMeta(mixed $result): array
    {
        if ($result instanceof \Illuminate\Support\Collection) {
            return ['loaded_rows' => $result->count()];
        }

        if ($result instanceof TestResponse) {
            $json = $result->json();

            return [
                'status' => $result->getStatusCode(),
                'content_bytes' => strlen($result->getContent()),
                'data_count' => is_array($json['data'] ?? null) ? count($json['data']) : null,
                'total_count' => $json['meta']['total'] ?? null,
                'incidents_count' => is_array($json['incidents'] ?? null) ? count($json['incidents']) : null,
            ];
        }

        if (! is_array($result)) {
            return [];
        }

        $meta = [];

        if (isset($result['incidents'])) {
            $incidents = $result['incidents'];

            if ($incidents instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                $meta['page_count'] = $incidents->count();
                $meta['total_count'] = $incidents->total();
            } elseif ($incidents instanceof \Illuminate\Support\Collection) {
                $meta['loaded_incidents'] = $incidents->count();
            }
        }

        if (isset($result['total'])) {
            $meta['report_total'] = $result['total'];
        }

        if (isset($result['recentIncidents']) && is_countable($result['recentIncidents'])) {
            $meta['recent_count'] = count($result['recentIncidents']);
        }

        return $meta;
    }
}
