<?php

namespace App\Entities;

class DDEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('DD_école', 'Directeur Département');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_department', 'view_reports'], true);
    }
}
