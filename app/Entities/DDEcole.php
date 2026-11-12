<?php

namespace App\Entities;

class DDEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('DD_école', 'Directeur de Discipline de l’école');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_discipline', 'manage_student_attendance', 'view_reports'], true);
    }
}
