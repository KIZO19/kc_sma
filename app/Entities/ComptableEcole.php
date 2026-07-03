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
        return in_array($permission, [
            'manage_finances',
            'record_payments',
            'manage_agent_payroll',
            'approve_exemptions',
            'plan_collections',
            'create_fees',
            'manage_currency',
        ], true);
    }
}
