<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class FacturationController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles([
            'comptable_école',
            'promoteur_école',
            'préfet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
        ]);

        $paiementsController = new \App\Controllers\PaiementsController();
        $paiementsController->index();
    }
}
