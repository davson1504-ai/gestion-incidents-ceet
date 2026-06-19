<?php

namespace App\Http\Controllers;

use App\Models\Statut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StatutController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogues.view')->only(['index']);
        $this->middleware('permission:catalogues.manage')->except(['index']);
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $statuts = Statut::query()
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = '%'.$filters['q'].'%';

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('code', 'like', $search)
                        ->orWhere('libelle', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when(in_array($filters['status'], ['active', 'inactive', 'final'], true), function ($query) use ($filters): void {
                if ($filters['status'] === 'final') {
                    $query->where('is_final', true);

                    return;
                }

                $query->where('is_active', $filters['status'] === 'active');
            })
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->paginate(5)
            ->withQueryString();

        $statusMetrics = [
            'total' => Statut::count(),
            'active' => Statut::where('is_active', true)->count(),
            'final' => Statut::where('is_final', true)->count(),
            'last_updated_at' => Statut::max('updated_at'),
        ];

        return view('catalogues.statuts.index', compact('statuts', 'filters', 'statusMetrics'));
    }

    public function create(): View
    {
        return view('catalogues.statuts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('code') && $request->filled('libelle')) {
            $request->merge([
                'code' => $this->nextCodeForLabel((string) $request->input('libelle')),
            ]);
        }

        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_final'] = $request->boolean('is_final');

        Statut::create($data);

        return redirect()->route('catalogues.statuts.index')->with('success', 'Statut cree.');
    }

    public function edit(Statut $statut): View
    {
        return view('catalogues.statuts.edit', compact('statut'));
    }

    public function update(Request $request, Statut $statut): RedirectResponse
    {
        $data = $this->validated($request, $statut->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_final'] = $request->boolean('is_final');

        $statut->update($data);

        return redirect()->route('catalogues.statuts.index')->with('success', 'Statut mis a jour.');
    }

    public function destroy(Statut $statut): RedirectResponse
    {
        if ($statut->incidents()->exists()) {
            return back()->with('error', 'Suppression impossible: ce statut est utilise par des incidents.');
        }

        $statut->delete();

        return redirect()->route('catalogues.statuts.index')->with('success', 'Statut supprime.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:statuses,code';
        if ($ignoreId !== null) {
            $uniqueRule .= ','.$ignoreId;
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $uniqueRule],
            'libelle' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'couleur' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'is_final' => ['nullable', 'boolean'],
        ]);
    }

    private function nextCodeForLabel(string $label): string
    {
        $baseCode = Str::upper(Str::slug($label, '_')) ?: 'STATUT';
        $code = $baseCode;
        $suffix = 2;

        while (Statut::query()->where('code', $code)->exists()) {
            $code = $baseCode.'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
