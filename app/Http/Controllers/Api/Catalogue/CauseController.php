<?php

namespace App\Http\Controllers\Api\Catalogue;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\CauseResource;
use App\Models\Cause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CauseController extends ApiController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cause::class);

        $query = Cause::query()
            ->with('typeIncident')
            ->when($request->filled('type_incident_id'), fn ($q) => $q->where('type_incident_id', $request->integer('type_incident_id')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('code', 'like', $term)
                        ->orWhere('libelle', 'like', $term);
                });
            })
            ->orderBy('libelle');

        return CauseResource::collection($query->paginate(min((int) $request->input('per_page', 50), 200))->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Cause::class);

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:causes,code'],
            'libelle' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'type_incident_id' => ['required', 'exists:type_incidents,id'],
        ]);

        $cause = Cause::create($data);
        $cause->load('typeIncident');

        return $this->success(CauseResource::make($cause), 'Cause creee avec succes.', 201);
    }

    public function update(Request $request, Cause $cause): JsonResponse
    {
        $this->authorize('update', Cause::class);

        $data = $request->validate([
            'code' => ['sometimes', 'nullable', 'string', 'max:50', 'unique:causes,code,'.$cause->id],
            'libelle' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'type_incident_id' => ['sometimes', 'exists:type_incidents,id'],
        ]);

        $cause->update($data);
        $cause->load('typeIncident');

        return $this->success(CauseResource::make($cause), 'Cause mise a jour avec succes.');
    }

    public function destroy(Cause $cause): JsonResponse
    {
        $this->authorize('delete', Cause::class);

        $cause->delete();

        return $this->success(null, 'Cause supprimee avec succes.');
    }
}
