<?php

namespace App\Entities;

class SuperAdmin extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('super_admin', 'Super Administrateur');
    }

    public function can(string $permission): bool
    {
        return true; // full access
    }
}
