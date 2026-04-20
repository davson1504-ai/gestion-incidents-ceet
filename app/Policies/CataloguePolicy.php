<?php

namespace App\Policies;

use App\Models\User;

class CataloguePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperviseur() || $user->isOperateur();
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperviseur();
    }

    public function update(User $user): bool
    {
        return $user->isSuperviseur();
    }

    public function delete(User $user): bool
    {
        return $user->isSuperviseur();
    }
}
