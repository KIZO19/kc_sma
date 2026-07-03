<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        require $viewFile;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    protected function getModulesForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                ['name' => 'Écoles', 'path' => '/ecoles', 'icon' => 'bi-building'],
                ['name' => 'Utilisateurs', 'path' => '/utilisateurs', 'icon' => 'bi-person-check'],
                ['name' => 'Agents', 'path' => '/agents', 'icon' => 'bi-people-fill'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Paiements', 'path' => '/paiements', 'icon' => 'bi-currency-dollar'],
                ['name' => 'Générer mot de passe', 'path' => '/ecoles/generatePassword', 'icon' => 'bi-key'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Bulletins', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Évaluations', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
                ['name' => 'Evenements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
                ['name' => 'Facturation', 'path' => '/facturation', 'icon' => 'bi-receipt'],
                ['name' => 'Caisses & Banques', 'path' => '/comptes', 'icon' => 'bi-bank'],
                ['name' => 'Rapports', 'path' => '/rapports', 'icon' => 'bi-file-earmark-bar-graph'],
                ['name' => 'Statistiques', 'path' => '/statistiques', 'icon' => 'bi-bar-chart-line'],
                ['name' => 'Mes cours', 'path' => '/mes-cours', 'icon' => 'bi-book'],
                ['name' => 'Mon emploi', 'path' => '/mon-emploi', 'icon' => 'bi-calendar2-week'],
                ['name' => 'Mes notes', 'path' => '/mes-notes', 'icon' => 'bi-bar-chart-steps'],
                ['name' => 'Mes présences', 'path' => '/mes-presences', 'icon' => 'bi-check2-square'],
                ['name' => 'Mes paiements', 'path' => '/mes-paiements', 'icon' => 'bi-wallet2'],
                ['name' => 'Enfants', 'path' => '/mes-enfants', 'icon' => 'bi-people'],
                ['name' => 'Bulletins parents', 'path' => '/bulletins-enfant', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Messages', 'path' => '/messages', 'icon' => 'bi-chat-dots'],
            ],
            'ecole_admin' => [
                ['name' => 'Écoles', 'path' => '/ecoles', 'icon' => 'bi-building'],
                ['name' => 'Agents', 'path' => '/agents', 'icon' => 'bi-people-fill'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Paiements', 'path' => '/paiements', 'icon' => 'bi-currency-dollar'],
                ['name' => 'Générer mot de passe', 'path' => '/ecoles/generatePassword', 'icon' => 'bi-key'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Bulletins', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Évaluations', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
                ['name' => 'Evenements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
                ['name' => 'Facturation', 'path' => '/facturation', 'icon' => 'bi-receipt'],
                ['name' => 'Caisses & Banques', 'path' => '/comptes', 'icon' => 'bi-bank'],
                ['name' => 'Rapports', 'path' => '/rapports', 'icon' => 'bi-file-earmark-bar-graph'],
                ['name' => 'Statistiques', 'path' => '/statistiques', 'icon' => 'bi-bar-chart-line'],
                ['name' => 'Mes cours', 'path' => '/mes-cours', 'icon' => 'bi-book'],
                ['name' => 'Mon emploi', 'path' => '/mon-emploi', 'icon' => 'bi-calendar2-week'],
                ['name' => 'Mes notes', 'path' => '/mes-notes', 'icon' => 'bi-bar-chart-steps'],
                ['name' => 'Mes présences', 'path' => '/mes-presences', 'icon' => 'bi-check2-square'],
                ['name' => 'Mes paiements', 'path' => '/mes-paiements', 'icon' => 'bi-wallet2'],
                ['name' => 'Enfants', 'path' => '/mes-enfants', 'icon' => 'bi-people'],
                ['name' => 'Bulletins parents', 'path' => '/bulletins-enfant', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Messages', 'path' => '/messages', 'icon' => 'bi-chat-dots'],
            ],
            'promoteur_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école' => [
                ['name' => 'Admissions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Utilisateurs', 'path' => '/utilisateurs', 'icon' => 'bi-person-check'],
                ['name' => 'Classes', 'path' => '/classes', 'icon' => 'bi-houses'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Bulletins', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Évaluations', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
                ['name' => 'Événements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
                ['name' => 'Générer mot de passe', 'path' => '/ecoles/generatePassword', 'icon' => 'bi-key'],
            ],
            'comptable_école' => [
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Facturation', 'path' => '/facturation', 'icon' => 'bi-receipt'],
                ['name' => 'Frais', 'path' => '/frais', 'icon' => 'bi-tag'],
                ['name' => 'Devises', 'path' => '/devises', 'icon' => 'bi-currency-exchange'],
                ['name' => 'Dérogations', 'path' => '/derogations', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Recouvrements', 'path' => '/recouvrements', 'icon' => 'bi-cash-stack'],
                ['name' => 'Caisses & Banques', 'path' => '/comptes', 'icon' => 'bi-bank'],
                ['name' => 'Paiements', 'path' => '/paiements', 'icon' => 'bi-currency-dollar'],
                ['name' => 'Rapports', 'path' => '/rapports', 'icon' => 'bi-file-earmark-bar-graph'],
            ],
            'sec_école' => [
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Classes', 'path' => '/classes', 'icon' => 'bi-houses'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Générer mot de passe', 'path' => '/ecoles/generatePassword', 'icon' => 'bi-key'],
                ['name' => 'Rapports', 'path' => '/rapports', 'icon' => 'bi-file-earmark-bar-graph'],
            ],
            'agent_ecole' => [
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
            ],
            'enseignant_école' => [
                ['name' => 'Mes cours', 'path' => '/mes-cours', 'icon' => 'bi-book'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Événements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
            ],
            'eleve_ecole' => [
                ['name' => 'Mon emploi du temps', 'path' => '/mon-emploi', 'icon' => 'bi-calendar2-week'],
                ['name' => 'Mes notes', 'path' => '/mes-notes', 'icon' => 'bi-bar-chart-steps'],
                ['name' => 'Mes présences', 'path' => '/mes-presences', 'icon' => 'bi-check2-square'],
                ['name' => 'Mes paiements', 'path' => '/mes-paiements', 'icon' => 'bi-wallet2'],
            ],
            'parent_ecole' => [
                ['name' => 'Enfants', 'path' => '/mes-enfants', 'icon' => 'bi-people'],
                ['name' => 'Bulletins', 'path' => '/bulletins-enfant', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Paiements', 'path' => '/paiements-enfant', 'icon' => 'bi-currency-dollar'],
                ['name' => 'Messages', 'path' => '/messages', 'icon' => 'bi-chat-dots'],
            ],
            default => [
                ['name' => 'Accueil', 'path' => '/dashboard', 'icon' => 'bi-house'],
            ],
        };
    }
}
