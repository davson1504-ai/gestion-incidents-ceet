<?php

namespace App\Http\Controllers;

use App\Exports\IncidentReportExport;
use App\Http\Requests\DailyReportRequest;
use App\Http\Requests\MonthlyReportRequest;
use App\Services\IncidentReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private IncidentReportService $service)
    {
        $this->middleware(['auth', 'verified', 'permission:incidents.view']);
    }

    public function exportDailyReport(DailyReportRequest $request)
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));
        $data = $this->service->dailyData($date);

        return $this->export($request, $data, "rapport-journalier-{$date->format('Y-m-d')}");
    }

    public function exportMonthlyReport(MonthlyReportRequest $request)
    {
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->format('Y-m')));
        $data = $this->service->monthlyData($month);

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
}
