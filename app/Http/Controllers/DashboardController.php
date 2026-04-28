<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
        $this->middleware(['auth', 'verified']);
    }

    public function __invoke(Request $request): View
    {
        $payload = $this->dashboardService->buildForUser($request->user(), [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);

        return view($payload['view'], $payload['data']);
    }
}
