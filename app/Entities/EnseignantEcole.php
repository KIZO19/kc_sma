<?php

namespace App\Entities;

class EnseignantEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('enseignant_école', 'Enseignant');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['enter_grades', 'manage_classes'], true);
    }
}
