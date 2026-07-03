<?php

namespace App\Entities;

class EcoleAdmin extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('ecole_admin', 'Administrateur École');
    }

    public function can(string $permission): bool
    {
        // extend with real permission checks
        return in_array($permission, ['manage_school', 'view_reports'], true);
    }
}
