<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\IncidentFilterRequest;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class ExportController extends ApiController
{
    public function __construct(private readonly ReportService $reportService) {}

    public function incidentsCsv(IncidentFilterRequest $request)
    {
        Gate::authorize('exportReports');

        $rows = $this->reportService->exportRows($request->validated());

        $callback = function () use ($rows): void {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'Code',
                'Titre',
                'Departement',
                'Type',
                'Cause',
                'Statut',
                'Priorite',
                'Operateur',
                'Responsable',
                'Date debut',
                'Date fin',
                'Duree minutes',
            ], ';');

            foreach ($rows as $incident) {
                fputcsv($output, [
                    $incident->code_incident,
                    $incident->titre,
                    $incident->departement?->nom,
                    $incident->typeIncident?->libelle,
                    $incident->cause?->libelle,
                    $incident->status?->libelle,
                    $incident->priorite?->libelle,
                    $incident->operateur?->name,
                    $incident->responsable?->name,
                    $incident->date_debut?->format('Y-m-d H:i:s'),
                    $incident->date_fin?->format('Y-m-d H:i:s'),
                    $incident->duree_minutes,
                ], ';');
            }

            fclose($output);
        };

        return response()->streamDownload(
            $callback,
            'incidents-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function incidentsPdf(IncidentFilterRequest $request)
    {
        Gate::authorize('exportReports');

        $rows = $this->reportService->exportRows($request->validated());

        $pdf = Pdf::loadView('exports.incidents-pdf', [
            'incidents' => $rows,
            'generatedAt' => now(),
            'filters' => $request->validated(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('incidents-'.now()->format('Y-m-d-His').'.pdf');
    }
}
