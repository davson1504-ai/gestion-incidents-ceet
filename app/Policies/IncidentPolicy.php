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
        return $user->can('incidents.view') || $user->can('incidents.view.assigned');
    }

    public function view(User $user, Incident $incident): bool
    {
        if (! $user->can('incidents.view') && ! $user->can('incidents.view.assigned')) {
            return false;
        }

        if ($user->isSuperviseur()) {
            return true;
        }

        return $incident->responsable_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('incidents.create');
    }

    public function update(User $user, Incident $incident): bool
    {
        if (! $user->can('incidents.update')) {
            return false;
        }

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
        return $user->can('incidents.assign') && $user->isSuperviseur();
    }

    public function close(User $user, Incident $incident): bool
    {
        return $user->can('incidents.close') && $user->isSuperviseur();
    }

    public function intervene(User $user, Incident $incident): bool
    {
        if ($user->isSuperviseur()) {
            return true;
        }

        return $user->isOperateur() && $incident->responsable_id === $user->id;
    }

    public function take(User $user, Incident $incident): bool
    {
        return $user->can('incidents.take')
            && $user->isOperateur()
            && $incident->responsable_id === $user->id;
    }

    public function resolve(User $user, Incident $incident): bool
    {
        return $user->can('incidents.resolve')
            && $user->isOperateur()
            && $incident->responsable_id === $user->id;
    }

    public function report(User $user, Incident $incident): bool
    {
        return $user->can('incidents.report')
            && $user->isOperateur()
            && $incident->responsable_id === $user->id;
    }

    public function validateResolution(User $user, Incident $incident): bool
    {
        return $user->can('incidents.validate') && $user->isSuperviseur();
    }

    public function export(User $user): bool
    {
        return $user->can('incidents.export');
    }
}
