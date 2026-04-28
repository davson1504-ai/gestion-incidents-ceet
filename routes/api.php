<?php

use App\Http\Controllers\Api\Catalogue\CauseController as CatalogueCauseController;
use App\Http\Controllers\Api\Catalogue\DepartementController as CatalogueDepartementController;
use App\Http\Controllers\Api\Catalogue\TypeIncidentController as CatalogueTypeIncidentController;
use App\Http\Controllers\Api\Catalogue\UserController as CatalogueUserController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\IncidentAssignmentController;
use App\Http\Controllers\Api\IncidentCloseController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\IncidentInterventionController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'throttle:60,1'])->prefix('v1')->name('api.v1.')->group(function (): void {
    Route::apiResource('incidents', IncidentController::class);
    Route::post('incidents/{incident}/assign', [IncidentAssignmentController::class, 'store'])
        ->name('incidents.assign');
    Route::post('incidents/{incident}/close', [IncidentCloseController::class, 'store'])
        ->name('incidents.close');
    Route::post('incidents/{incident}/interventions', [IncidentInterventionController::class, 'store'])
        ->name('incidents.interventions.store');

    Route::prefix('catalogues')->group(function (): void {
        Route::apiResource('departements', CatalogueDepartementController::class)->except(['show']);
        Route::apiResource('types-incidents', CatalogueTypeIncidentController::class)
            ->except(['show'])
            ->parameters(['types-incidents' => 'typeIncident']);
        Route::apiResource('causes', CatalogueCauseController::class)->except(['show']);
        Route::get('users', [CatalogueUserController::class, 'index'])->name('catalogues.users.index');
    });

    Route::prefix('reports')->group(function (): void {
        Route::get('overview', [ReportController::class, 'overview'])->name('reports.overview');
        Route::get('by-type', [ReportController::class, 'byType'])->name('reports.by-type');
        Route::get('by-cause', [ReportController::class, 'byCause'])->name('reports.by-cause');
        Route::get('by-departement', [ReportController::class, 'byDepartement'])->name('reports.by-departement');
        Route::get('daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    });

    Route::prefix('exports')->group(function (): void {
        // Export endpoints: strictement throttled (10 exports par 5 min)
        Route::get('incidents.csv', [ExportController::class, 'incidentsCsv'])
            ->middleware('throttle:10,5')
            ->name('exports.incidents.csv');
        Route::get('incidents.pdf', [ExportController::class, 'incidentsPdf'])
            ->middleware('throttle:10,5')
            ->name('exports.incidents.pdf');
    });
});
