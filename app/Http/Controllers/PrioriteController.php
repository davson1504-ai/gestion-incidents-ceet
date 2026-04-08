<?php

namespace App\Http\Controllers;

use App\Models\Priorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrioriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:catalogues.view')->only(['index']);
        $this->middleware('permission:catalogues.manage')->except(['index']);
    }

    public function index(): View
    {
        $priorites = Priorite::orderBy('niveau')->orderBy('libelle')->paginate(25);

        return view('catalogues.priorites.index', compact('priorites'));
    }

    public function create(): View
    {
        return view('catalogues.priorites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        Priorite::create($data);

        return redirect()->route('catalogues.priorites.index')->with('success', 'Priorite creee.');
    }

    public function edit(Priorite $priorite): View
    {
        return view('catalogues.priorites.edit', compact('priorite'));
    }

    public function update(Request $request, Priorite $priorite): RedirectResponse
    {
        $data = $this->validated($request, $priorite->id);
        $data['is_active'] = $request->boolean('is_active');

        $priorite->update($data);

        return redirect()->route('catalogues.priorites.index')->with('success', 'Priorite mise a jour.');
    }

    public function destroy(Priorite $priorite): RedirectResponse
    {
        if ($priorite->incidents()->exists()) {
            return back()->with('error', 'Suppression impossible: cette priorite est utilisee par des incidents.');
        }

        $priorite->delete();

        return redirect()->route('catalogues.priorites.index')->with('success', 'Priorite supprimee.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:priorites,code';
        if ($ignoreId !== null) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $uniqueRule],
            'libelle' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'niveau' => ['required', 'integer', 'min:1'],
            'couleur' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}

