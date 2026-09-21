<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ParentModel;
use App\Models\User;

class ParentsController extends Controller
{
    public function show(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $parentId = (int) ($_GET['id'] ?? 0);
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $parent = ($user['role'] ?? '') === 'super_admin'
            ? ParentModel::findById($parentId)
            : ($schoolId > 0 ? ParentModel::findByIdAndSchool($parentId, $schoolId) : null);

        if (!$parent) {
            $this->redirect('/error/notFound');
        }

        $role = $user['role'] ?? 'default';
        $this->view('parents/show', [
            'title' => APP_NAME . ' - Profil parent',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $this->getModulesForRole($role),
            'parent' => $parent,
            'children' => ParentModel::getChildren($parentId),
        ]);
    }
}