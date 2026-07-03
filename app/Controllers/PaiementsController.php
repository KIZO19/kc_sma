<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Eleve;
use App\Models\User;

class PaiementsController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'ecole_admin', 'comptable_école', 'sec_école', 'parent_ecole']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $this->view('pages/page', [
            'title' => APP_NAME . ' - Paiements',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'pageTitle' => 'Paiements',
            'pageDescription' => 'Gérez les paiements et les reçus des élèves.',
            'pageContent' => 'Suivi des paiements scolaires, des plans de recouvrement et des écritures de trésorerie.',
            'pageNotes' => 'Page accessible aux services financiers et à l’administration. Le comptable planifie les recouvrements et gère les paiements.',
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $role = $user['role'] ?? 'default';
        $modules = $this->getModulesForRole($role);

        $eleveId = (int) ($_GET['eleve_id'] ?? $_GET['id'] ?? 0);
        if ($eleveId <= 0) {
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $eleve = Eleve::findById($eleveId);
        if (!$eleve) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $compte = Eleve::getAccount($eleveId);

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM caisses_banques WHERE ecole_id = :ecole_id OR ecole_id IS NULL');
        $stmt->execute([':ecole_id' => $user['ecole_id'] ?? 0]);
        $caisses = $stmt->fetchAll();

        $this->view('paiements/create', [
            'title' => APP_NAME . ' - Enregistrer paiement',
            'user' => $user,
            'role' => $role,
            'roleLabel' => User::getRoleLabel($role),
            'modules' => $modules,
            'eleve' => $eleve,
            'compte' => $compte,
            'caisses' => $caisses,
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école']);

        $user = Auth::refresh() ?: Auth::user();
        $agentId = $user['reference_id'] ?? null;

        if (!$agentId) {
            $_SESSION['flash_error'] = 'Impossible d\'identifier l\'agent en cours. Assurez-vous d\'être connecté en tant qu\'agent.';
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $eleveId = (int) ($_POST['eleve_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $libelle = trim($_POST['libelle'] ?? 'Paiement élève');
        $caisseId = !empty($_POST['caisse_id']) ? (int) $_POST['caisse_id'] : null;

        if ($eleveId <= 0 || $montant <= 0) {
            $_SESSION['flash_error'] = 'Élève ou montant invalide.';
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $db = Database::getConnection();

        // Ensure compte exists
        $compte = Eleve::getAccount($eleveId);
        if (!$compte) {
            // Create a compte (use latest active school year if available)
            $stmtYear = $db->prepare('SELECT id FROM annees_scolaires WHERE est_active = 1 AND ecole_id = :ecole_id LIMIT 1');
            $stmtYear->execute([':ecole_id' => $user['ecole_id'] ?? 0]);
            $year = $stmtYear->fetch();
            $anneeId = $year['id'] ?? 1;

            $ins = $db->prepare('INSERT INTO comptes_eleves (eleve_id, annee_scolaire_id, solde_debiteur) VALUES (:eleve, :annee, 0)');
            $ins->execute([':eleve' => $eleveId, ':annee' => $anneeId]);
            $compteId = (int) $db->lastInsertId();
        } else {
            $compteId = (int) $compte['id'];
        }

        $reference = 'REC-' . date('YmdHis') . '-' . random_int(100, 999);

        $stmt = $db->prepare('INSERT INTO ecritures_comptables_eleves (compte_eleve_id, frais_id, caisse_banque_id, type_mouvement, montant, reference_recu, libelle, agent_saisie_id) VALUES (:compte, NULL, :caisse, :type, :montant, :ref, :libelle, :agent)');
        $stmt->execute([
            ':compte' => $compteId,
            ':caisse' => $caisseId,
            ':type' => 'CREDIT',
            ':montant' => $montant,
            ':ref' => $reference,
            ':libelle' => $libelle,
            ':agent' => $agentId,
        ]);

        $ecritureId = (int) $db->lastInsertId();

        // Update compte solde_debiteur (subtract payment)
        $upd = $db->prepare('UPDATE comptes_eleves SET solde_debiteur = solde_debiteur - :montant WHERE id = :id');
        $upd->execute([':montant' => $montant, ':id' => $compteId]);

        header('Location: ' . BASE_URL . '/paiements/receipt?id=' . $ecritureId);
        exit;
    }

    public function receipt(): void
    {
        Auth::requireAuth();
        Auth::requireRoles(['super_admin', 'comptable_école', 'sec_école']);

        $user = Auth::refresh() ?: Auth::user();
        $ecritureId = (int) ($_GET['id'] ?? 0);
        if ($ecritureId <= 0) {
            header('Location: ' . BASE_URL . '/paiements');
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT ece.*, ce.eleve_id, el.nom, el.postnom, el.prenom, cb.nom_compte AS caisse_name FROM ecritures_comptables_eleves ece INNER JOIN comptes_eleves ce ON ece.compte_eleve_id = ce.id INNER JOIN eleves el ON ce.eleve_id = el.id LEFT JOIN caisses_banques cb ON ece.caisse_banque_id = cb.id WHERE ece.id = :id LIMIT 1');
        $stmt->execute([':id' => $ecritureId]);
        $data = $stmt->fetch();

        if (!$data) {
            header('Location: ' . BASE_URL . '/error/notFound');
            exit;
        }

        $this->view('paiements/receipt', [
            'title' => APP_NAME . ' - Reçu paiement',
            'user' => $user,
            'role' => $user['role'] ?? 'default',
            'roleLabel' => User::getRoleLabel($user['role'] ?? 'default'),
            'modules' => $this->getModulesForRole($user['role'] ?? 'default'),
            'ecriture' => $data,
        ]);
    }
}
