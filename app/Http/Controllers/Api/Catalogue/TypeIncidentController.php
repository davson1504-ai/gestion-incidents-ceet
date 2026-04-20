<?php

namespace App\Http\Controllers\Api\Catalogue;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TypeIncidentResource;
use App\Models\TypeIncident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypeIncidentController extends ApiController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', TypeIncident::class);

        $query = TypeIncident::query()
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('code', 'like', $term)
                        ->orWhere('libelle', 'like', $term);
                });
            })
            ->orderBy('libelle');

        return TypeIncidentResource::collection($query->paginate(min((int) $request->input('per_page', 50), 200))->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TypeIncident::class);

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:type_incidents,code'],
            'libelle' => ['required', 'string', 'max:150', 'unique:type_incidents,libelle'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $type = TypeIncident::create($data);

        return $this->success(TypeIncidentResource::make($type), 'Type incident cree avec succes.', 201);
    }

    public function update(Request $request, TypeIncident $typeIncident): JsonResponse
    {
        $this->authorize('update', TypeIncident::class);

        $data = $request->validate([
            'code' => ['sometimes', 'nullable', 'string', 'max:50', 'unique:type_incidents,code,'.$typeIncident->id],
            'libelle' => ['sometimes', 'string', 'max:150', 'unique:type_incidents,libelle,'.$typeIncident->id],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $typeIncident->update($data);

        return $this->success(TypeIncidentResource::make($typeIncident->refresh()), 'Type incident mis a jour avec succes.');
    }

    public function destroy(TypeIncident $typeIncident): JsonResponse
    {
        $this->authorize('delete', TypeIncident::class);

        $typeIncident->delete();

        return $this->success(null, 'Type incident supprime avec succes.');
    }
}
