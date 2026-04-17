<?php

namespace App\Http\Controllers;

use App\Models\IncidentAction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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
            'action_type' => null,
            'date_from' => null,
            'date_to' => null,
            'q' => null,
        ], $request->only(['user_id', 'action_type', 'date_from', 'date_to', 'q']));

        $actions = IncidentAction::with(['user.roles', 'incident'])
            ->when($filters['user_id'], fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['action_type'], fn ($q, $v) => $q->where('action_type', $v))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('action_date', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('action_date', '<=', $v))
            ->when($filters['q'], function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('description', 'like', "%{$v}%")
                        ->orWhereHas('incident', fn ($i) => $i->where('code_incident', 'like', "%{$v}%")
                            ->orWhere('titre', 'like', "%{$v}%")
                        )
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$v}%")
                            ->orWhere('email', 'like', "%{$v}%")
                        );
                });
            })
            ->latest('action_date')
            ->paginate(25)
            ->withQueryString();

        $users = User::orderBy('name')->get();
        $actionTypes = IncidentAction::distinct()->pluck('action_type')->sort()->values();

        return view('historique.index', compact('actions', 'filters', 'users', 'actionTypes'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['user_id', 'action_type', 'date_from', 'date_to', 'q']);
        $format = $request->input('format', 'pdf');

        $actions = IncidentAction::with(['user', 'incident'])
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['action_type'] ?? null, fn ($q, $v) => $q->where('action_type', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('action_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('action_date', '<=', $v))
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('description', 'like', "%{$v}%")
                        ->orWhereHas('incident', fn ($i) => $i->where('code_incident', 'like', "%{$v}%")
                            ->orWhere('titre', 'like', "%{$v}%")
                        );
                });
            })
            ->latest('action_date')
            ->get();

        $fileName = 'historique-'.now()->format('Y-m-d');

        if ($format === 'excel') {
            // Export CSV simple (pas besoin de maatwebsite pour l'historique)
            $callback = function () use ($actions) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
                fputcsv($out, ['Date', 'Utilisateur', 'Action', 'Incident', 'Description'], ';');
                foreach ($actions as $a) {
                    fputcsv($out, [
                        optional($a->action_date)?->format('d/m/Y H:i'),
                        optional($a->user)?->name ?? '-',
                        strtoupper($a->action_type),
                        optional($a->incident)?->code_incident ?? '-',
                        $a->description,
                    ], ';');
                }
                fclose($out);
            };

            return response()->streamDownload($callback, "{$fileName}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // PDF
        $pdf = Pdf::loadView('historique.export-pdf', compact('actions', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("{$fileName}.pdf");
    }
}
