<?php

namespace App\Entities;

class Roles
{
    public static function list(): array
    {
        return [
            'super_admin' => 'Super Administrateur',
            'ecole_admin' => 'Administrateur École',
            'préfet_école' => 'Préfet des études',
            'DE_école' => 'Directeur des études',
            'DD_école' => 'Directeur Département',
            'DP_école' => 'Directeur Principal',
            'DA_école' => 'Directeur Adjoint',
            'comptable_école' => 'Comptable',
            'sec_école' => 'Secrétaire',
            'promoteur_école' => 'Promoteur',
            'enseignant_école' => 'Enseignant',
            'eleve_ecole' => 'Élève',
            'parent_ecole' => 'Parent',
        ];
    }

    public static function getEntity(string $role): RoleEntity
    {
        $list = self::list();
        $label = $list[$role] ?? ucfirst(str_replace('_', ' ', $role));
        return new RoleEntity($role, $label);
    }
}
