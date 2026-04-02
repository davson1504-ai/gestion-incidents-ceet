<?php

namespace App\Http\Controllers;

use App\Models\TypeIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeIncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogues.view')->only(['index']);
        $this->middleware('permission:catalogues.manage')->except(['index']);
    }

    public function index(): View
    {
        $types = TypeIncident::orderBy('libelle')->paginate(25);
        return view('catalogues.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('catalogues.types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:20', 'unique:type_incidents,code'],
            'libelle'     => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        TypeIncident::create($data);

        return redirect()->route('catalogues.types.index')->with('success', 'Type créé.');
    }

    public function edit(TypeIncident $type): View
    {
        return view('catalogues.types.edit', compact('type'));
    }

    public function update(Request $request, TypeIncident $type): RedirectResponse
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:20', 'unique:type_incidents,code,'.$type->id],
            'libelle'     => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $type->update($data);

        return redirect()->route('catalogues.types.index')->with('success', 'Type mis à jour.');
    }

    public function destroy(TypeIncident $type): RedirectResponse
    {
        $type->delete();
        return redirect()->route('catalogues.types.index')->with('success', 'Type supprimé.');
    }
}
