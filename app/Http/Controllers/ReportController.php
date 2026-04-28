<?php

namespace App\Http\Controllers;

use App\Exports\IncidentReportExport;
use App\Http\Requests\DailyReportRequest;
use App\Http\Requests\MonthlyReportRequest;
use App\Services\IncidentReportService;
use App\Services\ReportPageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        private readonly IncidentReportService $incidentReportService,
        private readonly ReportPageService $reportPageService,
    ) {
        $this->middleware(['auth', 'verified', 'permission:reporting.view']);
    }

    public function index(Request $request): View
    {
        $period = $this->parseMonth((string) $request->input('period', now()->format('Y-m')));

        $filters = [
            'period' => $period->format('Y-m'),
            'departement_id' => $request->input('departement_id'),
            'cause_id' => $request->input('cause_id'),
        ];

        return view('reports.index', $this->reportPageService->buildMonthlyIndexData($period, $filters));
    }

    public function exportDailyReport(DailyReportRequest $request)
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));
        $filters = $request->only(['departement_id', 'cause_id']);
        $data = $this->incidentReportService->dailyData($date, $filters);

        return $this->export($request, $data, "rapport-journalier-{$date->format('Y-m-d')}");
    }

    public function exportMonthlyReport(MonthlyReportRequest $request)
    {
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')));
        $filters = $request->only(['departement_id', 'cause_id']);
        $data = $this->incidentReportService->monthlyData($month, $filters);

        return $this->export($request, $data, "rapport-mensuel-{$month->format('Y-m')}");
    }

    private function export(Request $request, array $data, string $baseName)
    {
        $format = $request->input('format', 'pdf');

        if ($format === 'excel') {
            return Excel::download(new IncidentReportExport($data), "{$baseName}.xlsx");
        }

        $pdf = Pdf::loadView('reports.incidents', $data)->setPaper('a4', 'portrait');

        return $pdf->download("{$baseName}.pdf");
    }

    private function parseMonth(string $value): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }
}
