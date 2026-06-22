<?php

namespace App\Http\Controllers;

use App\Models\IncidentAction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HistoriqueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('role:Administrateur|Superviseur');
    }

    public function index(Request $request): View
    {
        $filters = array_merge([
            'user_id' => null,
            'module' => null,
            'action_type' => null,
            'date_from' => null,
            'date_to' => null,
            'q' => null,
        ], $request->only(['user_id', 'module', 'action_type', 'date_from', 'date_to', 'q']));

        $logs = $this->filteredActions($filters)
            ->with(['user.roles', 'incident'])
            ->orderByDesc('action_date')
            ->orderByDesc('id')
            ->paginate(7)
            ->withQueryString();

        $users = User::query()
            ->orderBy('name')
            ->get();

        // La vue historique conserve le nom "module", mais les données métier
        // viennent maintenant de incident_actions.action_type.
        $modules = IncidentAction::query()
            ->whereNotNull('action_type')
            ->distinct()
            ->orderBy('action_type')
            ->pluck('action_type');

        $totalLogs = IncidentAction::query()->count();

        $lastLog = IncidentAction::query()
            ->orderByDesc('action_date')
            ->first();

        $lastLogMinutes = $lastLog?->action_date
            ? $lastLog->action_date->diffInMinutes(now())
            : null;

        $journalAvailability = $totalLogs > 0
            ? ($lastLogMinutes !== null && $lastLogMinutes <= 1440 ? 99.8 : 97.5)
            : 0;

        $recentActivity = $this->recentActivity();

        return view('historique.index', [
            'logs' => $logs,
            'filters' => $filters,
            'users' => $users,
            'modules' => $modules,
            'totalLogs' => $totalLogs,
            'journalAvailability' => $journalAvailability,
            'lastLog' => $lastLog,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function export(Request $request)
    {
        $filters = array_merge([
            'user_id' => null,
            'module' => null,
            'action_type' => null,
            'date_from' => null,
            'date_to' => null,
            'q' => null,
        ], $request->only(['user_id', 'module', 'action_type', 'date_from', 'date_to', 'q']));

        $format = $request->input('format', 'csv');

        $query = $this->filteredActions($filters)
            ->with(['user', 'incident'])
            ->orderByDesc('action_date')
            ->orderByDesc('id');

        $fileName = 'historique-systeme-' . now()->format('Y-m-d');

        if (in_array($format, ['csv', 'excel'], true)) {
            $callback = function () use ($query): void {
                $out = fopen('php://output', 'w');

                fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($out, [
                    'Date et heure',
                    'Utilisateur',
                    'Action',
                    'Module',
                    'Cible',
                    'Adresse IP',
                    'Détails',
                ], ';');

                $query->lazy(200)->each(function (IncidentAction $action) use ($out): void {
                    fputcsv($out, [
                        optional($action->action_date)->format('d/m/Y H:i:s'),
                        optional($action->user)->name ?? 'Système',
                        $action->action_type ?? '-',
                        'incidents',
                        $this->targetLabel($action),
                        '-',
                        $action->description ?: '-',
                    ], ';');
                });

                fclose($out);
            };

            return response()->streamDownload($callback, "{$fileName}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $actions = $query->get();

        $pdf = Pdf::loadView('historique.export-pdf', [
            'actions' => $actions,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("{$fileName}.pdf");
    }

    private function filteredActions(array $filters): Builder
    {
        $actionType = $filters['action_type'] ?? null;

        // Compatibilité avec l'ancien filtre "module" de la vue : il filtre maintenant action_type.
        if (! $actionType && ! empty($filters['module'])) {
            $actionType = $filters['module'];
        }

        return IncidentAction::query()
            ->when($filters['user_id'] ?? null, fn (Builder $query, $value) => $query->where('user_id', $value))
            ->when($actionType, fn (Builder $query, $value) => $query->where('action_type', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $value) => $query->whereDate('action_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $value) => $query->whereDate('action_date', '<=', $value))
            ->when($filters['q'] ?? null, function (Builder $query, string $value): void {
                $query->where(function (Builder $subQuery) use ($value): void {
                    $subQuery
                        ->where('action_type', 'like', "%{$value}%")
                        ->orWhere('description', 'like', "%{$value}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($value): void {
                            $userQuery
                                ->where('name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        })
                        ->orWhereHas('incident', function (Builder $incidentQuery) use ($value): void {
                            $incidentQuery
                                ->where('code_incident', 'like', "%{$value}%")
                                ->orWhere('titre', 'like', "%{$value}%");
                        });
                });
            });
    }

    private function recentActivity(): array
    {
        $items = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $count = IncidentAction::query()
                ->whereDate('action_date', $date->toDateString())
                ->count();

            $items->push([
                'label' => $this->dayLabel($date),
                'count' => $count,
            ]);
        }

        $max = max(1, (int) $items->max('count'));

        return $items
            ->map(function (array $item) use ($max): array {
                $item['height'] = $item['count'] > 0
                    ? max(12, (int) round(($item['count'] / $max) * 100))
                    : 8;

                return $item;
            })
            ->values()
            ->all();
    }

    private function dayLabel(Carbon $date): string
    {
        return match ((int) $date->isoWeekday()) {
            1 => 'LU',
            2 => 'MA',
            3 => 'ME',
            4 => 'JE',
            5 => 'VE',
            6 => 'SA',
            7 => 'DI',
        };
    }

    private function targetLabel(IncidentAction $action): string
    {
        if ($action->incident) {
            return '#' . ($action->incident->code_incident ?: 'INC-' . str_pad((string) $action->incident->id, 5, '0', STR_PAD_LEFT));
        }

        return '--';
    }
    public function clear(Request $request)
    {
        $deleted = IncidentAction::query()->delete();

        return redirect()
            ->route('historique.index')
            ->with('success', $deleted > 0
                ? "{$deleted} entrée(s) d’historique ont été vidées logiquement."
                : "Aucune entrée d’historique à vider.");
    }

}
