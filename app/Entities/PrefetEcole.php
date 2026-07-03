<?php

namespace App\Entities;

class PrefetEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('préfet_école', 'Préfet des études');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_academics', 'view_students'], true);
    }
}
