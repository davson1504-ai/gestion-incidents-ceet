<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\IncidentFilterRequest;
use App\Http\Resources\ReportSummaryResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReportController extends ApiController
{
    public function __construct(private readonly ReportService $reportService) {}

    public function overview(IncidentFilterRequest $request): JsonResponse
    {
        Gate::authorize('viewReports');

        $data = $this->reportService->overview($request->validated());

        return $this->success(ReportSummaryResource::make($data));
    }

    public function byType(IncidentFilterRequest $request): JsonResponse
    {
        Gate::authorize('viewReports');

        return $this->success($this->reportService->byType($request->validated()));
    }

    public function byCause(IncidentFilterRequest $request): JsonResponse
    {
        Gate::authorize('viewReports');

        return $this->success($this->reportService->byCause($request->validated()));
    }

    public function byDepartement(IncidentFilterRequest $request): JsonResponse
    {
        Gate::authorize('viewReports');

        return $this->success($this->reportService->byDepartement($request->validated()));
    }

    public function daily(IncidentFilterRequest $request): JsonResponse
    {
        Gate::authorize('viewReports');

        return $this->success($this->reportService->daily($request->validated()));
    }

    public function monthly(IncidentFilterRequest $request): JsonResponse
    {
        Gate::authorize('viewReports');

        return $this->success($this->reportService->monthly($request->validated()));
    }
}
