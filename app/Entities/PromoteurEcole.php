<?php

namespace App\Entities;

class PromoteurEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('promoteur_école', 'Promoteur');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_school', 'manage_discipline', 'manage_users', 'view_metrics', 'view_reports'], true);
    }
}
