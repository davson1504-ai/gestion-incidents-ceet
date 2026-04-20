<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperviseur() || $user->isOperateur();
    }

    public function view(User $user, Incident $incident): bool
    {
        if ($user->isSuperviseur()) {
            return true;
        }

        return $incident->operateur_id === $user->id
            || $incident->responsable_id === $user->id
            || $incident->superviseur_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperviseur() || $user->isOperateur();
    }

    public function update(User $user, Incident $incident): bool
    {
        if ($user->isSuperviseur()) {
            return true;
        }

        return $user->isOperateur()
            && ($incident->operateur_id === $user->id || $incident->responsable_id === $user->id);
    }

    public function delete(User $user, Incident $incident): bool
    {
        return false;
    }

    public function assign(User $user, Incident $incident): bool
    {
        return $user->isSuperviseur();
    }

    public function close(User $user, Incident $incident): bool
    {
        if ($user->isSuperviseur()) {
            return true;
        }

        return $user->isOperateur() && $incident->responsable_id === $user->id;
    }

    public function export(User $user): bool
    {
        return $user->isSuperviseur();
    }
}
