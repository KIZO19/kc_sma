<?php

namespace App\Entities;

class EleveEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('eleve_ecole', 'Élève');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['view_own_records'], true);
    }
}
