<?php

namespace App\Services;

use App\Models\Cause;
use App\Models\Departement;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class IncidentCatalogueService
{
    private const CACHE_DURATION_SECONDS = 86400; // 24 heures

    public function activeFormCatalogues(): array
    {
        return Cache::remember('catalogues.active_form', self::CACHE_DURATION_SECONDS, fn() => [
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'statuts' => Statut::where('is_active', true)->orderBy('ordre')->get(),
            'priorites' => Priorite::where('is_active', true)->orderBy('niveau')->get(),
            'types' => TypeIncident::where('is_active', true)->orderBy('libelle')->get(),
            'causes' => Cause::where('is_active', true)->orderBy('libelle')->get(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function listingCatalogues(): array
    {
        return Cache::remember('catalogues.listing', self::CACHE_DURATION_SECONDS, fn() => [
            'departements' => Departement::orderBy('nom')->get(),
            'statuts' => Statut::orderBy('ordre')->get(),
            'priorites' => Priorite::orderBy('niveau')->get(),
            'types' => TypeIncident::orderBy('libelle')->get(),
            'causes' => Cause::orderBy('libelle')->get(),
            'operateurs' => User::active()->orderBy('name')->get(),
        ]);
    }

    public function openIncidentCatalogues(): array
    {
        return Cache::remember('catalogues.open_incidents', self::CACHE_DURATION_SECONDS, fn() => [
            'departements' => Departement::where('is_active', true)->orderBy('nom')->get(),
            'priorites' => Priorite::where('is_active', true)->orderBy('niveau')->get(),
        ]);
    }

    public static function invalidateCache(): void
    {
        Cache::forget('catalogues.active_form');
        Cache::forget('catalogues.listing');
        Cache::forget('catalogues.open_incidents');
    }
}
