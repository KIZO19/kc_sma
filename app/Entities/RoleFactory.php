<?php

namespace App\Entities;

class RoleFactory
{
    public static function make(string $role): RoleEntity
    {
        return match ($role) {
            'super_admin' => new SuperAdmin(),
            'ecole_admin' => new EcoleAdmin(),
            'préfet_école' => new PrefetEcole(),
            'DE_école' => new DEEcole(),
            'DD_école' => new DDEcole(),
            'DP_école' => new DPEcole(),
            'DA_école' => new DAEcole(),
            'comptable_école' => new ComptableEcole(),
            'sec_école' => new SecEcole(),
            'promoteur_école' => new PromoteurEcole(),
            'enseignant_école' => new EnseignantEcole(),
            'eleve_ecole' => new EleveEcole(),
            'parent_ecole' => new ParentEcole(),
            default => new RoleEntity($role, ucfirst(str_replace('_', ' ', $role))),
        };
    }
}
