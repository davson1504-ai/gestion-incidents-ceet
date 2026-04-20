<?php

namespace App\Http\Controllers\Api\Catalogue;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function index(Request $request)
    {
        if (! $request->user()->isAdmin() && ! $request->user()->isSuperviseur()) {
            throw new AuthorizationException('Acces refuse a la liste des utilisateurs.');
        }

        $query = User::query()
            ->with(['departement', 'roles'])
            ->when($request->boolean('operators_only', true), fn ($q) => $q->operateurs())
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('telephone', 'like', $term);
                });
            })
            ->orderBy('name');

        return UserResource::collection($query->paginate(min((int) $request->input('per_page', 50), 200))->withQueryString());
    }
}
