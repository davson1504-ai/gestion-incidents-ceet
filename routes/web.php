<?php

use App\Http\Controllers\CauseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\PrioriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StatutController;
use App\Http\Controllers\TypeIncidentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('incidents/causes/by-type/{type}', [CauseController::class, 'byType'])
        ->middleware('permission:incidents.view')
        ->name('incidents.causes.by-type');

    Route::get('incidents-export', [IncidentController::class, 'export'])->name('incidents.export');
    Route::resource('incidents', IncidentController::class);

    Route::get('reports/daily', [ReportController::class, 'exportDailyReport'])
        ->middleware('permission:incidents.view')
        ->name('reports.daily');
    Route::get('reports/monthly', [ReportController::class, 'exportMonthlyReport'])
        ->middleware('permission:incidents.view')
        ->name('reports.monthly');

    Route::middleware('role:Administrateur|Superviseur')->group(function (): void {
        Route::get('historique', [HistoriqueController::class, 'index'])->name('historique.index');
        Route::get('historique/export', [HistoriqueController::class, 'export'])->name('historique.export');
    });

    Route::prefix('catalogues')->name('catalogues.')->group(function (): void {
        Route::resource('departements', DepartementController::class)->except('show');
        Route::resource('types', TypeIncidentController::class)->except('show');
        Route::resource('causes', CauseController::class)->except('show');
        Route::resource('statuts', StatutController::class)->except('show');
        Route::resource('priorites', PrioriteController::class)->except('show');
    });

    Route::resource('users', UserController::class)->except('show');
});

require __DIR__.'/auth.php';
