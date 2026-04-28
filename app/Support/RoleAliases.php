<?php

namespace App\Support;

final class RoleAliases
{
    /** @return array<int, string> */
    public static function adminNames(): array
    {
        return ['Administrateur', 'admin', 'ADMINISTRATEUR'];
    }

    /** @return array<int, string> */
    public static function supervisorNames(): array
    {
        return ['Superviseur', 'superviseur', 'SUPERVISEUR'];
    }

    /** @return array<int, string> */
    public static function operatorNames(): array
    {
        return ['Operateur', 'operateur', 'Opérateur', 'OPERATEUR', 'OPÉRATEUR'];
    }

    /** @return array<int, string> */
    public static function operatorLikePatterns(): array
    {
        return ['Op%rateur%', 'Operateur%'];
    }

    /** @return array<int, string> */
    public static function adminLikePatterns(): array
    {
        return ['Admin%'];
    }

    /** @return array<int, string> */
    public static function supervisorLikePatterns(): array
    {
        return ['Super%'];
    }
}
