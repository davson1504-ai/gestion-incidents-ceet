<?php

use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistoriqueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profil
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Incidents
    Route::middleware('role:Administrateur|Superviseur|Opérateur')->group(function () {
        Route::resource('incidents', IncidentController::class);
        Route::get('incidents-export', [IncidentController::class, 'export'])->name('incidents.export');
        Route::get('reports/daily',   [\App\Http\Controllers\ReportController::class, 'exportDailyReport'])->name('reports.daily');
        Route::get('reports/monthly', [\App\Http\Controllers\ReportController::class, 'exportMonthlyReport'])->name('reports.monthly');
    });

    // Historique (Administrateur + Superviseur uniquement)
    Route::middleware('role:Administrateur|Superviseur')->group(function () {
        Route::get('historique',        [HistoriqueController::class, 'index'])->name('historique.index');
        Route::get('historique/export', [HistoriqueController::class, 'export'])->name('historique.export');
    });

    // Catalogues
    Route::prefix('catalogues')->name('catalogues.')->group(function () {
        Route::resource('departements', \App\Http\Controllers\DepartementController::class)->except(['show']);
        Route::resource('types',        \App\Http\Controllers\TypeIncidentController::class)->except(['show']);
        Route::resource('causes',       \App\Http\Controllers\CauseController::class)->except(['show']);
    });

});

require __DIR__.'/auth.php';