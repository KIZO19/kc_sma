<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class PageController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $route = str_replace(BASE_URL, '', $route);
        $route = trim($route, '/');

        $page = $this->renderPage($route, $role);

        if (!$page['found']) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        if (!$page['accessible']) {
            header('Location: ' . BASE_URL . '/error/accessDenied');
            exit;
        }

        $this->view($page['view'], array_merge($page['data'], [
            'title' => APP_NAME . ' - ' . $page['title'],
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
        ]));
    }

    private function renderPage(string $route, string $role): array
    {
        $accessMap = [
            'inscriptions' => ['super_admin', 'ecole_admin', 'agent_ecole', 'comptable_école', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école'],
            'eleves' => ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école'],
            'notes' => ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'],
            'paiements' => ['super_admin', 'ecole_admin', 'comptable_école', 'sec_école', 'parent_ecole'],
            'bulletins' => ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'],
            'presences' => ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole'],
            'evaluations' => ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école'],
            'evenements' => ['super_admin', 'ecole_admin', 'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'sec_école', 'enseignant_école', 'eleve_ecole', 'parent_ecole'],
            'facturation' => ['super_admin', 'ecole_admin', 'comptable_école'],
            'comptes' => ['super_admin', 'ecole_admin', 'comptable_école'],
            'rapports' => ['super_admin', 'ecole_admin', 'comptable_école', 'sec_école'],
            'statistiques' => ['super_admin', 'ecole_admin'],
            'mes-cours' => ['enseignant_école'],
            'mon-emploi' => ['eleve_ecole', 'enseignant_école'],
            'mes-notes' => ['eleve_ecole', 'parent_ecole'],
            'mes-presences' => ['eleve_ecole', 'enseignant_école'],
            'mes-paiements' => ['eleve_ecole', 'parent_ecole'],
            'mes-enfants' => ['parent_ecole'],
            'bulletins-enfant' => ['parent_ecole'],
            'messages' => ['parent_ecole', 'enseignant_école', 'sec_école', 'ecole_admin'],
            'paiements-enfant' => ['parent_ecole'],
            'agents' => ['super_admin', 'ecole_admin'],
            'parents' => ['super_admin', 'ecole_admin', 'sec_école'],
            'settings' => ['super_admin'],
        ];

        $pages = [
            'inscriptions' => [
                'title' => 'Inscriptions',
                'pageTitle' => 'Inscriptions',
                'pageDescription' => 'Inscrivez de nouveaux élèves et suivez les dossiers scolaires.',
                'pageContent' => 'Ici vous pouvez gérer les inscriptions des élèves de l’école.',
                'pageNotes' => 'Page de gestion des inscriptions pour les rôles autorisés.',
            ],
            'eleves' => [
                'title' => 'Élèves',
                'pageTitle' => 'Élèves',
                'pageDescription' => 'Affichez et gérez les informations des élèves de l’école.',
                'pageContent' => 'Liste des élèves, profils et actions de gestion.',
                'pageNotes' => 'Page de gestion élève pour administrateurs et responsables pédagogiques.',
            ],
            'notes' => [
                'title' => 'Notes',
                'pageTitle' => 'Notes',
                'pageDescription' => 'Créez des évaluations et enregistrez les notes.',
                'pageContent' => 'Gestion des notes et des évaluations.',
                'pageNotes' => 'Page réservée aux enseignants et aux responsables pédagogiques.',
            ],
            'paiements' => [
                'title' => 'Paiements',
                'pageTitle' => 'Paiements',
                'pageDescription' => 'Gérez les paiements et les reçus des élèves.',
                'pageContent' => 'Suivi des paiements scolaires et des factures.',
                'pageNotes' => 'Accessible aux services financiers et à l’administration.',
            ],
            'bulletins' => [
                'title' => 'Bulletins',
                'pageTitle' => 'Bulletins',
                'pageDescription' => 'Consultez et publiez les bulletins scolaires.',
                'pageContent' => 'Gestion des bulletins et de la communication des résultats.',
                'pageNotes' => 'Rôle pédagogique ou administratif requis.',
            ],
            'presences' => [
                'title' => 'Présences',
                'pageTitle' => 'Présences',
                'pageDescription' => 'Suivez les présences des élèves et du personnel.',
                'pageContent' => 'Marquage et contrôle des présences.',
                'pageNotes' => 'Utilisez cette page pour le suivi quotidien.',
            ],
            'evaluations' => [
                'title' => 'Évaluations',
                'pageTitle' => 'Évaluations',
                'pageDescription' => 'Créez et gérez les évaluations pédagogiques.',
                'pageContent' => 'Définition des examens, contrôles et devoirs.',
                'pageNotes' => 'Réservé aux enseignants et responsables pédagogiques.',
            ],
            'evenements' => [
                'title' => 'Événements',
                'pageTitle' => 'Événements',
                'pageDescription' => 'Planifiez et publiez les événements scolaires.',
                'pageContent' => 'Calendrier des événements et annonces.',
                'pageNotes' => 'Partagé entre administration et vie scolaire.',
            ],
            'facturation' => [
                'title' => 'Facturation',
                'pageTitle' => 'Facturation',
                'pageDescription' => 'Gérez les frais et les factures de l’école.',
                'pageContent' => 'Suivi des factures et des paiements reçus.',
                'pageNotes' => 'Accessible aux comptables et à l’administration.',
            ],
            'comptes' => [
                'title' => 'Caisses & Banques',
                'pageTitle' => 'Caisses & Banques',
                'pageDescription' => 'Gérez les caisses et les comptes bancaires.',
                'pageContent' => 'Suivi des flux financiers de l’école.',
                'pageNotes' => 'Réservé aux comptables et aux responsables financiers.',
            ],
            'rapports' => [
                'title' => 'Rapports',
                'pageTitle' => 'Rapports',
                'pageDescription' => 'Consultez les rapports et les analyses.',
                'pageContent' => 'Tableaux de bord et exports financiers/pédagogiques.',
                'pageNotes' => 'Utilisé pour les bilans mensuels et trimestriels.',
            ],
            'statistiques' => [
                'title' => 'Statistiques',
                'pageTitle' => 'Statistiques',
                'pageDescription' => 'Analysez les indicateurs clés de l’école.',
                'pageContent' => 'Données de fréquentation, notes et paiements.',
                'pageNotes' => 'Affiché pour la direction et le pilotage.',
            ],
            'mes-cours' => [
                'title' => 'Mes cours',
                'pageTitle' => 'Mes cours',
                'pageDescription' => 'Consultez vos cours et votre emploi du temps.',
                'pageContent' => 'Liste des cours et matières assignées.',
                'pageNotes' => 'Accessible aux enseignants.',
            ],
            'mon-emploi' => [
                'title' => 'Mon emploi',
                'pageTitle' => 'Mon emploi du temps',
                'pageDescription' => 'Consultez votre emploi du temps et vos créneaux.',
                'pageContent' => 'Vue de l’emploi du temps personnel.',
                'pageNotes' => 'Pour élèves et enseignants selon le rôle.',
            ],
            'mes-notes' => [
                'title' => 'Mes notes',
                'pageTitle' => 'Mes notes',
                'pageDescription' => 'Consultez vos notes scolaires.',
                'pageContent' => 'Historique des notes et moyennes.',
                'pageNotes' => 'Accessible aux élèves et aux parents.',
            ],
            'mes-presences' => [
                'title' => 'Mes présences',
                'pageTitle' => 'Mes présences',
                'pageDescription' => 'Suivez votre présence en cours.',
                'pageContent' => 'Historique des présences et absences.',
                'pageNotes' => 'Pour les élèves et les enseignants.',
            ],
            'mes-paiements' => [
                'title' => 'Mes paiements',
                'pageTitle' => 'Mes paiements',
                'pageDescription' => 'Suivez vos paiements et reçus.',
                'pageContent' => 'Détails des paiements effectués.',
                'pageNotes' => 'Pour les élèves et les parents.',
            ],
            'mes-enfants' => [
                'title' => 'Enfants',
                'pageTitle' => 'Enfants',
                'pageDescription' => 'Accédez aux dossiers scolaires de vos enfants.',
                'pageContent' => 'Liste des enfants et informations associées.',
                'pageNotes' => 'Réservé aux parents.',
            ],
            'bulletins-enfant' => [
                'title' => 'Bulletins',
                'pageTitle' => 'Bulletins de mon enfant',
                'pageDescription' => 'Consultez les bulletins scolaires de votre enfant.',
                'pageContent' => 'Bulletins et appréciations par période.',
                'pageNotes' => 'Réservé aux parents.',
            ],
            'messages' => [
                'title' => 'Messages',
                'pageTitle' => 'Messages',
                'pageDescription' => 'Envoyez et recevez des messages avec l’administration.',
                'pageContent' => 'Messagerie interne pour l’école.',
                'pageNotes' => 'Facilite la communication entre utilisateurs.',
            ],
            'paiements-enfant' => [
                'title' => 'Paiements',
                'pageTitle' => 'Paiements enfant',
                'pageDescription' => 'Suivez les paiements effectués pour votre enfant.',
                'pageContent' => 'Détails financiers de votre enfant.',
                'pageNotes' => 'Accessible aux parents uniquement.',
            ],
            'agents' => [
                'title' => 'Agents',
                'pageTitle' => 'Agents',
                'pageDescription' => 'Gérez les comptes des agents de l’école.',
                'pageContent' => 'Liste du personnel administratif et enseignant.',
                'pageNotes' => 'Accessible aux administrateurs.',
            ],
            'parents' => [
                'title' => 'Parents',
                'pageTitle' => 'Parents',
                'pageDescription' => 'Gérez les comptes et contacts parents.',
                'pageContent' => 'Liste des parents et leurs enfants associés.',
                'pageNotes' => 'Accessible aux secrétaires et administrateurs.',
            ],
            'settings' => [
                'title' => 'Paramètres',
                'pageTitle' => 'Paramètres',
                'pageDescription' => 'Configurez les paramètres de l’application.',
                'pageContent' => 'Réglages globaux et préférences du compte.',
                'pageNotes' => 'Réservé aux administrateurs.',
            ],
        ];

        $accessible = true;
        if ($route !== '' && isset($accessMap[$route]) && !in_array($role, $accessMap[$route], true)) {
            $accessible = false;
        }

        if (isset($pages[$route])) {
            $page = $pages[$route];
            return [
                'found' => true,
                'accessible' => $accessible,
                'view' => 'pages/page',
                'title' => $page['title'],
                'data' => [
                    'pageTitle' => $page['pageTitle'],
                    'pageDescription' => $page['pageDescription'],
                    'pageContent' => $page['pageContent'],
                    'pageNotes' => $page['pageNotes'],
                ],
            ];
        }

        if ($route === 'ecoles/generatePassword') {
            $accessible = in_array($role, ['super_admin', 'ecole_admin'], true);
            return [
                'found' => true,
                'accessible' => $accessible,
                'view' => 'ecoles/generate_password',
                'title' => 'Générer mot de passe',
                'data' => [],
            ];
        }

        return [
            'found' => false,
            'accessible' => false,
            'view' => 'pages/page',
            'title' => 'Page introuvable',
            'data' => [
                'pageTitle' => 'Page introuvable',
                'pageDescription' => 'La page demandée est introuvable ou n’est pas encore disponible.',
                'pageContent' => 'Cette page n’existe pas encore ou n’est pas accessible depuis votre rôle.',
            ],
        ];
    }
}
