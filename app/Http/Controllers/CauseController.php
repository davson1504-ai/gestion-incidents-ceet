<?php

namespace App\Http\Controllers;

use App\Models\Cause;
use App\Models\TypeIncident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CauseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogues.view')->only(['index']);
        $this->middleware('permission:catalogues.manage')->except(['index', 'byType']);
    }

    public function index(): View
    {
        $causes = Cause::with('typeIncident')->orderBy('libelle')->paginate(25);

        return view('catalogues.causes.index', compact('causes'));
    }

    public function create(): View
    {
        $types = TypeIncident::orderBy('libelle')->get();

        return view('catalogues.causes.create', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:causes,code'],
            'libelle' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type_incident_id' => ['required', 'exists:type_incidents,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Cause::create($data);

        return redirect()->route('catalogues.causes.index')->with('success', 'Cause creee.');
    }

    public function edit(Cause $cause): View
    {
        $types = TypeIncident::orderBy('libelle')->get();

        return view('catalogues.causes.edit', compact('cause', 'types'));
    }

    public function update(Request $request, Cause $cause): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:causes,code,'.$cause->id],
            'libelle' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'type_incident_id' => ['required', 'exists:type_incidents,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cause->update($data);

        return redirect()->route('catalogues.causes.index')->with('success', 'Cause mise a jour.');
    }

    public function destroy(Cause $cause): RedirectResponse
    {
        $cause->delete();

        return redirect()->route('catalogues.causes.index')->with('success', 'Cause supprimee.');
    }

    public function byType(TypeIncident $type): JsonResponse
    {
        $causes = Cause::query()
            ->where('type_incident_id', $type->id)
            ->where('is_active', true)
            ->orderBy('libelle')
            ->get(['id', 'code', 'libelle']);

        return response()->json($causes);
    }
}
