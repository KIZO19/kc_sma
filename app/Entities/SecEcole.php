<?php

namespace App\Entities;

class SecEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('sec_école', 'Secrétaire');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_records', 'assist_admin'], true);
    }
}
