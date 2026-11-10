<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $user = $data['user'] ?? null;
        if ($user !== null && (int) ($user['id'] ?? 0) > 0) {
            \App\Models\EvenementNotification::deliverDueForUser($user);
            $data['eventNotifications'] = \App\Models\EvenementNotification::getForUser((int) $user['id']);
            $data['eventNotificationsCount'] = \App\Models\EvenementNotification::countUnreadForUser((int) $user['id']);
            if (($user['role'] ?? '') === 'comptable_école') {
                \App\Models\UserMessage::notifyAccountantsOfExpiredDerogations((int) ($user['ecole_id'] ?? 0));
            }
            $data['userMessages'] = \App\Models\UserMessage::getUnreadForUser((int) $user['id']);
            $data['userMessagesCount'] = \App\Models\UserMessage::countUnreadForUser((int) $user['id']);
            $data['directMessagesCount'] = \App\Models\NotificationAccess::isDirectMessagingEnabled(
                (int) ($user['ecole_id'] ?? 0)
            )
                ? \App\Models\SchoolMessage::countUnreadForUser(
                    (int) $user['id'],
                    (int) ($user['ecole_id'] ?? 0)
                )
                : 0;
            if (($user['role'] ?? '') === 'comptable_école') {
                $schoolId = (int) ($user['ecole_id'] ?? 0);
                $data['pendingDerogationMessages'] = \App\Models\Derogation::getPendingForAccountant($schoolId);
                $data['pendingDerogationMessagesCount'] = \App\Models\Derogation::countPendingForAccountant($schoolId);
            } elseif (in_array($user['role'] ?? '', ['préfet_école', 'DE_école', 'DP_école', 'parent_ecole', 'parent_école'], true)) {
                $requesterId = (int) $user['id'];
                $data['derogationDecisionMessages'] = \App\Models\Derogation::getUnreadDecisionMessages($requesterId);
                $data['derogationDecisionMessagesCount'] = \App\Models\Derogation::countUnreadDecisionMessages($requesterId);
            }
        }
        $expiryNoticeRoles = [
            'ecole_admin',
            'promoteur_école',
            'préfet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
            'sec_école',
        ];
        if (
            $user !== null
            && in_array($user['role'] ?? '', $expiryNoticeRoles, true)
            && (int) ($user['ecole_id'] ?? 0) > 0
        ) {
            $data['planExpiryNotice'] = \App\Models\Ecole::getSubscriptionExpiryNotice(
                (int) $user['ecole_id'],
                14
            );
        }

        if (isset($data['title']) && is_string($data['title'])) {
            $data['title'] = $this->resolvePageTitle($data['title'], $user);
        } elseif (!isset($data['title'])) {
            $data['title'] = $this->resolvePageTitle(APP_NAME, $user);
        }

        extract($data);
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        $schoolRouteBaseUrl = Auth::getSchoolRouteBaseUrl();
        if ($schoolRouteBaseUrl === null) {
            require $viewFile;
            return;
        }

        ob_start();
        require $viewFile;
        $html = ob_get_clean();
        if ($html === false) {
            throw new \RuntimeException('Unable to render view: ' . $view);
        }

        $baseUrlPattern = '~' . preg_quote(BASE_URL, '~') . '(/[^"\'\s<>]*)?~';
        $prefixedHtml = preg_replace_callback(
            $baseUrlPattern,
            static function (array $matches) use ($schoolRouteBaseUrl): string {
                $path = $matches[1] ?? '';
                if (preg_match('~^/(?:assets|uploads|vendor)(?:/|$)~i', $path)) {
                    return $matches[0];
                }

                return $schoolRouteBaseUrl . $path;
            },
            $html
        );
        if ($prefixedHtml === null) {
            throw new \RuntimeException('Unable to prefix school URLs in view: ' . $view);
        }

        echo $prefixedHtml;
    }

    protected function resolvePageTitle(string $title, ?array $user = null): string
    {
        $schoolName = APP_NAME;
        $ecoleId = (int) ($user['ecole_id'] ?? 0);

        if ($ecoleId > 0) {
            $school = \App\Models\Ecole::findById($ecoleId);
            if (!empty($school['nom_etablissement'])) {
                $schoolName = $school['nom_etablissement'];
            }
        }

        if ($title === APP_NAME) {
            return $schoolName;
        }

        $prefix = APP_NAME . ' - ';
        if (strpos($title, $prefix) === 0) {
            $suffix = substr($title, strlen($prefix));
            return $schoolName . ($suffix !== '' ? ' - ' . $suffix : '');
        }

        return $title;
    }

    protected function redirect(string $path): void
    {
        $routeBaseUrl = Auth::getSchoolRouteBaseUrl() ?? BASE_URL;
        header('Location: ' . $routeBaseUrl . $path);
        exit;
    }

    protected function getModulesForRole(string $role): array
    {
        if ($role === 'super_admin') {
            $modules = [
                ['name' => 'Toutes les écoles', 'path' => '/ecoles', 'icon' => 'bi-buildings'],
                ['name' => 'Créer une école', 'path' => '/ecoles/create', 'icon' => 'bi-building-add'],
                ['name' => 'Demandes des écoles', 'path' => '/ecoles/requests', 'icon' => 'bi-inbox'],
                ['name' => 'Gestion des abonnements', 'path' => '/ecoles/subscriptions', 'icon' => 'bi-card-checklist'],
                ['name' => 'Accès aux communications', 'path' => '/ecoles/notificationAccess', 'icon' => 'bi-chat-square-dots'],
                ['name' => 'Tarifs des abonnements', 'path' => '/ecoles/plans', 'icon' => 'bi-tags'],
                ['name' => 'Attribuer un abonnement', 'path' => '/ecoles/addSubscription', 'icon' => 'bi-credit-card'],
                ['name' => 'Validation des comptes', 'path' => '/utilisateurs', 'icon' => 'bi-person-check'],
                ['name' => 'Activités', 'path' => '/activities', 'icon' => 'bi-activity'],
            ];
            return $modules;
        }

        $modules = match ($role) {
            'ecole_admin' => [
                ['name' => 'Paramètres école', 'path' => '/ecoles/settings', 'icon' => 'bi-gear'],
                ['name' => 'Accès utilisateurs', 'path' => '/utilisateurs', 'icon' => 'bi-person-check'],
                ['name' => 'Mon abonnement', 'path' => '/ecoles/subscriptions', 'icon' => 'bi-card-checklist'],
                ['name' => 'Autorisations paiements', 'path' => '/paiements/gestionAutorisations', 'icon' => 'bi-shield-check'],
                ['name' => 'Agents', 'path' => '/agents', 'icon' => 'bi-people-fill'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Exonérations', 'path' => '/exonerations', 'icon' => 'bi-shield-minus'],
                ['name' => 'Communiqués', 'path' => '/communiques', 'icon' => 'bi-megaphone'],
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
            'préfet_école' => [
                ['name' => 'Admissions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Classes', 'path' => '/classes', 'icon' => 'bi-houses'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Exonérations', 'path' => '/exonerations', 'icon' => 'bi-shield-minus'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Bulletins', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Évaluations', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
            ],
            'DP_école' => [
                ['name' => 'Admissions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Classes', 'path' => '/classes', 'icon' => 'bi-houses'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Exonérations', 'path' => '/exonerations', 'icon' => 'bi-shield-minus'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Bulletins', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Évaluations', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
                ['name' => 'Événements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
            ],
            'promoteur_école', 'DE_école', 'DD_école', 'DA_école' => [
                ['name' => 'Admissions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Utilisateurs', 'path' => '/utilisateurs', 'icon' => 'bi-person-check'],
                ['name' => 'Classes', 'path' => '/classes', 'icon' => 'bi-houses'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Dérogations', 'path' => '/derogations', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Exonérations', 'path' => '/exonerations', 'icon' => 'bi-shield-minus'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Bulletins', 'path' => '/bulletins', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Évaluations', 'path' => '/evaluations', 'icon' => 'bi-journal-check'],
                ['name' => 'Événements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
                ['name' => 'Générer mot de passe', 'path' => '/ecoles/generatePassword', 'icon' => 'bi-key'],
            ],
            'comptable_école' => [
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Facturation', 'path' => '/facturation', 'icon' => 'bi-receipt'],
                ['name' => 'Frais', 'path' => '/frais', 'icon' => 'bi-tag'],
                ['name' => 'Devises', 'path' => '/devises', 'icon' => 'bi-currency-exchange'],
                ['name' => 'Dérogations', 'path' => '/derogations', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Exonérations', 'path' => '/exonerations', 'icon' => 'bi-shield-minus'],
                ['name' => 'Recouvrements', 'path' => '/recouvrements', 'icon' => 'bi-cash-stack'],
                ['name' => 'Caisses & Banques', 'path' => '/comptes', 'icon' => 'bi-bank'],
                ['name' => 'Paiements', 'path' => '/paiements', 'icon' => 'bi-currency-dollar'],
                ['name' => 'Événements', 'path' => '/evenements', 'icon' => 'bi-calendar-event'],
                ['name' => 'Rapports', 'path' => '/rapports', 'icon' => 'bi-file-earmark-bar-graph'],
            ],
            'sec_école' => [
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
                ['name' => 'Communiqués', 'path' => '/communiques', 'icon' => 'bi-megaphone'],
                ['name' => 'Classes', 'path' => '/classes', 'icon' => 'bi-houses'],
                ['name' => 'Options', 'path' => '/options', 'icon' => 'bi-grid-1x2'],
                ['name' => 'Parents', 'path' => '/parents', 'icon' => 'bi-people'],
                ['name' => 'Élèves', 'path' => '/eleves', 'icon' => 'bi-person-badge'],
                ['name' => 'Dérogations', 'path' => '/derogations', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Exonérations', 'path' => '/exonerations', 'icon' => 'bi-shield-minus'],
                ['name' => 'Notes', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Présences', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Gérer les mots de passe', 'path' => '/inscriptions/managePasswords', 'icon' => 'bi-key'],
                ['name' => 'Rapports', 'path' => '/rapports', 'icon' => 'bi-file-earmark-bar-graph'],
            ],
            'agent_ecole' => [
                ['name' => 'Inscriptions', 'path' => '/inscriptions', 'icon' => 'bi-person-plus'],
            ],
            'enseignant_école' => [
                ['name' => 'Mon emploi du temps', 'path' => '/mon-emploi', 'icon' => 'bi-calendar2-week'],
                ['name' => 'Présences des élèves', 'path' => '/presences', 'icon' => 'bi-calendar-check'],
                ['name' => 'Notes de mes cours', 'path' => '/notes', 'icon' => 'bi-pencil-square'],
                ['name' => 'Élèves inscrits', 'path' => '/eleves', 'icon' => 'bi-people'],
            ],
            'eleve_ecole', 'eleve_école' => [
                ['name' => 'Mon emploi du temps', 'path' => '/mon-emploi', 'icon' => 'bi-calendar2-week'],
                ['name' => 'Mes notes', 'path' => '/mes-notes', 'icon' => 'bi-bar-chart-steps'],
                ['name' => 'Mes paiements', 'path' => '/mes-paiements', 'icon' => 'bi-wallet2'],
                ['name' => 'Mon dossier disciplinaire', 'path' => '/espace-eleve/discipline', 'icon' => 'bi-journal-text'],
                ['name' => 'Mes dérogations', 'path' => '/espace-eleve/derogations', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Mes cours', 'path' => '/espace-eleve/cours', 'icon' => 'bi-book'],
                ['name' => 'Mes évaluations', 'path' => '/espace-eleve/evaluations', 'icon' => 'bi-journal-check'],
                ['name' => 'Mes communiqués', 'path' => '/espace-eleve/communiques', 'icon' => 'bi-megaphone'],
                ['name' => 'Mes présences', 'path' => '/espace-eleve/presences', 'icon' => 'bi-check2-square'],
            ],
            'parent_ecole', 'parent_école' => [
                ['name' => 'Enfants', 'path' => '/mes-enfants', 'icon' => 'bi-people'],
                ['name' => 'Notes et discipline', 'path' => '/mes-enfants', 'icon' => 'bi-journal-check'],
                ['name' => 'Paiements', 'path' => '/paiements-enfant', 'icon' => 'bi-currency-dollar'],
                ['name' => 'Dérogations', 'path' => '/derogations', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Messages', 'path' => '/messages', 'icon' => 'bi-chat-dots'],
            ],
            default => [
                ['name' => 'Accueil', 'path' => '/dashboard', 'icon' => 'bi-house'],
            ],
        };

        if (in_array($role, [
            'ecole_admin',
            'promoteur_école',
            'préfet_école',
            'DE_école',
            'DD_école',
            'DP_école',
            'DA_école',
            'comptable_école',
            'sec_école',
            'parent_ecole',
            'parent_école',
        ], true)) {
            array_unshift($modules, [
                'name' => 'Vérifier solvabilité',
                'path' => '/paiements/verifierSolvabilite',
                'icon' => 'bi-qr-code-scan',
            ]);
        }

        if (in_array($role, ['préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école'], true)) {
            array_unshift($modules, ['name' => 'Communiqués', 'path' => '/communiques', 'icon' => 'bi-megaphone']);
        }
        if (in_array($role, ['DE_école', 'DP_école', 'DA_école'], true)) {
            array_unshift($modules, [
                'name' => $role === 'DP_école' ? 'Enseignants et horaires' : 'Gestion des horaires',
                'path' => '/horaires',
                'icon' => 'bi-calendar-week',
            ]);
        }

        $user = Auth::user();
        if (!$user || ($user['role'] ?? '') !== $role) {
            return $modules;
        }

        return array_values(array_filter($modules, static function (array $module) use ($user): bool {
            if (
                ($module['path'] ?? '') === '/messages'
                && !\App\Models\NotificationAccess::isDirectMessagingEnabled((int) ($user['ecole_id'] ?? 0))
            ) {
                return false;
            }
            $permissionModule = match ($module['path'] ?? '') {
                '/inscriptions' => 'inscriptions',
                '/notes' => 'notes',
                default => null,
            };

            return $permissionModule === null
                || \App\Models\AdministrationAutorisation::canAccessModule($user, $permissionModule);
        }));
    }

    protected function requireAdministrationModuleAccess(array $user, string $module): void
    {
        if (($user['role'] ?? '') === 'super_admin') {
            return;
        }

        if (!\App\Models\AdministrationAutorisation::canAccessModule($user, $module)) {
            $this->redirect('/error/accessDenied');
        }
    }

    protected function getSectionScopeForUser(array $user): ?int
    {
        $role = (string) ($user['role'] ?? '');
        if (in_array($role, ['préfet_école', 'DE_école', 'DD_école'], true)) {
            $sectionId = (int) ($user['section_id'] ?? 0);
            $section = $sectionId > 0 ? \App\Models\Section::findById($sectionId) : null;
            $sectionName = mb_strtolower(trim((string) ($section['nom_section'] ?? '')), 'UTF-8');
            if ($sectionName === 'secondaire') {
                return $sectionId;
            }

            return \App\Models\User::getDefaultSectionIdForRole($role) ?? 0;
        }

        if ($role === 'DP_école') {
            $sectionId = (int) ($user['section_id'] ?? 0);
            $section = $sectionId > 0 ? \App\Models\Section::findById($sectionId) : null;
            $sectionName = mb_strtolower(trim((string) ($section['nom_section'] ?? '')), 'UTF-8');
            if ($sectionName === 'primaire') {
                return $sectionId;
            }

            return \App\Models\User::getDefaultSectionIdForRole($role) ?? 0;
        }

        if ($role === 'DA_école') {
            $sectionId = (int) ($user['section_id'] ?? 0);
            $section = $sectionId > 0 ? \App\Models\Section::findById($sectionId) : null;
            $sectionName = mb_strtolower(trim((string) ($section['nom_section'] ?? '')), 'UTF-8');
            if (in_array($sectionName, ['primaire', 'maternelle'], true)) {
                return $sectionId;
            }

            return \App\Models\User::getDefaultSectionIdForRole($role) ?? 0;
        }

        return null;
    }
}
