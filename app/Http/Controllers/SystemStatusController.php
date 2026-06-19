<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SystemStatusController extends Controller
{
    public function __invoke(): View
    {
        $database = $this->databaseStatus();
        $cache = $this->cacheStatus();
        $queue = $this->queueStatus();

        $incidentCounts = $this->incidentCounts();

        return view('system.status', [
            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
                'debug' => (bool) config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
            ],
            'database' => $database,
            'cache' => $cache,
            'queue' => $queue,
            'broadcasting' => [
                'driver' => config('broadcasting.default'),
                'reverb_host' => config('reverb.servers.reverb.host'),
                'reverb_port' => config('reverb.servers.reverb.port'),
            ],
            'metrics' => [
                'users' => User::query()->count(),
                'incidents_total' => $incidentCounts['total'],
                'incidents_open' => $incidentCounts['open'],
                'incidents_closed' => $incidentCounts['closed'],
            ],
        ]);
    }

    private function databaseStatus(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'ok' => true,
                'connection' => config('database.default'),
                'database' => DB::connection()->getDatabaseName(),
                'latest_migration' => Schema::hasTable('migrations')
                    ? DB::table('migrations')->orderByDesc('batch')->orderByDesc('migration')->value('migration')
                    : null,
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'connection' => config('database.default'),
                'database' => null,
                'latest_migration' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function cacheStatus(): array
    {
        try {
            $key = 'system.status.ping';
            Cache::put($key, now()->toIso8601String(), 30);

            return [
                'ok' => Cache::has($key),
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'driver' => config('cache.default'),
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function queueStatus(): array
    {
        $connection = config('queue.default');
        $pendingJobs = null;

        try {
            if ($connection === 'database' && Schema::hasTable('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
            }

            return [
                'ok' => true,
                'connection' => $connection,
                'pending_jobs' => $pendingJobs,
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'connection' => $connection,
                'pending_jobs' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function incidentCounts(): array
    {
        $summary = Incident::query()
            ->leftJoin('statuses', 'statuses.id', '=', 'incidents.status_id')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN statuses.is_final = 1 THEN 1 ELSE 0 END) as closed_count,
                SUM(CASE WHEN statuses.is_final = 0 OR statuses.is_final IS NULL THEN 1 ELSE 0 END) as open_count
            ')
            ->first();

        return [
            'total' => (int) ($summary?->total ?? 0),
            'open' => (int) ($summary?->open_count ?? 0),
            'closed' => (int) ($summary?->closed_count ?? 0),
        ];
    }
}
