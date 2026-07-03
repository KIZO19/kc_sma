<?php

namespace App\Entities;

class ParentEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('parent_ecole', 'Parent');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['view_child_records'], true);
    }
}
