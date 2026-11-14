<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ecole;
use App\Models\User;

class ManuelController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::refresh() ?: Auth::user();
        if ($user === null) {
            $this->redirect('/login');
            return;
        }

        $role = (string) ($user['role'] ?? 'default');
        $schoolId = (int) ($user['ecole_id'] ?? 0);
        $school = $schoolId > 0 ? Ecole::findById($schoolId) : null;
        $this->view('manuel/index', [
            'title' => 'Manuel guide - ' . User::getRoleLabel($role),
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'roleAccessSummary' => $this->getRoleAccessSummary($role),
            'schoolName' => (string) ($school['nom_etablissement'] ?? APP_NAME),
            'modules' => $this->getModulesForRole($role),
            'routeBaseUrl' => Auth::getSchoolRouteBaseUrl() ?? BASE_URL,
            'generatedAt' => date('d/m/Y'),
            'documentReference' => 'KC-SMA/MAN-UTILISATEUR',
            'guides' => self::moduleGuides(),
        ]);
    }

    private function getRoleAccessSummary(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Accès à la plateforme : établissements, demandes, abonnements, comptes et historique.',
            'ecole_admin' => 'Accès à la gestion de l’établissement, dans les limites des autorisations et paramètres actifs.',
            'préfet_école', 'DE_école', 'DD_école', 'DP_école', 'DA_école', 'promoteur_école' =>
                'Accès aux activités de direction et de suivi scolaire. Le périmètre peut être limité à une section.',
            'comptable_école' => 'Accès aux inscriptions et opérations financières : frais, factures, paiements, paie des agents et rapports.',
            'sec_école' => 'Accès au suivi administratif des élèves, inscriptions, présences et documents autorisés.',
            'agent_ecole', 'agent_école' => 'Accès au traitement des inscriptions qui vous sont confiées.',
            'enseignant_école' => 'Accès à vos cours, aux élèves concernés, à leurs présences et à la saisie des notes autorisée.',
            'eleve_ecole', 'eleve_école' => 'Consultation de vos propres informations scolaires et financières.',
            'parent_ecole', 'parent_école' => 'Consultation des informations des enfants rattachés à votre compte et échanges autorisés.',
            default => 'Votre rôle et les autorisations attribuées déterminent les informations et opérations accessibles.',
        };
    }

    private static function moduleGuides(): array
    {
        return [
            '/dashboard' => ['purpose' => 'Consulter les indicateurs et accéder rapidement aux espaces du compte.', 'steps' => 'Ouvrez une carte ou un lien du tableau de bord. Utilisez le menu latéral pour revenir à un module.'],
            '/ecoles' => ['purpose' => 'Consulter et administrer les établissements de la plateforme.', 'steps' => 'Recherchez l’établissement, ouvrez sa fiche, puis utilisez les actions disponibles.'],
            '/ecoles/create' => ['purpose' => 'Créer un établissement et ses informations de base.', 'steps' => 'Renseignez les informations obligatoires, vérifiez-les, puis enregistrez le formulaire.'],
            '/ecoles/requests' => ['purpose' => 'Traiter les demandes transmises par les écoles.', 'steps' => 'Lisez la demande et sa justification, puis choisissez Approuver ou Refuser et confirmez.'],
            '/ecoles/subscriptions' => ['purpose' => 'Consulter et gérer les abonnements des établissements.', 'steps' => 'Sélectionnez une école ou une demande, vérifiez les dates et le plan, puis appliquez l’action proposée.'],
            '/ecoles/notificationAccess' => ['purpose' => 'Régler les communications et notifications autorisées pour une école.', 'steps' => 'Choisissez l’école, ajustez l’autorisation puis enregistrez.'],
            '/ecoles/plans' => ['purpose' => 'Gérer les offres et tarifs d’abonnement.', 'steps' => 'Consultez les plans puis utilisez les actions de création ou modification affichées.'],
            '/ecoles/addSubscription' => ['purpose' => 'Attribuer un abonnement à un établissement.', 'steps' => 'Sélectionnez l’école et le plan, contrôlez la durée, puis confirmez.'],
            '/utilisateurs' => ['purpose' => 'Gérer les comptes, leurs rôles et leur activation.', 'steps' => ['Recherchez le compte par nom ou identifiant.', 'Vérifiez la personne et le rôle attribué.', 'Modifiez uniquement les éléments nécessaires, puis enregistrez.']],
            '/activities' => ['purpose' => 'Consulter l’historique des activités enregistrées.', 'steps' => 'Filtrez la liste pour retrouver l’utilisateur, l’action ou la période recherchée.'],
            '/ecoles/settings' => ['purpose' => 'Mettre à jour les paramètres et l’année scolaire de l’école.', 'steps' => 'Vérifiez les coordonnées, enregistrez les changements et, si nécessaire, transmettez une demande de changement d’année au super-administrateur.'],
            '/agents' => ['purpose' => 'Gérer les agents et leurs affectations.', 'steps' => 'Créez ou recherchez un agent, vérifiez ses informations et enregistrez son affectation.'],
            '/parents' => ['purpose' => 'Consulter et gérer les parents ou tuteurs.', 'steps' => 'Recherchez un parent, ouvrez sa fiche pour vérifier ses coordonnées et ses enfants liés.'],
            '/eleves' => ['purpose' => 'Consulter les dossiers des élèves et leurs informations scolaires.', 'steps' => 'Filtrez par classe ou matricule, puis ouvrez le dossier de l’élève choisi.'],
            '/inscriptions' => ['purpose' => 'Créer et suivre les inscriptions, validations et réinscriptions annuelles.', 'steps' => ['Recherchez l’élève ou démarrez une nouvelle inscription.', 'Vérifiez l’identité, l’année, la classe, l’option et les pièces demandées.', 'Pour une réinscription, choisissez passage ou redoublement et contrôlez la nouvelle classe avant validation.']],
            '/inscriptions/managePasswords' => ['purpose' => 'Générer et gérer les accès des élèves et de leurs parents.', 'steps' => ['Recherchez l’élève ou le parent concerné.', 'Générez ou renvoyez l’accès temporaire.', 'Remettez-le confidentiellement : le premier accès demande un nouveau mot de passe.']],
            '/options' => ['purpose' => 'Gérer les options rattachées aux sections et classes.', 'steps' => 'Ajoutez ou modifiez une option, vérifiez son rattachement à la section puis enregistrez.'],
            '/classes' => ['purpose' => 'Gérer les classes et leurs rattachements.', 'steps' => 'Créez ou ouvrez une classe, renseignez section et option si nécessaire, puis enregistrez.'],
            '/exonerations' => ['purpose' => 'Consulter et traiter les exonérations de frais.', 'steps' => 'Ouvrez la demande, vérifiez l’élève, le frais et le motif, puis appliquez la décision autorisée.'],
            '/communiques' => ['purpose' => 'Publier ou consulter les communications scolaires.', 'steps' => 'Créez un communiqué, vérifiez son contenu et ses destinataires, puis publiez-le.'],
            '/paiements' => ['purpose' => 'Enregistrer et suivre les paiements et leurs reçus.', 'steps' => ['Recherchez l’élève et sélectionnez le frais concerné.', 'Saisissez le montant réellement reçu et vérifiez la référence.', 'Validez, puis imprimez le reçu. En cas d’erreur, utilisez uniquement une correction autorisée.']],
            '/paiements/gestionAutorisations' => ['purpose' => 'Gérer les autorisations relatives aux opérations de paiement.', 'steps' => 'Vérifiez l’utilisateur et le périmètre, modifiez les droits nécessaires puis enregistrez.'],
            '/paiements/verifierSolvabilite' => ['purpose' => 'Vérifier la solvabilité à partir des informations du dossier ou du reçu.', 'steps' => 'Saisissez ou scannez la référence demandée, vérifiez l’identité affichée et consultez le résultat.'],
            '/ecoles/generatePassword' => ['purpose' => 'Générer un accès temporaire pour un compte scolaire.', 'steps' => 'Sélectionnez le compte, générez le mot de passe puis remettez-le confidentiellement à son propriétaire.'],
            '/notes' => ['purpose' => 'Saisir et consulter les résultats scolaires autorisés.', 'steps' => ['Choisissez la classe, le cours et l’évaluation.', 'Saisissez les notes dans les lignes des élèves concernés.', 'Relisez les valeurs, puis enregistrez.']],
            '/bulletins' => ['purpose' => 'Consulter ou produire les bulletins scolaires.', 'steps' => ['Choisissez la classe ou l’élève, puis la période.', 'Vérifiez le bulletin affiché avant de l’imprimer ou de l’exporter.']],
            '/presences' => ['purpose' => 'Consigner et consulter les présences des élèves.', 'steps' => ['Choisissez la classe et la date du cours.', 'Indiquez le statut de chaque élève.', 'Contrôlez la liste et enregistrez.']],
            '/evaluations' => ['purpose' => 'Créer et gérer les évaluations scolaires.', 'steps' => ['Choisissez la classe, le cours et la période.', 'Saisissez le titre et les informations demandées.', 'Vérifiez les dates, puis enregistrez.']],
            '/evenements' => ['purpose' => 'Créer et consulter les événements scolaires.', 'steps' => 'Renseignez titre, date et détails, sélectionnez les destinataires si proposés puis publiez.'],
            '/facturation' => ['purpose' => 'Consulter les éléments de facturation disponibles.', 'steps' => 'Choisissez la période ou l’élément puis vérifiez les montants avant toute validation.'],
            '/comptes' => ['purpose' => 'Consulter les soldes et suivre les sorties de caisse ou de banque.', 'steps' => ['Le comptable soumet les sorties avec un motif et un compte.', 'La sortie et le paiement de salaire restent en attente jusqu’à l’approbation par un rôle désigné dans les paramètres de l’école.', 'Le compte est débité uniquement après approbation. La personne qui soumet une sortie ne peut pas l’approuver elle-même.']],
            '/rapports' => ['purpose' => 'Produire les rapports disponibles pour votre rôle.', 'steps' => 'Choisissez les filtres, la période et le format, puis lancez l’affichage ou l’export.'],
            '/statistiques' => ['purpose' => 'Analyser les indicateurs agrégés de la plateforme ou de l’école.', 'steps' => 'Sélectionnez les filtres proposés et comparez les indicateurs affichés.'],
            '/mes-cours' => ['purpose' => 'Consulter les cours associés au compte.', 'steps' => 'Choisissez un cours pour voir les informations ou ressources disponibles.'],
            '/mon-emploi' => ['purpose' => 'Consulter l’emploi du temps personnel ou scolaire.', 'steps' => 'Choisissez la période ou la classe proposée, puis consultez les horaires.'],
            '/mes-notes' => ['purpose' => 'Consulter les notes personnelles.', 'steps' => 'Choisissez une période si nécessaire et ouvrez le détail du résultat.'],
            '/mes-presences' => ['purpose' => 'Consulter l’historique des présences personnelles.', 'steps' => 'Filtrez par période pour afficher les absences et présences enregistrées.'],
            '/mes-paiements' => ['purpose' => 'Suivre les paiements et télécharger les reçus personnels.', 'steps' => 'Ouvrez un paiement pour consulter son détail et son reçu. Signalez toute anomalie au service compétent.'],
            '/mes-enfants' => ['purpose' => 'Suivre les informations des enfants liés au compte parent.', 'steps' => 'Sélectionnez l’enfant puis consultez ses notes, présences, discipline et informations disponibles.'],
            '/bulletins-enfant' => ['purpose' => 'Consulter les bulletins des enfants associés.', 'steps' => 'Sélectionnez l’enfant et la période, puis ouvrez ou téléchargez le bulletin proposé.'],
            '/paiements-enfant' => ['purpose' => 'Suivre les paiements des enfants et consulter les reçus.', 'steps' => 'Sélectionnez un enfant et un paiement pour voir le montant enregistré et son reçu.'],
            '/derogations' => ['purpose' => 'Consulter les dérogations ou décisions accessibles à votre rôle.', 'steps' => 'Ouvrez le dossier concerné, vérifiez le motif et la période puis soumettez ou consultez la décision autorisée.'],
            '/espace-eleve/discipline' => ['purpose' => 'Consulter le dossier disciplinaire personnel.', 'steps' => 'Parcourez les éléments affichés et contactez l’administration pour toute demande de rectification.'],
            '/espace-eleve/derogations' => ['purpose' => 'Consulter les dérogations accordées ou demandes personnelles.', 'steps' => 'Ouvrez une demande pour lire son statut et les détails associés.'],
            '/espace-eleve/cours' => ['purpose' => 'Consulter les cours et ressources accessibles à l’élève.', 'steps' => 'Choisissez un cours pour consulter ses informations.'],
            '/espace-eleve/evaluations' => ['purpose' => 'Consulter les évaluations prévues ou publiées.', 'steps' => 'Parcourez les évaluations associées à la classe et aux cours.'],
            '/espace-eleve/communiques' => ['purpose' => 'Lire les communiqués adressés à l’élève.', 'steps' => 'Ouvrez un communiqué pour lire son contenu et ses dates.'],
            '/messages' => ['purpose' => 'Consulter les messages et conversations auxquels vous avez accès.', 'steps' => 'Ouvrez une conversation pour répondre. Vérifiez le destinataire avant l’envoi.'],
            '/horaires' => ['purpose' => 'Gérer ou consulter les horaires et affectations de cours.', 'steps' => 'Choisissez classe, cours et période, vérifiez les conflits puis enregistrez les affectations.'],
            '/frais' => ['purpose' => 'Créer et gérer les frais et leurs paramètres.', 'steps' => 'Saisissez l’intitulé et le montant, contrôlez le ciblage de l’année, de la classe ou de l’option puis enregistrez.'],
            '/devises' => ['purpose' => 'Gérer les devises et conversions applicables.', 'steps' => 'Vérifiez le code et le taux avant d’enregistrer une devise ou une conversion.'],
            '/recouvrements' => ['purpose' => 'Suivre les soldes restant dus et les opérations de recouvrement.', 'steps' => 'Filtrez par classe ou élève, vérifiez les montants et consultez les actions disponibles.'],
            '/paies-agents' => ['purpose' => 'Gérer les salaires de base et les fiches mensuelles des agents.', 'steps' => ['Définissez pour chaque agent le salaire de base et sa devise.', 'Préparez la fiche du mois en ajoutant les primes et retenues applicables.', 'Soumettez le paiement à l’approbation. Le compte de même devise ne sera débité qu’après la validation d’un utilisateur ayant un rôle désigné.']],
            '/contact' => ['purpose' => 'Contacter le support de la plateforme.', 'steps' => 'Décrivez le problème, indiquez la page concernée sans transmettre de mot de passe, puis envoyez le message.'],
        ];
    }
}
