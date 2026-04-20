<?php

namespace App\Providers;

use App\Models\Cause;
use App\Models\Departement;
use App\Models\Incident;
use App\Models\TypeIncident;
use App\Policies\CataloguePolicy;
use App\Policies\IncidentPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::preventLazyLoading($this->app->isProduction());

        Gate::policy(Incident::class, IncidentPolicy::class);
        Gate::policy(Departement::class, CataloguePolicy::class);
        Gate::policy(TypeIncident::class, CataloguePolicy::class);
        Gate::policy(Cause::class, CataloguePolicy::class);

        Gate::define('viewReports', [ReportPolicy::class, 'viewAny']);
        Gate::define('exportReports', [ReportPolicy::class, 'export']);
    }
}
