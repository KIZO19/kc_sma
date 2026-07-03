<?php

namespace App\Entities;

class RoleEntity
{
    private string $role;
    private string $label;

    public function __construct(string $role, string $label)
    {
        $this->role = $role;
        $this->label = $label;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function can(string $permission): bool
    {
        // Simple placeholder — extend with permission maps later.
        return false;
    }
}
