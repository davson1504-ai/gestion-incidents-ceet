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

    public function index(): View
    {
        $departements = Departement::orderBy('nom')->paginate(25);

        return view('catalogues.departements.index', compact('departements'));
    }

    public function create(): View
    {
        return view('catalogues.departements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'                  => ['required', 'string', 'max:50', 'unique:departements,code'],
            'nom'                   => ['required', 'string', 'max:150'],
            'zone'                  => ['nullable', 'string', 'max:150'],
            'direction_exploitation'=> ['nullable', 'string', 'max:150'],
            'poste_repartition'     => ['nullable', 'string', 'max:150'],
            'poste_source'          => ['nullable', 'string', 'max:150'],
            'transformateur'        => ['nullable', 'string', 'max:150'],
            'arrivee'               => ['nullable', 'string', 'max:100'],
            'charge_maximale'       => ['nullable', 'numeric'],
            'charge_unite'          => ['nullable', 'string', 'max:20'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        Departement::create($data);

        return redirect()->route('catalogues.departements.index')->with('success', 'Département créé.');
    }

    public function edit(Departement $departement): View
    {
        return view('catalogues.departements.edit', compact('departement'));
    }

    public function update(Request $request, Departement $departement): RedirectResponse
    {
        $data = $request->validate([
            'code'                  => ['required', 'string', 'max:50', 'unique:departements,code,'.$departement->id],
            'nom'                   => ['required', 'string', 'max:150'],
            'zone'                  => ['nullable', 'string', 'max:150'],
            'direction_exploitation'=> ['nullable', 'string', 'max:150'],
            'poste_repartition'     => ['nullable', 'string', 'max:150'],
            'poste_source'          => ['nullable', 'string', 'max:150'],
            'transformateur'        => ['nullable', 'string', 'max:150'],
            'arrivee'               => ['nullable', 'string', 'max:100'],
            'charge_maximale'       => ['nullable', 'numeric'],
            'charge_unite'          => ['nullable', 'string', 'max:20'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $departement->update($data);

        return redirect()->route('catalogues.departements.index')->with('success', 'Département mis à jour.');
    }

    public function destroy(Departement $departement): RedirectResponse
    {
        $departement->delete();

        return redirect()->route('catalogues.departements.index')->with('success', 'Département supprimé.');
    }
}
