<?php

namespace App\Entities;

class DEEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('DE_école', 'Directeur des études');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_curriculum', 'view_reports'], true);
    }
}
