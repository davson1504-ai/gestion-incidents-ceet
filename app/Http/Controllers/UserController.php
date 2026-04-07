<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index']);
        $this->middleware('permission:users.manage')->except(['index']);
    }

    public function index(Request $request): View
    {
        $roleOptions = $this->roleOptions();

        $filters = array_merge([
            'q'             => null,
            'role'          => null,
            'departement_id'=> null,
            'is_active'     => null,
        ], $request->only(['q', 'role', 'departement_id', 'is_active']));

        $users = User::query()
            ->with(['departement', 'roles'])
            ->when($filters['q'], function ($q, $v) {
                $q->where(function ($sq) use ($v) {
                    $sq->where('name', 'like', "%{$v}%")
                        ->orWhere('email', 'like', "%{$v}%")
                        ->orWhere('telephone', 'like', "%{$v}%");
                });
            })
            ->when($filters['role'], fn ($q, $v) => $q->whereHas('roles', fn ($rq) => $rq->where('name', $v)))
            ->when($filters['departement_id'], fn ($q, $v) => $q->where('departement_id', $v))
            ->when($filters['is_active'] !== null && $filters['is_active'] !== '', fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', [
            'users'        => $users,
            'filters'      => $filters,
            'roles'        => $roleOptions,
            'departements' => Departement::orderBy('nom')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles'        => $this->roleOptions(),
            'departements' => Departement::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telephone'     => ['nullable', 'string', 'max:20', 'unique:users,telephone'],
            'departement_id'=> ['nullable', 'exists:departements,id'],
            'role'          => ['required', Rule::in($this->roleOptions())],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'telephone'     => $validated['telephone'] ?? null,
            'departement_id'=> $validated['departement_id'] ?? null,
            'is_active'     => $request->boolean('is_active'),
            'password'      => Hash::make($validated['password']),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'Utilisateur cree avec succes.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'userToEdit'    => $user,
            'roles'         => $this->roleOptions(),
            'departements'  => Departement::orderBy('nom')->get(),
            'selectedRole'  => $user->roles()->value('name'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone'     => ['nullable', 'string', 'max:20', Rule::unique('users', 'telephone')->ignore($user->id)],
            'departement_id'=> ['nullable', 'exists:departements,id'],
            'role'          => ['required', Rule::in($this->roleOptions())],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'telephone'     => $validated['telephone'] ?? null,
            'departement_id'=> $validated['departement_id'] ?? null,
            'is_active'     => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis a jour avec succes.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) $user->id === (int) auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Suppression impossible pour votre propre compte.');
        }

        $hasHistory = $user->incidentsDeclares()->exists()
            || $user->incidentsSupervises()->exists()
            || $user->actions()->exists()
            || $user->logs()->exists();

        if ($hasHistory) {
            $user->update(['is_active' => false]);
            return redirect()->route('users.index')->with('success', 'Utilisateur desactive (historique conserve).');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprime.');
    }

    private function roleOptions(): array
    {
        return Role::query()
            ->whereIn('name', ['Administrateur', 'Superviseur', 'Opérateur'])
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }
}

