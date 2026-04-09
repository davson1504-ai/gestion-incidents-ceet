<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogues.view')->only(['index']);
        $this->middleware('permission:catalogues.manage')->except(['index']);
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'selected' => $request->input('selected'),
        ];

        $query = Departement::query()
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';

                $q->where(function ($inner) use ($term) {
                    $inner->where('code', 'like', $term)
                        ->orWhere('nom', 'like', $term)
                        ->orWhere('zone', 'like', $term)
                        ->orWhere('poste_repartition', 'like', $term)
                        ->orWhere('poste_source', 'like', $term);
                });
            });

        $departements = (clone $query)
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        $selectedDepartement = null;

        if ($filters['selected']) {
            $selectedDepartement = (clone $query)->whereKey($filters['selected'])->first();
        }

        if (! $selectedDepartement) {
            $selectedDepartement = $departements->first();
        }

        $totalCount = Departement::count();
        $activeCount = Departement::where('is_active', true)->count();
        $zoneCount = Departement::query()
            ->whereNotNull('zone')
            ->where('zone', '!=', '')
            ->distinct('zone')
            ->count('zone');
        $totalPowerMw = (float) Departement::sum('charge_maximale');

        return view('catalogues.departements.index', [
            'departements' => $departements,
            'selectedDepartement' => $selectedDepartement,
            'filters' => $filters,
            'stats' => [
                'totalCount' => $totalCount,
                'activeCount' => $activeCount,
                'zoneCount' => $zoneCount,
                'totalPowerMw' => $totalPowerMw,
            ],
        ]);
    }

    public function create(): View
    {
        return view('catalogues.departements.create');
    }

    public function store(Request $request): RedirectResponse
    {
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

        $data['is_active'] = $request->boolean('is_active');

        $created = Departement::create($data);

        return redirect()
            ->route('catalogues.departements.index', ['selected' => $created->id])
            ->with('success', 'Departement cree.');
    }

    public function edit(Departement $departement): View
    {
        return view('catalogues.departements.edit', compact('departement'));
    }

    public function update(Request $request, Departement $departement): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departements,code,' . $departement->id],
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

        $data['is_active'] = $request->boolean('is_active');

        $departement->update($data);

        return redirect()
            ->route('catalogues.departements.index', ['selected' => $departement->id])
            ->with('success', 'Departement mis a jour.');
    }

    public function destroy(Departement $departement): RedirectResponse
    {
        $departement->delete();

        return redirect()->route('catalogues.departements.index')->with('success', 'Departement supprime.');
    }
}
