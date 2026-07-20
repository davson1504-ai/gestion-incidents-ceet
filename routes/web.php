<?php

use App\Http\Controllers\CauseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrioriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StatutController;
use App\Http\Controllers\SystemStatusController;
use App\Http\Controllers\TypeIncidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VueConsoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogueImportController;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/clear-old', [NotificationController::class, 'clearOldNotifications'])->name('notifications.clear-old');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('incidents/causes/by-type/{type}', [CauseController::class, 'byType'])
        ->middleware('permission:incidents.view|incidents.view.assigned')
        ->name('incidents.causes.by-type');

    Route::get('mes-incidents', [IncidentController::class, 'mine'])
        ->middleware('permission:incidents.view|incidents.view.assigned')
        ->name('incidents.mine');

    Route::get('incidents/en-cours', [IncidentController::class, 'enCours'])
        ->middleware('permission:incidents.view|incidents.view.assigned')
        ->name('incidents.en-cours');

    Route::get('incidents/vue-console', VueConsoleController::class)
        ->middleware(['permission:incidents.view', 'permission:reporting.view'])
        ->name('incidents.vue-console');

    Route::get('incidents-export', [IncidentController::class, 'export'])
        ->middleware('permission:incidents.export')
        ->name('incidents.export');

    Route::post('incidents/{incident}/assign', [IncidentController::class, 'assign'])
        ->middleware('permission:incidents.assign')
        ->name('incidents.assign');

    Route::post('incidents/{incident}/interventions', [IncidentController::class, 'storeIntervention'])
        ->middleware('permission:incidents.take')
        ->name('incidents.interventions.store');

    Route::post('incidents/{incident}/take', [IncidentController::class, 'take'])
        ->middleware('permission:incidents.take')
        ->name('incidents.take');

    Route::post('incidents/{incident}/resolve', [IncidentController::class, 'resolve'])
        ->middleware('permission:incidents.resolve')
        ->name('incidents.resolve');

    Route::get('incidents/{incident}/report', [IncidentController::class, 'show'])
        ->middleware('permission:reports.view|incidents.view|incidents.view.assigned')
        ->name('incidents.report.show');

    Route::get('incidents/{incident}/report/edit', [IncidentController::class, 'editRejectedReport'])
        ->middleware('permission:reports.update|incidents.report')
        ->name('incidents.report.edit');

    Route::post('incidents/{incident}/report', [IncidentController::class, 'submitReport'])
        ->middleware('permission:reports.submit|incidents.report')
        ->name('incidents.report');

    Route::patch('incidents/{incident}/report', [IncidentController::class, 'updateReport'])
        ->middleware('permission:reports.update|incidents.report')
        ->name('incidents.report.update');

    Route::post('incidents/{incident}/report/validate', [IncidentController::class, 'validateReport'])
        ->middleware('permission:reports.validate|incidents.validate')
        ->name('incidents.report.validate');

    Route::post('incidents/{incident}/report/reject', [IncidentController::class, 'rejectReport'])
        ->middleware('permission:reports.reject|incidents.validate')
        ->name('incidents.report.reject');

    Route::post('incidents/{incident}/validate', [IncidentController::class, 'validateResolution'])
        ->middleware('permission:reports.validate|incidents.validate')
        ->name('incidents.validate');

    Route::post('incidents/{incident}/close', [IncidentController::class, 'close'])
        ->middleware('permission:incidents.close')
        ->name('incidents.close');

    Route::resource('incidents', IncidentController::class)
        ->middlewareFor(['index', 'show'], 'permission:incidents.view|incidents.view.assigned')
        ->middlewareFor(['create', 'store'], 'permission:incidents.create')
        ->middlewareFor(['edit', 'update'], 'permission:incidents.update')
        ->middlewareFor(['destroy'], 'permission:incidents.delete');

    Route::get('reports', [ReportController::class, 'index'])
        ->middleware('permission:reporting.view')
        ->name('reports.index');

    Route::get('reports/daily', [ReportController::class, 'exportDailyReport'])
        ->middleware('permission:reporting.export')
        ->name('reports.daily');

    Route::get('reports/monthly', [ReportController::class, 'exportMonthlyReport'])
        ->middleware('permission:reporting.export')
        ->name('reports.monthly');

    Route::middleware('role:Administrateur|Superviseur')->group(function (): void {
        Route::get('historique', [HistoriqueController::class, 'index'])->name('historique.index');
        Route::get('historique/export', [HistoriqueController::class, 'export'])->name('historique.export');
        Route::post('historique/clear', [HistoriqueController::class, 'clear'])->name('historique.clear');
        Route::get('system/status', SystemStatusController::class)->name('system.status');
    });

    Route::prefix('catalogues')->name('catalogues.')->middleware('permission:catalogues.view')->group(function (): void {
        Route::get('/', function () {
            return view('catalogues.index', [
                'departements' => \App\Models\Departement::query()
                    ->orderBy('code')
                    ->orderBy('nom')
                    ->get(),

                'types' => \App\Models\TypeIncident::query()
                    ->orderBy('code')
                    ->orderBy('libelle')
                    ->get(),

                'causes' => \App\Models\Cause::query()
                    ->orderBy('code')
                    ->orderBy('libelle')
                    ->get(),

                'statuts' => \App\Models\Statut::query()
                    ->orderBy('ordre')
                    ->orderBy('code')
                    ->get(),

                'priorites' => \App\Models\Priorite::query()
                    ->orderBy('niveau')
                    ->orderBy('code')
                    ->get(),
            ]);
        })->name('index');

        Route::get('import/template', [CatalogueImportController::class, 'template'])
            ->middleware('permission:catalogues.manage')
            ->name('import.template');

        Route::post('import', [CatalogueImportController::class, 'store'])
            ->middleware('permission:catalogues.manage')
            ->name('import.store');

        Route::resource('departements', DepartementController::class)->except('show');
        Route::resource('types', TypeIncidentController::class)->except('show');
        Route::resource('causes', CauseController::class)->except('show');
        Route::resource('statuts', StatutController::class)->except('show');
        Route::resource('priorites', PrioriteController::class)->except('show');
    });

    Route::resource('users', UserController::class)
        ->except('show')
        ->middlewareFor(['index'], 'permission:users.view')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:users.manage');
});

require __DIR__.'/auth.php';
