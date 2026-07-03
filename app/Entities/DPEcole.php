<?php

namespace App\Entities;

class DPEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('DP_école', 'Directeur Principal');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_school_operations'], true);
    }
}
