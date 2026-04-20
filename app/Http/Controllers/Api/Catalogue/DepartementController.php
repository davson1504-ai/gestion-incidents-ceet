<?php

namespace App\Http\Controllers\Api\Catalogue;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\DepartementResource;
use App\Models\Departement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartementController extends ApiController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Departement::class);

        $query = Departement::query()
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%' ;
                $q->where(function ($inner) use ($term) {
                    $inner->where('code', 'like', $term)
                        ->orWhere('nom', 'like', $term)
                        ->orWhere('zone', 'like', $term)
                        ->orWhere('poste_repartition', 'like', $term);
                });
            })
            ->orderBy('nom');

        $perPage = min((int) $request->input('per_page', 50), 200);

        return DepartementResource::collection($query->paginate($perPage)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Departement::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departements,code'],
            'nom' => ['required', 'string', 'max:150'],
            'zone' => ['nullable', 'string', 'max:150'],
            'direction_exploitation' => ['nullable', 'string', 'max:150'],
            'poste_repartition' => ['nullable', 'string', 'max:150'],
            'poste_source' => ['nullable', 'string', 'max:150'],
            'transformateur' => ['nullable', 'string', 'max:150'],
            'arrivee' => ['nullable', 'string', 'max:100'],
            'charge_maximale' => ['nullable', 'numeric'],
            'charge_unite' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $departement = Departement::create($data);

        return $this->success(DepartementResource::make($departement), 'Departement cree avec succes.', 201);
    }

    public function update(Request $request, Departement $departement): JsonResponse
    {
        $this->authorize('update', Departement::class);

        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', 'unique:departements,code,'.$departement->id],
            'nom' => ['sometimes', 'string', 'max:150'],
            'zone' => ['sometimes', 'nullable', 'string', 'max:150'],
            'direction_exploitation' => ['sometimes', 'nullable', 'string', 'max:150'],
            'poste_repartition' => ['sometimes', 'nullable', 'string', 'max:150'],
            'poste_source' => ['sometimes', 'nullable', 'string', 'max:150'],
            'transformateur' => ['sometimes', 'nullable', 'string', 'max:150'],
            'arrivee' => ['sometimes', 'nullable', 'string', 'max:100'],
            'charge_maximale' => ['sometimes', 'nullable', 'numeric'],
            'charge_unite' => ['sometimes', 'nullable', 'string', 'max:20'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $departement->update($data);

        return $this->success(DepartementResource::make($departement->refresh()), 'Departement mis a jour avec succes.');
    }

    public function destroy(Departement $departement): JsonResponse
    {
        $this->authorize('delete', Departement::class);

        $departement->delete();

        return $this->success(null, 'Departement supprime avec succes.');
    }
}
