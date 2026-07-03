<?php

namespace App\Entities;

class DAEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('DA_école', 'Directeur Adjoint');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['assist_director'], true);
    }
}
