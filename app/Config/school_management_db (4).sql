-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 08:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `abonnements_ecoles`
--

CREATE TABLE `abonnements_ecoles` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `statut_abonnement` enum('Actif','Expire','Resilie') DEFAULT 'Actif',
  `montant_paye` decimal(10,2) NOT NULL,
  `reference_paiement` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `postnom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `salaire_de_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `est_enseignant_secondaire` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `ecole_id`, `nom`, `postnom`, `prenom`, `telephone`, `email`, `mot_de_passe`, `role_id`, `salaire_de_base`, `est_enseignant_secondaire`) VALUES
(1, 1, 'Kakule', 'Vianney', 'Jean', '+243990000001', NULL, NULL, 1, 500.00, 0),
(2, 1, 'Kavugho', 'Sifa', 'Anny', '+243990000002', NULL, NULL, 2, 450.00, 0),
(3, 1, 'Luvualu', 'Kizombo', 'Joseph', '+243990000003', NULL, NULL, 5, 400.00, 0),
(4, 1, 'Kambale', 'Mwisa', 'Pascal', '+243990000004', NULL, NULL, 7, 350.00, 1),
(5, 1, 'Mumbere', 'Kitsali', 'Claude', '+243990000005', NULL, NULL, 7, 350.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `annees_scolaires`
--

CREATE TABLE `annees_scolaires` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `annee` varchar(20) NOT NULL,
  `est_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `annees_scolaires`
--

INSERT INTO `annees_scolaires` (`id`, `ecole_id`, `annee`, `est_active`) VALUES
(1, 1, '2025-2026', 1);

-- --------------------------------------------------------

--
-- Table structure for table `attribution_cours`
--

CREATE TABLE `attribution_cours` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `volume_horaire_hebdo` int(11) NOT NULL DEFAULT 2 COMMENT 'Nombre d''heures ou périodes par semaine',
  `max_evaluation` int(11) NOT NULL DEFAULT 10,
  `max_examen` int(11) NOT NULL DEFAULT 40
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attribution_cours`
--

INSERT INTO `attribution_cours` (`id`, `ecole_id`, `classe_id`, `cours_id`, `agent_id`, `annee_scolaire_id`, `volume_horaire_hebdo`, `max_evaluation`, `max_examen`) VALUES
(3, 1, 3, 1, 4, 1, 5, 10, 40),
(4, 1, 3, 2, 5, 1, 2, 10, 40);

-- --------------------------------------------------------

--
-- Table structure for table `caisses_banques`
--

CREATE TABLE `caisses_banques` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `nom_compte` varchar(100) NOT NULL,
  `type_compte` enum('Caisse','Banque','Mobile Money') NOT NULL,
  `solde_actuel` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `caisses_banques`
--

INSERT INTO `caisses_banques` (`id`, `ecole_id`, `nom_compte`, `type_compte`, `solde_actuel`) VALUES
(1, 1, 'Caisse Secrétariat Principal', 'Caisse', 2500.00),
(2, 1, 'Compte Rawbank Établissement', 'Banque', 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories_evenements`
--

CREATE TABLE `categories_evenements` (
  `id` int(11) NOT NULL,
  `nom_categorie` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories_evenements`
--

INSERT INTO `categories_evenements` (`id`, `nom_categorie`) VALUES
(1, 'Réunion Administrative'),
(2, 'Assemblée Générale des Enseignants'),
(3, 'Journée Pédagogique Spéciale');

-- --------------------------------------------------------

--
-- Table structure for table `child_requests`
--

CREATE TABLE `child_requests` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `postnom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `genre` enum('Masculin','Féminin','Autre') NOT NULL,
  `date_naissance` date NOT NULL,
  `statut` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `child_requests`
--

INSERT INTO `child_requests` (`id`, `parent_id`, `nom`, `postnom`, `prenom`, `genre`, `date_naissance`, `statut`, `created_at`, `updated_at`) VALUES
(1, 4, 'Joseph', 'Kizo', 'Lael', '', '2026-06-19', 'pending', '2026-06-28 00:18:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `nom_classe` varchar(100) NOT NULL,
  `section_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `ecole_id`, `nom_classe`, `section_id`, `option_id`) VALUES
(1, 1, '3ème Maternelle', 1, NULL),
(2, 1, '6ème Année Primaire', 2, NULL),
(3, 1, '3ème Technique Informatique', 3, 1),
(4, 1, '4ème Commerciale', 3, 2),
(5, 1, '1ère', 2, 2),
(6, 4, '1', 2, NULL),
(7, 4, '7ème', 3, 6);

-- --------------------------------------------------------

--
-- Table structure for table `comptes_eleves`
--

CREATE TABLE `comptes_eleves` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `solde_debiteur` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comptes_eleves`
--

INSERT INTO `comptes_eleves` (`id`, `eleve_id`, `annee_scolaire_id`, `solde_debiteur`) VALUES
(1, 2, 1, 50.00),
(2, 3, 1, 0.00),
(3, 6, 1, 1.00),
(4, 1, 1, -1.00),
(5, 9, 1, -24.00);

-- --------------------------------------------------------

--
-- Table structure for table `cours`
--

CREATE TABLE `cours` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `nom_cours` varchar(100) NOT NULL,
  `code_cours` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cours`
--

INSERT INTO `cours` (`id`, `ecole_id`, `nom_cours`, `code_cours`) VALUES
(1, 1, 'Mathématiques', 'MATH'),
(2, 1, 'Informatique Générale', 'INFO-GEN'),
(3, 1, 'Français', 'FRAN');

-- --------------------------------------------------------

--
-- Table structure for table `dettes_eleves`
--

CREATE TABLE `dettes_eleves` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `frais_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `montant_initial` decimal(10,2) NOT NULL DEFAULT 0.00,
  `montant_restant` decimal(10,2) NOT NULL DEFAULT 0.00,
  `devise` varchar(5) NOT NULL DEFAULT 'USD',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devises`
--

CREATE TABLE `devises` (
  `id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `taux` decimal(14,6) NOT NULL DEFAULT 1.000000,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `devises`
--

INSERT INTO `devises` (`id`, `code`, `libelle`, `taux`, `actif`, `date_creation`) VALUES
(1, 'USD', 'Dollar américain', 1.000000, 1, '2026-07-04 22:33:35'),
(2, 'EUR', 'Euro', 1.100000, 1, '2026-07-04 22:33:35'),
(3, 'CDF', 'Franc congolais', 2500.000000, 1, '2026-07-04 22:33:35'),
(4, 'XAF', 'Franc CFA BEAC', 0.001700, 1, '2026-07-04 22:33:35'),
(5, 'XOF', 'Franc CFA BCEAO', 0.001700, 1, '2026-07-04 22:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `discipline_eleves`
--

CREATE TABLE `discipline_eleves` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `faute` varchar(255) NOT NULL,
  `sanction` varchar(255) DEFAULT NULL,
  `retrait_points` int(11) DEFAULT 0,
  `date_evenement` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discipline_eleves`
--

INSERT INTO `discipline_eleves` (`id`, `eleve_id`, `periode_id`, `faute`, `sanction`, `retrait_points`, `date_evenement`) VALUES
(1, 2, 1, 'Absence injustifiée au laboratoire', 'Retenu le samedi après-midi', 2, '2026-06-12');

-- --------------------------------------------------------

--
-- Table structure for table `ecoles`
--

CREATE TABLE `ecoles` (
  `id` int(11) NOT NULL,
  `nom_etablissement` varchar(150) NOT NULL,
  `adresse` text DEFAULT NULL,
  `telephone_contact` varchar(20) NOT NULL,
  `email_officiel` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `date_creation_compte` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut_systeme` enum('Actif','Suspendu','En_Attente') DEFAULT 'En_Attente',
  `code_antenne` varchar(50) DEFAULT NULL COMMENT 'Code Antenne SERNIE/MINEPST',
  `code_ecole` varchar(50) DEFAULT NULL COMMENT 'Code Unique de l''Établissement',
  `province_education` varchar(100) DEFAULT NULL COMMENT 'Ex: Nord-Kivu 1',
  `devise_principale` varchar(5) DEFAULT 'USD',
  `matricule` varchar(100) DEFAULT NULL COMMENT 'Numéro matricule unique de l''école',
  `logo_url` varchar(255) DEFAULT NULL COMMENT 'Chemin vers le logo de l''école',
  `admin_ecole_id` int(11) DEFAULT NULL,
  `identifiant` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecoles`
--

INSERT INTO `ecoles` (`id`, `nom_etablissement`, `adresse`, `telephone_contact`, `email_officiel`, `mot_de_passe`, `date_creation_compte`, `statut_systeme`, `code_antenne`, `code_ecole`, `province_education`, `devise_principale`, `matricule`, `logo_url`, `admin_ecole_id`, `identifiant`) VALUES
(1, 'École Pilote Test', '', '+243990000000', 'contact@ecole-pilote.com', '123456', '2026-06-23 16:29:21', 'Actif', NULL, NULL, NULL, 'USD', 'LZHEG', '/uploads/school_logos/school_1785088070_3eae7916ee.png', NULL, NULL),
(3, 'École Exemple', 'Rue de Test 1', '+243990000000', 'contact@ecole-exemple.com', 'ecole123', '2026-06-23 22:07:16', 'Actif', NULL, 'CODE123', 'Kinshasa', 'USD', NULL, NULL, NULL, NULL),
(4, 'Complexe Scolaire Emmanuel 1', 'Goma, Nord-Kivu, RDC', '+243000000000', 'contact@cs-emmanuel1.com', 'Pass123*', '2026-06-27 21:05:07', 'Actif', NULL, NULL, NULL, 'USD', 'XDW2K', '/uploads/school_logos/school_1783242344_e872f66f5a.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ecritures_comptables_eleves`
--

CREATE TABLE `ecritures_comptables_eleves` (
  `id` int(11) NOT NULL,
  `compte_eleve_id` int(11) NOT NULL,
  `frais_id` int(11) DEFAULT NULL,
  `caisse_banque_id` int(11) DEFAULT NULL,
  `type_mouvement` enum('DEBIT','CREDIT') NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `reference_recu` varchar(50) DEFAULT NULL,
  `date_operation` timestamp NOT NULL DEFAULT current_timestamp(),
  `libelle` varchar(255) NOT NULL,
  `agent_saisie_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecritures_comptables_eleves`
--

INSERT INTO `ecritures_comptables_eleves` (`id`, `compte_eleve_id`, `frais_id`, `caisse_banque_id`, `type_mouvement`, `montant`, `reference_recu`, `date_operation`, `libelle`, `agent_saisie_id`) VALUES
(1, 1, 1, NULL, 'DEBIT', 150.00, NULL, '2026-06-23 15:03:23', 'Facturation Minerval 1er Trimestre', 3),
(2, 1, 1, 1, 'CREDIT', 100.00, 'REC-2026-001', '2026-06-23 15:03:23', 'Acompte Minerval perçu en espèces', 3),
(4, 3, 4, NULL, 'DEBIT', 1.00, NULL, '2026-07-04 23:30:18', 'Facturation inscription: FRAIS DE CONSTRUCTION', 1),
(6, 4, 4, NULL, 'CREDIT', 1.00, 'REC-20260705020036-706', '2026-07-05 00:00:36', 'FRAIS DE CONSTRUCTION - 1.00 USD', 1),
(7, 5, 2, NULL, 'CREDIT', 1.00, 'REC-20260705123817-645', '2026-07-05 10:38:17', 'Frais technique - 45.00 USD', 1),
(8, 5, 2, NULL, 'CREDIT', 23.00, 'REC-20260705214321-691', '2026-07-05 19:43:21', 'Frais technique - 45.00 USD', 1);

-- --------------------------------------------------------

--
-- Table structure for table `eleves`
--

CREATE TABLE `eleves` (
  `id` int(11) NOT NULL,
  `matricule` varchar(50) DEFAULT NULL COMMENT 'Numéro matricule unique de l''élève',
  `nom` varchar(50) NOT NULL,
  `postnom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `genre` char(1) NOT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `nationalite` varchar(50) DEFAULT 'CONGOLAISE',
  `adresse` text DEFAULT NULL,
  `date_naissance` date NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `nom_pere` varchar(100) DEFAULT NULL,
  `nom_mere` varchar(100) DEFAULT NULL,
  `province_origine` varchar(100) DEFAULT NULL,
  `territoire` varchar(100) DEFAULT NULL,
  `secteur` varchar(100) DEFAULT NULL,
  `groupement` varchar(100) DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `num_permanent` varchar(50) DEFAULT NULL COMMENT 'Numéro permanent de l''élève (MINEDUB)',
  `photo` varchar(255) DEFAULT NULL,
  `statut_eleve` enum('actif','inactif') DEFAULT 'actif',
  `ecole_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eleves`
--

INSERT INTO `eleves` (`id`, `matricule`, `nom`, `postnom`, `prenom`, `genre`, `lieu_naissance`, `nationalite`, `adresse`, `date_naissance`, `parent_id`, `nom_pere`, `nom_mere`, `province_origine`, `territoire`, `secteur`, `groupement`, `village`, `num_permanent`, `photo`, `statut_eleve`, `ecole_id`) VALUES
(1, NULL, 'Kambale', 'Muhindo', 'Justin', 'M', NULL, 'CONGOLAISE', NULL, '2012-05-14', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'actif', NULL),
(2, NULL, 'Paluku', 'Muhindo', 'Arsène', 'M', NULL, 'CONGOLAISE', NULL, '2009-08-22', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'actif', NULL),
(3, 'JSDJSK21', 'Masika', 'Kavira', 'Clarisse', 'F', NULL, 'CONGOLAISE', NULL, '2021-02-02', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'actif', NULL),
(4, 'AMINA-20260703085920-252854', 'AMINA', 'MBOKASI', 'ANGE', 'F', 'bunia', 'CONGOLAISE', 'RULENGA\r\nNdosho', '2011-09-03', 1, 'Jean', 'Marie', 'Nord-Kivu', 'Goma', 'Sector 1', 'Group A', 'Village 1', '', NULL, 'actif', NULL),
(5, 'AGISHA-20260703122431-213539', 'AGISHA', 'MITIMA', 'PRUNELLE', 'F', 'GOMA', 'CONGOLAISE', 'RULENGA\r\nNdosho', '2019-02-01', 1, 'JEAN PAUL', 'MUHINDO', 'Nord-Kivu', 'Beni', '', '', '', '', NULL, 'actif', 1),
(6, 'B-20260705013018-a99f18', 'Bahati', 'kalimira', 'angle', 'F', 'GOMA', 'CONGOLAISE', 'Goma\r\nNdosho', '2022-06-12', 2, 'Joseph KIZOMBO', 'Joseph KIZOMBO', 'Nord-Kivu', 'Goma', 'Sector 1', 'Group A', 'Village 1', '', NULL, 'actif', 1),
(7, 'UNIQUE-TEST-1783237523', 'Test', 'Unique', 'Case', 'M', NULL, 'CONGOLAISE', NULL, '2000-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactif', NULL),
(8, 'UNIQUE-TEST-1783237559', 'Test', 'Unique', 'Case', 'M', NULL, 'CONGOLAISE', NULL, '2000-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'inactif', NULL),
(9, 'ANGELA-20260705123651-584c73', 'ANGELA', 'BAHATI', 'MIRIAM', 'F', 'GOMA', 'CONGOLAISE', 'HZHSDCSJ', '2017-02-12', 5, 'BAHATI', 'GRACE', 'Nord-Kivu', 'Beni', 'Beni-Centre', 'Group X', 'Village X1', '', NULL, 'actif', 4);

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `attribution_cours_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `type_evaluation` enum('Interrogation','Devoir','Exposé','Examen') NOT NULL,
  `date_evaluation` date NOT NULL,
  `ponderation_max` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evenements`
--

CREATE TABLE `evenements` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `lieu` varchar(150) DEFAULT 'Au Complexe',
  `categorie_id` int(11) NOT NULL,
  `organisateur_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evenements`
--

INSERT INTO `evenements` (`id`, `ecole_id`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`, `categorie_id`, `organisateur_id`, `annee_scolaire_id`) VALUES
(1, 1, 'Conseil des Directeurs', 'Planification de la clôture des notes du premier semestre.', '2026-06-25 10:00:00', '2026-06-25 13:00:00', 'Bureau du Préfet', 1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `fiches_paie`
--

CREATE TABLE `fiches_paie` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `mois` int(11) NOT NULL,
  `annee` int(11) NOT NULL,
  `salaire_base_historique` decimal(10,2) NOT NULL,
  `total_primes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_retenues` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_a_payer` decimal(10,2) NOT NULL,
  `caisse_banque_id` int(11) DEFAULT NULL,
  `date_edition` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut_paiement` enum('En attente','Payé','Bloqué') DEFAULT 'En attente',
  `date_paiement_effectif` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fiches_paie`
--

INSERT INTO `fiches_paie` (`id`, `agent_id`, `mois`, `annee`, `salaire_base_historique`, `total_primes`, `total_retenues`, `net_a_payer`, `caisse_banque_id`, `date_edition`, `statut_paiement`, `date_paiement_effectif`) VALUES
(1, 3, 6, 2026, 400.00, 40.00, 35.00, 405.00, NULL, '2026-06-23 12:50:05', 'En attente', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fiche_prime`
--

CREATE TABLE `fiche_prime` (
  `id` int(11) NOT NULL,
  `fiche_paie_id` int(11) NOT NULL,
  `prime_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fiche_prime`
--

INSERT INTO `fiche_prime` (`id`, `fiche_paie_id`, `prime_id`) VALUES
(1, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `fiche_retenue`
--

CREATE TABLE `fiche_retenue` (
  `id` int(11) NOT NULL,
  `fiche_paie_id` int(11) NOT NULL,
  `retenue_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fiche_retenue`
--

INSERT INTO `fiche_retenue` (`id`, `fiche_paie_id`, `retenue_id`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `frais_scolaires`
--

CREATE TABLE `frais_scolaires` (
  `id` int(11) NOT NULL,
  `classe_id` int(11) DEFAULT NULL,
  `type_frais` varchar(100) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `devise` varchar(5) NOT NULL DEFAULT 'USD',
  `scope` varchar(20) NOT NULL DEFAULT 'class',
  `scope_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `frais_scolaires`
--

INSERT INTO `frais_scolaires` (`id`, `classe_id`, `type_frais`, `montant_total`, `annee_scolaire_id`, `devise`, `scope`, `scope_id`) VALUES
(1, 3, 'Minerval 1er Trimestre', 150.00, 1, 'USD', 'class', NULL),
(2, NULL, 'Frais technique', 45.00, 1, 'USD', 'option', 3),
(3, 4, 'Frais de l\'Etat', 30000.00, 1, 'CDF', 'class', NULL),
(4, NULL, 'FRAIS DE CONSTRUCTION', 1.00, 1, 'USD', 'school', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `historique_etablissements`
--

CREATE TABLE `historique_etablissements` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `nom_ecole_provenance` varchar(150) DEFAULT NULL,
  `classe_bulletin` varchar(50) NOT NULL,
  `classe_precedente` varchar(50) DEFAULT NULL,
  `annee_depart_arrivee` varchar(20) NOT NULL,
  `motif_changement` text DEFAULT NULL,
  `pourcentage_obtenu` decimal(5,2) DEFAULT NULL,
  `application_conduite` varchar(50) DEFAULT NULL,
  `fichier_bulletin_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `historique_etablissements`
--

INSERT INTO `historique_etablissements` (`id`, `eleve_id`, `nom_ecole_provenance`, `classe_bulletin`, `classe_precedente`, `annee_depart_arrivee`, `motif_changement`, `pourcentage_obtenu`, `application_conduite`, `fichier_bulletin_url`) VALUES
(1, 1, 'Complexe Scolaire de Goma', '5ème Année Primaire', NULL, '2024-2025', 'Déménagement des parents', 72.50, 'Très Bonne', '/uploads/bulletins/2026/eleve_1_5eme.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `moyenne_annuelle` decimal(4,2) DEFAULT NULL,
  `rang` int(11) DEFAULT NULL,
  `decision_finale` enum('Admis','Doublant','Transféré','Exclu','En cours') DEFAULT 'En cours'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inscriptions`
--

INSERT INTO `inscriptions` (`id`, `eleve_id`, `classe_id`, `annee_scolaire_id`, `date_inscription`, `moyenne_annuelle`, `rang`, `decision_finale`) VALUES
(1, 1, 2, 1, '2026-06-23 12:50:05', NULL, NULL, 'En cours'),
(2, 2, 3, 1, '2026-06-23 12:50:05', NULL, NULL, 'En cours'),
(3, 3, 1, 1, '2026-06-23 12:50:05', NULL, NULL, 'En cours'),
(4, 5, 2, 1, '2026-07-03 10:24:31', NULL, NULL, 'En cours'),
(5, 6, 1, 1, '2026-07-04 23:30:18', NULL, NULL, 'En cours');

-- --------------------------------------------------------

--
-- Table structure for table `journees_pedagogiques`
--

CREATE TABLE `journees_pedagogiques` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `jour_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `journees_pedagogiques`
--

INSERT INTO `journees_pedagogiques` (`id`, `agent_id`, `jour_id`) VALUES
(1, 4, 3),
(2, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `jours_semaine`
--

CREATE TABLE `jours_semaine` (
  `id` int(11) NOT NULL,
  `nom_jour` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jours_semaine`
--

INSERT INTO `jours_semaine` (`id`, `nom_jour`) VALUES
(1, 'Lundi'),
(2, 'Mardi'),
(3, 'Mercredi'),
(4, 'Jeudi'),
(5, 'Vendredi'),
(6, 'Samedi');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `note_obtenue` decimal(4,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `nom_option` varchar(100) NOT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `nom_option`, `section_id`) VALUES
(1, 'Technique Informatique', 3),
(2, 'Commerciale et Gestion', 3),
(3, 'Sciences', NULL),
(4, 'Vétérinaire', 3),
(5, 'Technique Sociale', 3),
(6, 'Education de base', 3);

-- --------------------------------------------------------

--
-- Table structure for table `paiements_eleves`
--

CREATE TABLE `paiements_eleves` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `frais_id` int(11) NOT NULL,
  `montant_paye` decimal(10,2) NOT NULL,
  `date_paiement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paiements_eleves`
--

INSERT INTO `paiements_eleves` (`id`, `eleve_id`, `frais_id`, `montant_paye`, `date_paiement`) VALUES
(1, 2, 1, 100.00, '2026-06-23 12:50:05'),
(2, 2, 2, 50.00, '2026-06-23 12:50:05'),
(3, 1, 4, 1.00, '2026-07-05 00:00:36'),
(4, 9, 2, 1.00, '2026-07-05 10:38:17'),
(5, 9, 2, 23.00, '2026-07-05 19:43:21');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `nom_responsable` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`id`, `ecole_id`, `nom_responsable`, `telephone`, `email`, `mot_de_passe`) VALUES
(1, 1, 'Jean-Paul Muhindo', '+243991234567', 'jeanpaul@gmail.com', '$2y$10$abcdefghijklmnopqrstuv'),
(2, 1, 'Marie Kavira', '+243811234567', 'mariekav@gmail.com', '$2y$10$abcdefghijklmnopqrstuv'),
(3, 1, 'KIZOMBO LWALABA Joseph Kizombo', '0785747734', 'marc.balume@outlook.com', '$2y$10$Q1qdbvmfw/TchZ6A8tIs9el.CMHlI3vGLeJfERMb1PZ/Z1tVGSYje'),
(4, 4, 'JOH KIZO', '0785747734', 'adminkizo@gmail.com', '$2y$10$mKPtCGYOaGY6M3TBS80kgO2IV029dTO7HBhV/yE/SqpOKxuFhinDe'),
(5, 4, 'BAHATI', '097832832', 'codekizo8@gmail.com', '$2y$10$/U/I7FQ.2kF4qjElU.X2L.PLmS4u.6Rm33b7eoEEZDKWKnmh43MTG');

-- --------------------------------------------------------

--
-- Table structure for table `participants_evenement`
--

CREATE TABLE `participants_evenement` (
  `id` int(11) NOT NULL,
  `evenement_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `statut_participation` enum('Inconnu','Présence Confirmée','Excusé','Absent') DEFAULT 'Inconnu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participants_evenement`
--

INSERT INTO `participants_evenement` (`id`, `evenement_id`, `agent_id`, `statut_participation`) VALUES
(1, 1, 1, 'Présence Confirmée'),
(2, 1, 2, 'Inconnu');

-- --------------------------------------------------------

--
-- Table structure for table `periodes`
--

CREATE TABLE `periodes` (
  `id` int(11) NOT NULL,
  `nom_periode` varchar(50) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `cloturee` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `periodes`
--

INSERT INTO `periodes` (`id`, `nom_periode`, `annee_scolaire_id`, `cloturee`) VALUES
(1, '1ère Période', 1, 0),
(2, '2ème Période', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `plans_abonnement`
--

CREATE TABLE `plans_abonnement` (
  `id` int(11) NOT NULL,
  `nom_plan` varchar(50) NOT NULL,
  `duree_mois` int(11) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presences_agents`
--

CREATE TABLE `presences_agents` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `date_presence` date NOT NULL,
  `statut` enum('Présent','Absent','Journée Pédagogique','Congé') NOT NULL,
  `justification` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `presences_agents`
--

INSERT INTO `presences_agents` (`id`, `agent_id`, `date_presence`, `statut`, `justification`) VALUES
(1, 4, '2026-06-17', 'Journée Pédagogique', 'Attitré par l\'administration'),
(2, 4, '2026-06-18', 'Présent', NULL),
(3, 5, '2026-06-18', 'Absent', 'Maladie signalée');

-- --------------------------------------------------------

--
-- Table structure for table `presences_eleves`
--

CREATE TABLE `presences_eleves` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `date_jour` date NOT NULL,
  `statut` enum('Présent','Absent Justifié','Absent Non Justifié','Retard') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `presences_eleves`
--

INSERT INTO `presences_eleves` (`id`, `eleve_id`, `date_jour`, `statut`) VALUES
(1, 2, '2026-06-15', 'Présent'),
(2, 2, '2026-06-16', 'Retard');

-- --------------------------------------------------------

--
-- Table structure for table `primes_avantages`
--

CREATE TABLE `primes_avantages` (
  `id` int(11) NOT NULL,
  `nom_prime` varchar(150) NOT NULL,
  `montant` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `primes_avantages`
--

INSERT INTO `primes_avantages` (`id`, `nom_prime`, `montant`) VALUES
(1, 'Prime de Responsabilité', 60.00),
(2, 'Transport & Logement', 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `retenues_taxes`
--

CREATE TABLE `retenues_taxes` (
  `id` int(11) NOT NULL,
  `nom_retenue` varchar(150) NOT NULL,
  `montant` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `retenues_taxes`
--

INSERT INTO `retenues_taxes` (`id`, `nom_retenue`, `montant`) VALUES
(1, 'Impôt Professionnel sur le Revenu (IPR)', 35.00),
(2, 'Avance sur salaire', 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `roles_administration`
--

CREATE TABLE `roles_administration` (
  `id` int(11) NOT NULL,
  `titre_role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles_administration`
--

INSERT INTO `roles_administration` (`id`, `titre_role`) VALUES
(1, 'Préfet des études'),
(2, 'Directeur Principal (Primaire)'),
(3, 'Directeur Adjoint (Primaire)'),
(4, 'Directeur des études'),
(5, 'Secrétaire'),
(6, 'Enseignant Titulaire'),
(7, 'Enseignant');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `nom_section` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `nom_section`) VALUES
(1, 'Maternelle'),
(2, 'Primaire'),
(3, 'Secondaire');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(150) NOT NULL,
  `identifiant` varchar(100) NOT NULL COMMENT 'Email ou Téléphone pour la connexion',
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('super_admin','ecole_admin','préfet_école','DE_école','DD_école','DP_école','DA_école','comptable_école','sec_école','promoteur_école','enseignant_école','eleve_ecole','parent_ecole') NOT NULL,
  `ecole_id` int(11) DEFAULT NULL COMMENT 'NULL uniquement pour super_admin',
  `reference_id` int(11) DEFAULT NULL COMMENT 'ID de la table agents, parents ou eleves',
  `statut` enum('Actif','Inactif','Suspendu') DEFAULT 'Actif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL COMMENT 'Section affectÚe Ó l utilisateur pour ses responsabilitÚs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom_complet`, `identifiant`, `mot_de_passe`, `role`, `ecole_id`, `reference_id`, `statut`, `created_at`, `avatar`, `section_id`) VALUES
(1, 'Kakule Vianney Jean', 'vianney@gmail.com', '$2y$10$Q1qdbvmfw...', 'préfet_école', 1, 1, 'Actif', '2026-06-27 22:08:32', NULL, 3),
(2, 'Luvualu Kizombo Joseph', 'joseph@gmail.com', '$2y$10$Q1qdbvmfw...', 'sec_école', 1, 3, 'Actif', '2026-06-27 22:08:32', NULL, NULL),
(3, 'KIZOMBO LWALABA Joseph', 'marc.balume@outlook.com', '$2y$10$Q1qdbvmfw...', 'parent_ecole', 1, 3, 'Actif', '2026-06-27 22:08:32', NULL, NULL),
(100, 'Parent Test', 'parent.test@example.com', '<HASH_PARENT>', 'parent_ecole', 1, 1, 'Actif', '2026-06-27 23:06:48', NULL, NULL),
(101, 'Agent Test', '+243990000003', '<HASH_AGENT>', 'enseignant_école', 1, 3, 'Actif', '2026-06-27 23:06:48', NULL, NULL),
(102, 'Élève Test', 'eleve1@example.com', '<HASH_ELEVE>', 'eleve_ecole', 1, 1, 'Actif', '2026-06-27 23:06:48', NULL, NULL),
(103, 'Élève Test', 'eleve1', '$2y$10$Lym17ZPP92J6rWk983h0hOah.heso4HeP/a/qMxunIW7vcKJaEm/2', 'eleve_ecole', NULL, NULL, 'Actif', '2026-07-01 22:48:00', NULL, NULL),
(104, 'Parent Test', 'parent1', '$2y$10$NPVVWL97d0/r.D53V9f.muUOOfrPyyt/pv4hSIeN8xHZMmGIoAzyu', 'parent_ecole', 4, NULL, 'Actif', '2026-07-01 22:48:00', NULL, NULL),
(105, 'Agent Test', 'agent1', '$2y$10$F7FgxXypSjvLQ5ZsOQiNX.NfpGpdMT7O1LhdC9Bcp8x5AvcCF8wsS', '', NULL, NULL, 'Actif', '2026-07-01 22:48:00', NULL, NULL),
(106, 'École Test', 'ecole1', '$2y$10$.GPiu8iKdwNgPSfgxQD7vun17cpOX8p8qVAEOCSIIyny0lkWfrd5O', 'ecole_admin', NULL, NULL, 'Actif', '2026-07-01 22:48:00', NULL, NULL),
(107, 'Admin Test', 'admin1', '$2y$10$OFUC2olSBwR2BbWp4ydOn.wgskYJvtrf6IqdfCUmu6206VWNUAbG.', 'super_admin', NULL, NULL, 'Actif', '2026-07-01 22:48:00', NULL, NULL),
(108, 'Kakule Vianney Jean', '+243990000001', '$2y$10$twxGqzs8HefkSITzCIT98uYYgrXw5L/wJY4tGQOUWNsl81ZFzf1tG', 'enseignant_école', 1, 1, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(109, 'Kavugho Sifa Anny', '+243990000002', '$2y$10$T2az0YPx6rX.r8YGmYg2He3Cp19i8tvDfOvRy/FoZbF7UwH4aw1fq', 'enseignant_école', 1, 2, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(110, 'Kambale Mwisa Pascal', '+243990000004', '$2y$10$JnU2HRTeEzUTJ.9hCHL5CuVfoDEHmoGUsZC9YGq3JI1.CO9hv7tZq', 'enseignant_école', 1, 4, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(111, 'Mumbere Kitsali Claude', '+243990000005', '$2y$10$EJip1Q1oWk4KquNtO1SBJuk9eM30oJJN63XI1X5BLghXvqS2PpJRe', 'enseignant_école', 1, 5, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(112, 'Marie Kavira', 'mariekav@gmail.com', '$2y$10$83gZl3Nan4NZHUQtbFCkBurbmqkX6CF9aL0tTPqdir3dxQOz.QDeW', 'parent_ecole', 1, 2, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(113, 'JOH KIZO', 'adminkizo@gmail.com', '$2y$10$SjUUuKlXcwUVehJJAr3xe.QepJGjg9AyGv9NlN3G.moFa.54e91qC', 'parent_ecole', 4, 4, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(114, 'Arsène Paluku', 'eleve2@school.local', '$2y$10$y7iecyfnenW334rEdFCKsuOusJpGSdhLM5qKhyiAx8Mwk6mFG2sxC', 'eleve_ecole', 1, 2, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(115, 'Clarisse Masika', 'JSDJSK21', '$2y$10$OTIXV7Uk9MlweqkfBpH0a.tTaSEaQ.v4IgLGdPQTK0Mp1Dxj3Iyo2', 'eleve_ecole', 1, 3, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(116, 'Comptable Test', 'comptable_test1@example.com', '$2y$10$Q/bNi1xdBXvJwTOiOrgjweTFO30ZpCwCVdeuu3LymqoMrX5T4AHGe', 'comptable_école', 4, NULL, 'Actif', '2026-07-03 00:38:14', NULL, NULL),
(130, 'Super Admin Test', 'super_admin@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'super_admin', NULL, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(131, 'École Admin Test', 'ecole_admin@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'ecole_admin', 4, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(132, 'Préfet École Test', 'prefet_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'préfet_école', 4, NULL, 'Actif', '2026-07-03 07:51:05', NULL, 3),
(133, 'Directeur des études Test', 'DE_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'DE_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, 3),
(134, 'Directeur Département Test', 'DD_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'DD_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, 3),
(135, 'Directeur Principal Test', 'DP_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'DP_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, 2),
(136, 'Directeur Adjoint Test', 'DA_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'DA_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, 2),
(137, 'Comptable École Test', 'comptable_ecole@test.local', '$2y$10$vrcPVjUDDdeePxUB.szm9uuDw4aPtnNMmOnXq5bUUxmpg9Lu38BUe', 'comptable_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(138, 'Secrétaire École Test', 'sec_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'sec_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(139, 'Promoteur École Test', 'promoteur_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'promoteur_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(140, 'Enseignant École Test', 'enseignant_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'enseignant_école', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(141, 'Élève École Test', 'eleve_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'eleve_ecole', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL),
(142, 'Parent École Test', 'parent_ecole@test.local', '$2y$10$hYxzSYamKo9hylHM6gqpsO6fRNcl./0dv8ahyPuEYwEsEAXF5BEZC', 'parent_ecole', 1, NULL, 'Actif', '2026-07-03 07:51:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_liaison_utilisateurs_complete`
-- (See below for the actual view)
--
CREATE TABLE `v_liaison_utilisateurs_complete` (
`utilisateur_id` int(11)
,`compte_nom_affiche` varchar(150)
,`compte_identifiant` varchar(100)
,`compte_role` enum('super_admin','ecole_admin','préfet_école','DE_école','DD_école','DP_école','DA_école','comptable_école','sec_école','promoteur_école','enseignant_école','eleve_ecole','parent_ecole')
,`compte_statut` enum('Actif','Inactif','Suspendu')
,`ecole_id` int(11)
,`agent_id` int(11)
,`agent_fonction_exacte` varchar(100)
,`parent_id` int(11)
,`parent_nom_responsable` varchar(150)
,`eleve_id` int(11)
,`eleve_nom_complet` varchar(101)
);

-- --------------------------------------------------------

--
-- Structure for view `v_liaison_utilisateurs_complete`
--
DROP TABLE IF EXISTS `v_liaison_utilisateurs_complete`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_liaison_utilisateurs_complete`  AS SELECT `u`.`id` AS `utilisateur_id`, `u`.`nom_complet` AS `compte_nom_affiche`, `u`.`identifiant` AS `compte_identifiant`, `u`.`role` AS `compte_role`, `u`.`statut` AS `compte_statut`, `u`.`ecole_id` AS `ecole_id`, `a`.`id` AS `agent_id`, `r`.`titre_role` AS `agent_fonction_exacte`, `p`.`id` AS `parent_id`, `p`.`nom_responsable` AS `parent_nom_responsable`, `e`.`id` AS `eleve_id`, concat(`e`.`prenom`,' ',`e`.`nom`) AS `eleve_nom_complet` FROM (((((`utilisateurs` `u` left join `agents` `a` on(`u`.`reference_id` = `a`.`id` and `u`.`role` not in ('super_admin','parent_ecole','eleve_ecole'))) left join `roles_administration` `r` on(`a`.`role_id` = `r`.`id`)) left join `eleves` `e_direct` on(`u`.`reference_id` = `e_direct`.`id` and `u`.`role` = 'eleve_ecole')) left join `parents` `p` on(`u`.`reference_id` = `p`.`id` and `u`.`role` = 'parent_ecole' or `e_direct`.`parent_id` = `p`.`id`)) left join `eleves` `e` on(`e`.`id` = `e_direct`.`id` or `e`.`parent_id` = `p`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abonnements_ecoles`
--
ALTER TABLE `abonnements_ecoles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_abonnements_ecole` (`ecole_id`),
  ADD KEY `fk_abonnements_plan` (`plan_id`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_agents_ecole_final` (`ecole_id`);

--
-- Indexes for table `annees_scolaires`
--
ALTER TABLE `annees_scolaires`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attribution` (`classe_id`,`cours_id`,`annee_scolaire_id`),
  ADD KEY `cours_id` (`cours_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`);

--
-- Indexes for table `caisses_banques`
--
ALTER TABLE `caisses_banques`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories_evenements`
--
ALTER TABLE `categories_evenements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `child_requests`
--
ALTER TABLE `child_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `option_id` (`option_id`);

--
-- Indexes for table `comptes_eleves`
--
ALTER TABLE `comptes_eleves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_compte_eleve_annee` (`eleve_id`,`annee_scolaire_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`);

--
-- Indexes for table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dettes_eleves`
--
ALTER TABLE `dettes_eleves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`),
  ADD KEY `frais_id` (`frais_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`),
  ADD KEY `montant_restant` (`montant_restant`);

--
-- Indexes for table `devises`
--
ALTER TABLE `devises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `discipline_eleves`
--
ALTER TABLE `discipline_eleves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indexes for table `ecoles`
--
ALTER TABLE `ecoles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_officiel` (`email_officiel`);

--
-- Indexes for table `ecritures_comptables_eleves`
--
ALTER TABLE `ecritures_comptables_eleves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_recu` (`reference_recu`),
  ADD UNIQUE KEY `idx_ecritures_reference_recu_unique` (`reference_recu`),
  ADD KEY `compte_eleve_id` (`compte_eleve_id`),
  ADD KEY `frais_id` (`frais_id`),
  ADD KEY `caisse_banque_id` (`caisse_banque_id`),
  ADD KEY `agent_saisie_id` (`agent_saisie_id`);

--
-- Indexes for table `eleves`
--
ALTER TABLE `eleves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_eleve_matricule` (`matricule`),
  ADD UNIQUE KEY `idx_eleves_matricule_unique` (`matricule`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribution_cours_id` (`attribution_cours_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indexes for table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`),
  ADD KEY `organisateur_id` (`organisateur_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`);

--
-- Indexes for table `fiches_paie`
--
ALTER TABLE `fiches_paie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_paie_mensuelle` (`agent_id`,`mois`,`annee`),
  ADD KEY `fk_paie_caisse` (`caisse_banque_id`);

--
-- Indexes for table `fiche_prime`
--
ALTER TABLE `fiche_prime`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fiche_prime` (`fiche_paie_id`,`prime_id`),
  ADD KEY `fk_fp_final_prime` (`prime_id`);

--
-- Indexes for table `fiche_retenue`
--
ALTER TABLE `fiche_retenue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_fiche_retenue` (`fiche_paie_id`,`retenue_id`),
  ADD KEY `fk_fr_final_ret` (`retenue_id`);

--
-- Indexes for table `frais_scolaires`
--
ALTER TABLE `frais_scolaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classe_id` (`classe_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`),
  ADD KEY `scope_id` (`scope_id`);

--
-- Indexes for table `historique_etablissements`
--
ALTER TABLE `historique_etablissements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`);

--
-- Indexes for table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inscription_annuelle` (`eleve_id`,`annee_scolaire_id`),
  ADD KEY `classe_id` (`classe_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`);

--
-- Indexes for table `journees_pedagogiques`
--
ALTER TABLE `journees_pedagogiques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_journee_enseignant` (`agent_id`,`jour_id`),
  ADD KEY `jour_id` (`jour_id`);

--
-- Indexes for table `jours_semaine`
--
ALTER TABLE `jours_semaine`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_note_eleve_evaluation` (`evaluation_id`,`eleve_id`),
  ADD KEY `eleve_id` (`eleve_id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_options_sections` (`section_id`);

--
-- Indexes for table `paiements_eleves`
--
ALTER TABLE `paiements_eleves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`),
  ADD KEY `frais_id` (`frais_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_parent_telephone_ecole` (`ecole_id`,`telephone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_participant_ev` (`evenement_id`,`agent_id`),
  ADD KEY `fk_part_final_agent` (`agent_id`);

--
-- Indexes for table `periodes`
--
ALTER TABLE `periodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`);

--
-- Indexes for table `plans_abonnement`
--
ALTER TABLE `plans_abonnement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presences_agents`
--
ALTER TABLE `presences_agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_presence_agent_par_jour` (`agent_id`,`date_presence`);

--
-- Indexes for table `presences_eleves`
--
ALTER TABLE `presences_eleves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_presence_eleve_par_jour` (`eleve_id`,`date_jour`);

--
-- Indexes for table `primes_avantages`
--
ALTER TABLE `primes_avantages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `retenues_taxes`
--
ALTER TABLE `retenues_taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles_administration`
--
ALTER TABLE `roles_administration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_identifiant` (`identifiant`),
  ADD KEY `fk_utilisateurs_ecole` (`ecole_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abonnements_ecoles`
--
ALTER TABLE `abonnements_ecoles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `annees_scolaires`
--
ALTER TABLE `annees_scolaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `caisses_banques`
--
ALTER TABLE `caisses_banques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories_evenements`
--
ALTER TABLE `categories_evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `child_requests`
--
ALTER TABLE `child_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comptes_eleves`
--
ALTER TABLE `comptes_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cours`
--
ALTER TABLE `cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dettes_eleves`
--
ALTER TABLE `dettes_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devises`
--
ALTER TABLE `devises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `discipline_eleves`
--
ALTER TABLE `discipline_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ecoles`
--
ALTER TABLE `ecoles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ecritures_comptables_eleves`
--
ALTER TABLE `ecritures_comptables_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `eleves`
--
ALTER TABLE `eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fiches_paie`
--
ALTER TABLE `fiches_paie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fiche_prime`
--
ALTER TABLE `fiche_prime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fiche_retenue`
--
ALTER TABLE `fiche_retenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `frais_scolaires`
--
ALTER TABLE `frais_scolaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `historique_etablissements`
--
ALTER TABLE `historique_etablissements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `journees_pedagogiques`
--
ALTER TABLE `journees_pedagogiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jours_semaine`
--
ALTER TABLE `jours_semaine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `paiements_eleves`
--
ALTER TABLE `paiements_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `periodes`
--
ALTER TABLE `periodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plans_abonnement`
--
ALTER TABLE `plans_abonnement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `presences_agents`
--
ALTER TABLE `presences_agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `presences_eleves`
--
ALTER TABLE `presences_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `primes_avantages`
--
ALTER TABLE `primes_avantages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `retenues_taxes`
--
ALTER TABLE `retenues_taxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles_administration`
--
ALTER TABLE `roles_administration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abonnements_ecoles`
--
ALTER TABLE `abonnements_ecoles`
  ADD CONSTRAINT `fk_abonnements_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_abonnements_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans_abonnement` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles_administration` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agents_ecole_final` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agents_roles` FOREIGN KEY (`role_id`) REFERENCES `roles_administration` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  ADD CONSTRAINT `attribution_cours_ibfk_1` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `attribution_cours_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `attribution_cours_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `attribution_cours_ibfk_4` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attr_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attr_annee` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attr_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attr_cours` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `child_requests`
--
ALTER TABLE `child_requests`
  ADD CONSTRAINT `child_requests_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_classes_options` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_classes_sections` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `comptes_eleves`
--
ALTER TABLE `comptes_eleves`
  ADD CONSTRAINT `comptes_eleves_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comptes_eleves_ibfk_2` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`),
  ADD CONSTRAINT `fk_comptes_annee` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comptes_eleves` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dettes_eleves`
--
ALTER TABLE `dettes_eleves`
  ADD CONSTRAINT `dettes_eleves_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dettes_eleves_ibfk_2` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dettes_eleves_ibfk_3` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `discipline_eleves`
--
ALTER TABLE `discipline_eleves`
  ADD CONSTRAINT `discipline_eleves_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discipline_eleves_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_disc_eleves` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_disc_periodes` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ecritures_comptables_eleves`
--
ALTER TABLE `ecritures_comptables_eleves`
  ADD CONSTRAINT `ecritures_comptables_eleves_ibfk_1` FOREIGN KEY (`compte_eleve_id`) REFERENCES `comptes_eleves` (`id`),
  ADD CONSTRAINT `ecritures_comptables_eleves_ibfk_2` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`),
  ADD CONSTRAINT `ecritures_comptables_eleves_ibfk_3` FOREIGN KEY (`caisse_banque_id`) REFERENCES `caisses_banques` (`id`),
  ADD CONSTRAINT `ecritures_comptables_eleves_ibfk_4` FOREIGN KEY (`agent_saisie_id`) REFERENCES `agents` (`id`),
  ADD CONSTRAINT `fk_ecr_agent` FOREIGN KEY (`agent_saisie_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ecr_caisse` FOREIGN KEY (`caisse_banque_id`) REFERENCES `caisses_banques` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ecr_compte_eleve` FOREIGN KEY (`compte_eleve_id`) REFERENCES `comptes_eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ecr_frais` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `eleves`
--
ALTER TABLE `eleves`
  ADD CONSTRAINT `eleves_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eleve_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eleves_parents` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`attribution_cours_id`) REFERENCES `attribution_cours` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eval_attribution` FOREIGN KEY (`attribution_cours_id`) REFERENCES `attribution_cours` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eval_periodes` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `evenements`
--
ALTER TABLE `evenements`
  ADD CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories_evenements` (`id`),
  ADD CONSTRAINT `evenements_ibfk_2` FOREIGN KEY (`organisateur_id`) REFERENCES `agents` (`id`),
  ADD CONSTRAINT `evenements_ibfk_3` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ev_annee` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ev_cat` FOREIGN KEY (`categorie_id`) REFERENCES `categories_evenements` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ev_organisateur` FOREIGN KEY (`organisateur_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `fiches_paie`
--
ALTER TABLE `fiches_paie`
  ADD CONSTRAINT `fiches_paie_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_final_fiches_paie_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_final_fiches_paie_caisse` FOREIGN KEY (`caisse_banque_id`) REFERENCES `caisses_banques` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `fiche_prime`
--
ALTER TABLE `fiche_prime`
  ADD CONSTRAINT `fk_fp_final_fiche` FOREIGN KEY (`fiche_paie_id`) REFERENCES `fiches_paie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fp_final_prime` FOREIGN KEY (`prime_id`) REFERENCES `primes_avantages` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `fiche_retenue`
--
ALTER TABLE `fiche_retenue`
  ADD CONSTRAINT `fk_fr_final_fiche` FOREIGN KEY (`fiche_paie_id`) REFERENCES `fiches_paie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fr_final_ret` FOREIGN KEY (`retenue_id`) REFERENCES `retenues_taxes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `frais_scolaires`
--
ALTER TABLE `frais_scolaires`
  ADD CONSTRAINT `fk_frais_final_annee` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_frais_final_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `frais_scolaires_ibfk_1` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `frais_scolaires_ibfk_2` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `historique_etablissements`
--
ALTER TABLE `historique_etablissements`
  ADD CONSTRAINT `fk_hist_final_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historique_etablissements_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `fk_insc_final_annee` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_insc_final_classe` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_insc_final_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_3` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `journees_pedagogiques`
--
ALTER TABLE `journees_pedagogiques`
  ADD CONSTRAINT `fk_jped_final_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jped_final_jour` FOREIGN KEY (`jour_id`) REFERENCES `jours_semaine` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `journees_pedagogiques_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journees_pedagogiques_ibfk_2` FOREIGN KEY (`jour_id`) REFERENCES `jours_semaine` (`id`);

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_notes_final_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notes_final_eval` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `fk_options_sections` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `paiements_eleves`
--
ALTER TABLE `paiements_eleves`
  ADD CONSTRAINT `fk_paie_final_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_paie_final_frais` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `paiements_eleves_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `paiements_eleves_ibfk_2` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  ADD CONSTRAINT `fk_part_final_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_part_final_ev` FOREIGN KEY (`evenement_id`) REFERENCES `evenements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `periodes`
--
ALTER TABLE `periodes`
  ADD CONSTRAINT `fk_per_final_annee` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `periodes_ibfk_1` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `presences_agents`
--
ALTER TABLE `presences_agents`
  ADD CONSTRAINT `fk_pres_final_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presences_agents_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `presences_eleves`
--
ALTER TABLE `presences_eleves`
  ADD CONSTRAINT `fk_pres_final_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presences_eleves_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
