<?php

namespace App\Entities;

class ComptableEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('comptable_école', 'Comptable');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_finances', 'record_payments'], true);
    }
}
