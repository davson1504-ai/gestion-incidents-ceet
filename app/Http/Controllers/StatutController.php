<?php

namespace App\Http\Controllers;

use App\Models\Statut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatutController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogues.view')->only(['index']);
        $this->middleware('permission:catalogues.manage')->except(['index']);
    }

    public function index(): View
    {
        $statuts = Statut::orderBy('ordre')->orderBy('libelle')->paginate(25);

        return view('catalogues.statuts.index', compact('statuts'));
    }

    public function create(): View
    {
        return view('catalogues.statuts.create');
    }

    public function store(Request $request): RedirectResponse
    {
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
}
