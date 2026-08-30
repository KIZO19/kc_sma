<?php

namespace App\Entities;

class AgentEcole extends RoleEntity
{
    public function __construct()
    {
        parent::__construct('agent_ecole', 'Agent de l’école');
    }

    public function can(string $permission): bool
    {
        return in_array($permission, ['manage_school_records', 'register_students', 'view_school_data'], true);
    }
}
