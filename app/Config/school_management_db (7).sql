-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 05:12 PM
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
  `plan_id` int(11) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `statut_abonnement` enum('Actif','Expire','Resilie') DEFAULT 'Actif',
  `montant_paye` decimal(10,2) NOT NULL,
  `reference_paiement` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abonnements_ecoles`
--

INSERT INTO `abonnements_ecoles` (`id`, `ecole_id`, `plan_id`, `date_debut`, `date_fin`, `statut_abonnement`, `montant_paye`, `reference_paiement`) VALUES
(6, 4, 5, '2026-11-15 00:00:00', '2026-12-14 00:00:00', 'Actif', 50.00, NULL),
(13, 4, 5, '2026-10-15 00:00:00', '2026-11-14 00:00:00', 'Actif', 50.00, NULL),
(14, 3, NULL, '2026-10-24 00:00:00', '2026-11-08 00:00:00', 'Actif', 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `administration_autorisations`
--

CREATE TABLE `administration_autorisations` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `granted_by_user_id` int(11) DEFAULT NULL,
  `granted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `administration_autorisations`
--

INSERT INTO `administration_autorisations` (`id`, `ecole_id`, `role`, `module`, `actif`, `granted_by_user_id`, `granted_at`) VALUES
(1, 1827364510, 'enseignant_?cole', 'notes', 0, NULL, '2026-10-15 07:09:37'),
(5, 4, 'agent_ecole', 'inscriptions', 0, 169, '2026-10-22 14:14:29'),
(6, 4, 'préfet_école', 'inscriptions', 0, 169, '2026-10-22 14:14:35'),
(7, 4, 'DE_école', 'inscriptions', 0, 169, '2026-10-22 14:14:42'),
(8, 4, 'DP_école', 'inscriptions', 0, 169, '2026-10-22 14:14:47'),
(9, 4, 'DD_école', 'inscriptions', 0, 169, '2026-10-22 14:14:51'),
(10, 4, 'DA_école', 'inscriptions', 0, 169, '2026-10-22 14:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) DEFAULT NULL,
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
(5, 1, 'Mumbere', 'Kitsali', 'Claude', '+243990000005', NULL, NULL, 7, 350.00, 1),
(7, 4, 'Enseignant', 'Test', 'Présences', '+243000000047', 'enseignant.test@kc-sma.local', NULL, 7, 0.00, 0),
(8, 4, 'Test', 'Enseignant 01', 'Aline', '+2439999000001', 'enseignant.secondaire.test01@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(9, 4, 'Test', 'Enseignant 02', 'B?atrice', '+2439999000002', 'enseignant.secondaire.test02@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(10, 4, 'Test', 'Enseignant 03', 'C?dric', '+2439999000003', 'enseignant.secondaire.test03@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(11, 4, 'Test', 'Enseignant 04', 'Diane', '+2439999000004', 'enseignant.secondaire.test04@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(12, 4, 'Test', 'Enseignant 05', '?ric', '+2439999000005', 'enseignant.secondaire.test05@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(13, 4, 'Test', 'Enseignant 06', 'F?licit?', '+2439999000006', 'enseignant.secondaire.test06@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(14, 4, 'Test', 'Enseignant 07', 'G?rard', '+2439999000007', 'enseignant.secondaire.test07@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(15, 4, 'Test', 'Enseignant 08', 'H?l?ne', '+2439999000008', 'enseignant.secondaire.test08@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(16, 4, 'Test', 'Enseignant 09', 'Ir?ne', '+2439999000009', 'enseignant.secondaire.test09@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(17, 4, 'Test', 'Enseignant 10', 'Jean', '+2439999000010', 'enseignant.secondaire.test10@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(18, 4, 'Test', 'Enseignant 11', 'K?vin', '+2439999000011', 'enseignant.secondaire.test11@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(19, 4, 'Test', 'Enseignant 12', 'Laure', '+2439999000012', 'enseignant.secondaire.test12@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(20, 4, 'Test', 'Enseignant 13', 'Michel', '+2439999000013', 'enseignant.secondaire.test13@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(21, 4, 'Test', 'Enseignant 14', 'Nadine', '+2439999000014', 'enseignant.secondaire.test14@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(22, 4, 'Test', 'Enseignant 15', 'Olivier', '+2439999000015', 'enseignant.secondaire.test15@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(23, 4, 'Test', 'Enseignant 16', 'Pauline', '+2439999000016', 'enseignant.secondaire.test16@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(24, 4, 'Test', 'Enseignant 17', 'Quentin', '+2439999000017', 'enseignant.secondaire.test17@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(25, 4, 'Test', 'Enseignant 18', 'Rachel', '+2439999000018', 'enseignant.secondaire.test18@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(26, 4, 'Test', 'Enseignant 19', 'Samuel', '+2439999000019', 'enseignant.secondaire.test19@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(27, 4, 'Test', 'Enseignant 20', 'Th?r?se', '+2439999000020', 'enseignant.secondaire.test20@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(28, 4, 'Test', 'Enseignant 21', 'Urbain', '+2439999000021', 'enseignant.secondaire.test21@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(29, 4, 'Test', 'Enseignant 22', 'V?ronique', '+2439999000022', 'enseignant.secondaire.test22@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(30, 4, 'Test', 'Enseignant 23', 'William', '+2439999000023', 'enseignant.secondaire.test23@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(31, 4, 'Test', 'Enseignant 24', 'Yvette', '+2439999000024', 'enseignant.secondaire.test24@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(32, 4, 'Test', 'Enseignant 25', 'Zo?', '+2439999000025', 'enseignant.secondaire.test25@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(33, 4, 'Test', 'Enseignant 26', 'Armand', '+2439999000026', 'enseignant.secondaire.test26@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(34, 4, 'Test', 'Enseignant 27', 'Brigitte', '+2439999000027', 'enseignant.secondaire.test27@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(35, 4, 'Test', 'Enseignant 28', 'Christian', '+2439999000028', 'enseignant.secondaire.test28@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(36, 4, 'Test', 'Enseignant 29', 'Delphine', '+2439999000029', 'enseignant.secondaire.test29@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(37, 4, 'Test', 'Enseignant 30', 'Emmanuel', '+2439999000030', 'enseignant.secondaire.test30@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(38, 4, 'Test', 'Enseignant 31', 'Flore', '+2439999000031', 'enseignant.secondaire.test31@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(39, 4, 'Test', 'Enseignant 32', 'Gr?goire', '+2439999000032', 'enseignant.secondaire.test32@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(40, 4, 'Test', 'Enseignant 33', 'Hortense', '+2439999000033', 'enseignant.secondaire.test33@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(41, 4, 'Test', 'Enseignant 34', 'Isma?l', '+2439999000034', 'enseignant.secondaire.test34@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(42, 4, 'Test', 'Enseignant 35', 'Josu?', '+2439999000035', 'enseignant.secondaire.test35@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(43, 4, 'Test', 'Enseignant 36', 'L?onie', '+2439999000036', 'enseignant.secondaire.test36@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(44, 4, 'Test', 'Enseignant 37', 'Marcel', '+2439999000037', 'enseignant.secondaire.test37@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(45, 4, 'Test', 'Enseignant 38', 'No?lle', '+2439999000038', 'enseignant.secondaire.test38@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(46, 4, 'Test', 'Enseignant 39', 'Patrice', '+2439999000039', 'enseignant.secondaire.test39@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1),
(47, 4, 'Test', 'Enseignant 40', 'Ruth', '+2439999000040', 'enseignant.secondaire.test40@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 7, 0.00, 1);

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
(1, 1, '2025-2026', 1),
(2, 4, '2025-2026', 1);

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
(4, 1, 3, 2, 5, 1, 2, 10, 40),
(5, 4, 7, 4, 7, 2, 2, 10, 40),
(32, 4, 10, 11, 8, 2, 4, 10, 40),
(40, 4, 10, 22, 17, 2, 2, 10, 40),
(41, 4, 18, 22, 17, 2, 2, 10, 40),
(42, 4, 46, 22, 17, 2, 2, 10, 40),
(43, 4, 59, 22, 17, 2, 2, 10, 40),
(44, 4, 10, 10, 19, 2, 2, 10, 40),
(45, 4, 18, 10, 19, 2, 2, 10, 40),
(46, 4, 46, 10, 19, 2, 2, 10, 40),
(47, 4, 59, 10, 19, 2, 2, 10, 40),
(48, 4, 40, 25, 19, 2, 2, 10, 40),
(49, 4, 10, 26, 8, 2, 2, 10, 40),
(50, 4, 10, 29, 8, 2, 4, 10, 40),
(51, 4, 10, 28, 8, 2, 6, 10, 40),
(52, 4, 25, 27, 8, 2, 2, 10, 40),
(53, 4, 10, 30, 8, 2, 6, 10, 40);

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
(3, 'Journée Pédagogique Spéciale'),
(4, 'Proclamation');

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
(3, 1, '3ème Technique Informatique', 3, NULL),
(4, 1, '4ème Commerciale', 3, 2),
(5, 1, '1ère', 2, NULL),
(6, 4, '1ière', 2, NULL),
(7, 4, '7ème', 3, 6),
(8, 4, '4ième', 3, 13),
(9, 4, '2ième', 2, NULL),
(10, 4, '1ère', 3, 13),
(12, 4, '3ième', 2, NULL),
(13, 4, '4ième', 2, NULL),
(16, 4, '5ème A', 2, NULL),
(17, 4, '3ème', 3, 7),
(18, 4, '1ère', 3, 20),
(19, 4, '2ème', 3, 18),
(20, 4, '5ème B', 2, NULL),
(21, 4, '2ème A', 2, NULL),
(22, 4, '7ème', 2, 41),
(23, 4, '6ème A', 2, NULL),
(24, 4, '3ème A', 2, NULL),
(25, 4, '3ème', 3, 5),
(26, 4, '1ère A', 2, NULL),
(27, 4, '8ème', 3, 6),
(28, 4, '1ère', 3, 5),
(29, 4, '4ème A', 2, NULL),
(30, 4, '2ème', 3, 7),
(31, 4, '2ème B', 2, NULL),
(32, 4, '6ème B', 2, NULL),
(33, 4, '3ème B', 2, NULL),
(34, 4, '4ème', 3, 7),
(35, 4, '1ère', 3, 7),
(36, 4, '2ème C', 2, NULL),
(37, 4, '2ème', 3, 5),
(38, 4, '4ème B', 2, NULL),
(39, 4, '1ère B', 2, NULL),
(40, 4, '1ère', 3, 14),
(41, 4, '2ème', 3, 20),
(42, 4, '4ème', 3, 5),
(43, 4, '3ème A', 1, NULL),
(44, 4, '3ème', 3, 20),
(45, 4, '3ème', 3, 14),
(46, 4, '1ère', 3, 16),
(47, 4, '2ème', 3, 16),
(48, 4, '4ème', 3, 18),
(49, 4, '2ème A', 1, NULL),
(50, 4, '1ère A', 1, NULL),
(51, 4, '2ème', 3, 13),
(52, 4, '2ème B', 1, NULL),
(53, 4, '2ème', 3, 14),
(54, 4, '3ème C', 2, NULL),
(55, 4, '3ème B', 1, NULL),
(56, 4, '1ère C', 2, NULL),
(57, 4, '3ème', 3, 16),
(58, 4, '4ème', 3, 20),
(59, 4, '1ère', 3, 18),
(60, 4, '1ère', 2, NULL),
(61, 4, '3ème', 3, 18);

-- --------------------------------------------------------

--
-- Table structure for table `communiques`
--

CREATE TABLE `communiques` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `auteur_user_id` int(11) NOT NULL,
  `titre` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `destinataires_total` int(11) NOT NULL DEFAULT 0,
  `destinataires_livres` int(11) NOT NULL DEFAULT 0,
  `destinataires_echec` int(11) NOT NULL DEFAULT 0,
  `statut` varchar(20) NOT NULL DEFAULT 'failed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `destinataires_roles` text DEFAULT NULL,
  `destinataires_sections` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `communiques`
--

INSERT INTO `communiques` (`id`, `ecole_id`, `auteur_user_id`, `titre`, `message`, `destinataires_total`, `destinataires_livres`, `destinataires_echec`, `statut`, `created_at`, `destinataires_roles`, `destinataires_sections`) VALUES
(3, 4, 170, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 2, 0, 2, 'failed', '2026-10-29 14:24:23', '[\"comptable_école\",\"parent_ecole\"]', '[3]'),
(4, 4, 170, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 2, 0, 2, 'failed', '2026-10-29 14:39:35', '[\"comptable_école\",\"parent_ecole\"]', '[3]'),
(5, 4, 170, 'Récouvrement Forcé', 'oui', 2, 0, 2, 'failed', '2026-10-29 14:41:06', '[\"comptable_école\",\"parent_ecole\"]', '[3]');

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
(1, 2, 1, 0.00),
(2, 3, 1, 0.00),
(3, 6, 1, 0.00),
(4, 1, 1, -1.00),
(5, 9, 1, -41.00),
(6, 10, 2, 93.20),
(7, 11, 2, 30115.50),
(8, 143, 2, 24002.00),
(9, 240, 2, 24002.00),
(10, 496, 2, 24002.00),
(11, 503, 2, 24002.00),
(12, 509, 2, 24002.00),
(13, 550, 2, 24002.00),
(14, 642, 2, 24002.00),
(15, 670, 2, 24002.00),
(16, 263, 2, 24002.00),
(17, 327, 2, 24002.00),
(18, 278, 2, 24002.00),
(19, 285, 2, 24002.00),
(20, 403, 2, 24002.00),
(21, 408, 2, 24002.00),
(22, 590, 2, 24002.00),
(23, 299, 2, 24002.00),
(24, 566, 2, 24002.00),
(25, 354, 2, 24002.00),
(26, 372, 2, 24002.00),
(27, 616, 2, -8.00),
(28, 425, 2, -14.00),
(29, 710, 2, 24087.00),
(30, 29, 2, 2.00),
(31, 71, 2, 2.00),
(32, 80, 2, 2.00),
(33, 88, 2, 2.00),
(34, 103, 2, 2.00),
(35, 142, 2, 2.00),
(36, 144, 2, 2.00),
(37, 163, 2, 2.00),
(38, 185, 2, 2.00),
(39, 193, 2, 2.00),
(40, 201, 2, 2.00),
(41, 202, 2, 2.00),
(42, 244, 2, 2.00),
(43, 245, 2, 2.00),
(44, 251, 2, 2.00),
(45, 252, 2, 2.00),
(46, 254, 2, 2.00),
(47, 256, 2, 2.00),
(48, 261, 2, 2.00),
(49, 319, 2, 2.00),
(50, 320, 2, 2.00),
(51, 321, 2, 2.00),
(52, 325, 2, 2.00),
(53, 326, 2, 2.00),
(54, 329, 2, 2.00),
(55, 351, 2, 2.00),
(56, 356, 2, 2.00),
(57, 363, 2, 2.00),
(58, 366, 2, 2.00),
(59, 374, 2, 2.00),
(60, 378, 2, 2.00),
(61, 384, 2, 2.00),
(62, 388, 2, 2.00),
(63, 392, 2, 2.00),
(64, 394, 2, 2.00),
(65, 398, 2, 2.00),
(66, 416, 2, 2.00),
(67, 422, 2, 2.00),
(68, 434, 2, 2.00),
(69, 436, 2, 2.00),
(70, 445, 2, 2.00),
(71, 446, 2, 2.00),
(72, 458, 2, 2.00),
(73, 483, 2, 2.00),
(74, 492, 2, 2.00),
(75, 498, 2, 2.00),
(76, 505, 2, 2.00),
(77, 510, 2, 2.00),
(78, 511, 2, 2.00),
(79, 512, 2, 2.00),
(80, 514, 2, 2.00),
(81, 522, 2, 2.00),
(82, 541, 2, 2.00),
(83, 563, 2, 2.00),
(84, 569, 2, 2.00),
(85, 570, 2, 2.00),
(86, 574, 2, 2.00),
(87, 585, 2, 2.00),
(88, 595, 2, 2.00),
(89, 603, 2, 2.00),
(90, 612, 2, 2.00),
(91, 615, 2, 2.00),
(92, 632, 2, 2.00),
(93, 633, 2, 2.00),
(94, 635, 2, 2.00),
(95, 646, 2, 2.00),
(96, 665, 2, 2.00),
(97, 666, 2, 2.00),
(98, 682, 2, 2.00),
(99, 685, 2, 2.00),
(100, 700, 2, 2.00),
(101, 704, 2, 2.00),
(102, 19, 2, 2.00),
(103, 60, 2, 2.00),
(104, 197, 2, 2.00),
(105, 390, 2, 2.00),
(106, 597, 2, 2.00),
(107, 672, 2, 2.00),
(108, 12, 2, 2.00),
(109, 15, 2, 2.00),
(110, 17, 2, 2.00),
(111, 43, 2, 2.00),
(112, 59, 2, 2.00),
(113, 94, 2, 2.00),
(114, 127, 2, 2.00),
(115, 132, 2, 2.00),
(116, 155, 2, 2.00),
(117, 156, 2, 2.00),
(118, 161, 2, 2.00),
(119, 175, 2, 2.00),
(120, 177, 2, 2.00),
(121, 182, 2, 2.00),
(122, 186, 2, 2.00),
(123, 206, 2, 2.00),
(124, 213, 2, 2.00),
(125, 301, 2, 2.00),
(126, 362, 2, 2.00),
(127, 460, 2, 2.00),
(128, 486, 2, 2.00),
(129, 516, 2, 2.00),
(130, 537, 2, 2.00),
(131, 564, 2, 2.00),
(132, 581, 2, 2.00),
(133, 599, 2, 2.00),
(134, 13, 2, 2.00),
(135, 41, 2, 2.00),
(136, 52, 2, 2.00),
(137, 57, 2, 2.00),
(138, 58, 2, 2.00),
(139, 63, 2, 2.00),
(140, 82, 2, 2.00),
(141, 83, 2, 2.00),
(142, 86, 2, 2.00),
(143, 89, 2, 2.00),
(144, 96, 2, 2.00),
(145, 258, 2, 2.00),
(146, 273, 2, 2.00),
(147, 393, 2, 2.00),
(148, 397, 2, 2.00),
(149, 412, 2, 2.00),
(150, 415, 2, 2.00),
(151, 431, 2, 2.00),
(152, 433, 2, 2.00),
(153, 448, 2, 2.00),
(154, 449, 2, 2.00),
(155, 453, 2, 2.00),
(156, 459, 2, 2.00),
(157, 467, 2, 2.00),
(158, 484, 2, 2.00),
(159, 660, 2, 2.00),
(160, 684, 2, 2.00),
(161, 14, 2, 2.00),
(162, 72, 2, 2.00),
(163, 126, 2, 2.00),
(164, 152, 2, 2.00),
(165, 154, 2, 2.00),
(166, 190, 2, 2.00),
(167, 248, 2, 2.00),
(168, 260, 2, 2.00),
(169, 294, 2, 2.00),
(170, 344, 2, 2.00),
(171, 361, 2, 2.00),
(172, 470, 2, 2.00),
(173, 576, 2, 2.00),
(174, 593, 2, 2.00),
(175, 641, 2, 2.00),
(176, 693, 2, 2.00),
(177, 702, 2, 2.00),
(178, 16, 2, 2.00),
(179, 164, 2, 2.00),
(180, 174, 2, 2.00),
(181, 217, 2, 2.00),
(182, 223, 2, 2.00),
(183, 586, 2, 2.00),
(184, 650, 2, 2.00),
(185, 680, 2, 2.00),
(186, 18, 2, 2.00),
(187, 28, 2, 2.00),
(188, 30, 2, 2.00),
(189, 42, 2, 2.00),
(190, 48, 2, 2.00),
(191, 54, 2, 2.00),
(192, 61, 2, 2.00),
(193, 68, 2, 2.00),
(194, 159, 2, 2.00),
(195, 170, 2, 2.00),
(196, 198, 2, 2.00),
(197, 216, 2, 2.00),
(198, 230, 2, 2.00),
(199, 231, 2, 2.00),
(200, 237, 2, 2.00),
(201, 328, 2, 2.00),
(202, 353, 2, 2.00),
(203, 417, 2, 2.00),
(204, 427, 2, 2.00),
(205, 437, 2, 2.00),
(206, 450, 2, 2.00),
(207, 555, 2, 2.00),
(208, 557, 2, 2.00),
(209, 639, 2, 2.00),
(210, 648, 2, 2.00),
(211, 649, 2, 2.00),
(212, 678, 2, 2.00),
(213, 691, 2, 2.00),
(214, 696, 2, 2.00),
(215, 20, 2, 2.00),
(216, 24, 2, 2.00),
(217, 47, 2, 2.00),
(218, 50, 2, 2.00),
(219, 78, 2, 2.00),
(220, 87, 2, 2.00),
(221, 100, 2, 2.00),
(222, 109, 2, 2.00),
(223, 120, 2, 2.00),
(224, 123, 2, 2.00),
(225, 125, 2, 2.00),
(226, 140, 2, 2.00),
(227, 146, 2, 2.00),
(228, 151, 2, 2.00),
(229, 176, 2, 2.00),
(230, 178, 2, 2.00),
(231, 192, 2, 2.00),
(232, 227, 2, 2.00),
(233, 267, 2, 2.00),
(234, 271, 2, 2.00),
(235, 289, 2, 2.00),
(236, 303, 2, 2.00),
(237, 339, 2, 2.00),
(238, 348, 2, 2.00),
(239, 381, 2, 2.00),
(240, 387, 2, 2.00),
(241, 391, 2, 2.00),
(242, 396, 2, 2.00),
(243, 443, 2, 2.00),
(244, 468, 2, 2.00),
(245, 521, 2, 2.00),
(246, 543, 2, 2.00),
(247, 558, 2, 2.00),
(248, 562, 2, 2.00),
(249, 588, 2, 2.00),
(250, 592, 2, 2.00),
(251, 626, 2, 2.00),
(252, 669, 2, 2.00),
(253, 676, 2, 2.00),
(254, 21, 2, 2.00),
(255, 118, 2, 2.00),
(256, 22, 2, 2.00),
(257, 23, 2, 2.00),
(258, 31, 2, 2.00),
(259, 32, 2, 2.00),
(260, 38, 2, 2.00),
(261, 66, 2, 2.00),
(262, 76, 2, 2.00),
(263, 115, 2, 2.00),
(264, 129, 2, 2.00),
(265, 133, 2, 2.00),
(266, 137, 2, 2.00),
(267, 148, 2, 2.00),
(268, 160, 2, 2.00),
(269, 205, 2, 2.00),
(270, 224, 2, 2.00),
(271, 235, 2, 2.00),
(272, 264, 2, 2.00),
(273, 269, 2, 2.00),
(274, 399, 2, 2.00),
(275, 438, 2, 2.00),
(276, 440, 2, 2.00),
(277, 481, 2, 2.00),
(278, 536, 2, 2.00),
(279, 604, 2, 2.00),
(280, 607, 2, 2.00),
(281, 671, 2, 2.00),
(282, 677, 2, 2.00),
(283, 686, 2, 2.00),
(284, 689, 2, 2.00),
(285, 25, 2, 2.00),
(286, 33, 2, 2.00),
(287, 77, 2, 2.00),
(288, 101, 2, 2.00),
(289, 112, 2, 2.00),
(290, 147, 2, 2.00),
(291, 166, 2, 2.00),
(292, 183, 2, 2.00),
(293, 187, 2, 2.00),
(294, 234, 2, 2.00),
(295, 268, 2, 2.00),
(296, 276, 2, 2.00),
(297, 302, 2, 2.00),
(298, 338, 2, 2.00),
(299, 346, 2, 2.00),
(300, 350, 2, 2.00),
(301, 355, 2, 2.00),
(302, 370, 2, 2.00),
(303, 401, 2, 2.00),
(304, 461, 2, 2.00),
(305, 465, 2, 2.00),
(306, 507, 2, 2.00),
(307, 535, 2, 2.00),
(308, 539, 2, 2.00),
(309, 540, 2, 2.00),
(310, 542, 2, 2.00),
(311, 580, 2, 2.00),
(312, 583, 2, 2.00),
(313, 618, 2, 2.00),
(314, 644, 2, 2.00),
(315, 663, 2, 2.00),
(316, 675, 2, 2.00),
(317, 26, 2, 2.00),
(318, 44, 2, 2.00),
(319, 67, 2, 2.00),
(320, 149, 2, 2.00),
(321, 295, 2, 2.00),
(322, 452, 2, 2.00),
(323, 651, 2, 2.00),
(324, 658, 2, 2.00),
(325, 27, 2, 2.00),
(326, 40, 2, 2.00),
(327, 46, 2, 2.00),
(328, 145, 2, 2.00),
(329, 184, 2, 2.00),
(330, 189, 2, 2.00),
(331, 211, 2, 2.00),
(332, 218, 2, 2.00),
(333, 222, 2, 2.00),
(334, 228, 2, 2.00),
(335, 249, 2, 2.00),
(336, 253, 2, 2.00),
(337, 255, 2, 2.00),
(338, 265, 2, 2.00),
(339, 266, 2, 2.00),
(340, 274, 2, 2.00),
(341, 279, 2, 2.00),
(342, 283, 2, 2.00),
(343, 296, 2, 2.00),
(344, 300, 2, 2.00),
(345, 304, 2, 2.00),
(346, 309, 2, 2.00),
(347, 310, 2, 2.00),
(348, 311, 2, 2.00),
(349, 316, 2, 2.00),
(350, 317, 2, 2.00),
(351, 318, 2, 2.00),
(352, 323, 2, 2.00),
(353, 340, 2, 2.00),
(354, 341, 2, 2.00),
(355, 349, 2, 2.00),
(356, 357, 2, 2.00),
(357, 358, 2, 2.00),
(358, 360, 2, 2.00),
(359, 367, 2, 2.00),
(360, 368, 2, 2.00),
(361, 369, 2, 2.00),
(362, 371, 2, 2.00),
(363, 373, 2, 2.00),
(364, 377, 2, 2.00),
(365, 402, 2, 2.00),
(366, 406, 2, 2.00),
(367, 407, 2, 2.00),
(368, 410, 2, 2.00),
(369, 413, 2, 2.00),
(370, 418, 2, 2.00),
(371, 429, 2, 2.00),
(372, 435, 2, 2.00),
(373, 439, 2, 2.00),
(374, 444, 2, 2.00),
(375, 447, 2, 2.00),
(376, 471, 2, 2.00),
(377, 472, 2, 2.00),
(378, 473, 2, 2.00),
(379, 482, 2, 2.00),
(380, 489, 2, 2.00),
(381, 490, 2, 2.00),
(382, 491, 2, 2.00),
(383, 493, 2, 2.00),
(384, 494, 2, 2.00),
(385, 495, 2, 2.00),
(386, 497, 2, 2.00),
(387, 500, 2, 2.00),
(388, 501, 2, 2.00),
(389, 504, 2, 2.00),
(390, 513, 2, 2.00),
(391, 515, 2, 2.00),
(392, 518, 2, 2.00),
(393, 519, 2, 2.00),
(394, 520, 2, 2.00),
(395, 523, 2, 2.00),
(396, 524, 2, 2.00),
(397, 525, 2, 2.00),
(398, 530, 2, 2.00),
(399, 531, 2, 2.00),
(400, 546, 2, 2.00),
(401, 547, 2, 2.00),
(402, 548, 2, 2.00),
(403, 551, 2, 2.00),
(404, 552, 2, 2.00),
(405, 573, 2, 2.00),
(406, 577, 2, 2.00),
(407, 589, 2, 2.00),
(408, 596, 2, 2.00),
(409, 598, 2, 2.00),
(410, 601, 2, 2.00),
(411, 608, 2, 2.00),
(412, 610, 2, 2.00),
(413, 617, 2, 2.00),
(414, 628, 2, 2.00),
(415, 629, 2, 2.00),
(416, 631, 2, 2.00),
(417, 640, 2, 2.00),
(418, 643, 2, 2.00),
(419, 647, 2, 2.00),
(420, 654, 2, 2.00),
(421, 657, 2, 2.00),
(422, 661, 2, 2.00),
(423, 664, 2, 2.00),
(424, 707, 2, 2.00),
(425, 34, 2, 2.00),
(426, 37, 2, 2.00),
(427, 39, 2, 2.00),
(428, 69, 2, 2.00),
(429, 81, 2, 2.00),
(430, 84, 2, 2.00),
(431, 104, 2, 2.00),
(432, 108, 2, 2.00),
(433, 116, 2, 2.00),
(434, 119, 2, 2.00),
(435, 167, 2, 2.00),
(436, 172, 2, 2.00),
(437, 180, 2, 2.00),
(438, 191, 2, 2.00),
(439, 203, 2, 2.00),
(440, 226, 2, 2.00),
(441, 233, 2, 2.00),
(442, 236, 2, 2.00),
(443, 239, 2, 2.00),
(444, 243, 2, 2.00),
(445, 262, 2, 2.00),
(446, 277, 2, 2.00),
(447, 284, 2, 2.00),
(448, 287, 2, 2.00),
(449, 288, 2, 2.00),
(450, 291, 2, 2.00),
(451, 307, 2, 2.00),
(452, 322, 2, 2.00),
(453, 335, 2, 2.00),
(454, 336, 2, 2.00),
(455, 337, 2, 2.00),
(456, 342, 2, 2.00),
(457, 359, 2, 2.00),
(458, 442, 2, 2.00),
(459, 474, 2, 2.00),
(460, 487, 2, 2.00),
(461, 488, 2, 2.00),
(462, 506, 2, 2.00),
(463, 538, 2, 2.00),
(464, 549, 2, 2.00),
(465, 582, 2, 2.00),
(466, 594, 2, 2.00),
(467, 602, 2, 2.00),
(468, 630, 2, 2.00),
(469, 637, 2, 2.00),
(470, 653, 2, 2.00),
(471, 659, 2, 2.00),
(472, 668, 2, 2.00),
(473, 674, 2, 2.00),
(474, 679, 2, 2.00),
(475, 681, 2, 2.00),
(476, 694, 2, 2.00),
(477, 697, 2, 2.00),
(478, 709, 2, 2.00),
(479, 35, 2, 2.00),
(480, 55, 2, 2.00),
(481, 97, 2, 2.00),
(482, 221, 2, 2.00),
(483, 241, 2, 2.00),
(484, 411, 2, 2.00),
(485, 454, 2, 2.00),
(486, 692, 2, 2.00),
(487, 36, 2, 2.00),
(488, 53, 2, 2.00),
(489, 64, 2, 2.00),
(490, 106, 2, 2.00),
(491, 139, 2, 2.00),
(492, 141, 2, 2.00),
(493, 173, 2, 2.00),
(494, 207, 2, 2.00),
(495, 210, 2, 2.00),
(496, 212, 2, 2.00),
(497, 215, 2, 2.00),
(498, 219, 2, 2.00),
(499, 229, 2, 2.00),
(500, 259, 2, 2.00),
(501, 290, 2, 2.00),
(502, 313, 2, 2.00),
(503, 332, 2, 2.00),
(504, 334, 2, 2.00),
(505, 376, 2, 2.00),
(506, 380, 2, 2.00),
(507, 389, 2, 2.00),
(508, 395, 2, 2.00),
(509, 400, 2, 2.00),
(510, 409, 2, 2.00),
(511, 502, 2, 2.00),
(512, 508, 2, 2.00),
(513, 544, 2, 2.00),
(514, 579, 2, 2.00),
(515, 609, 2, 2.00),
(516, 613, 2, 2.00),
(517, 687, 2, 2.00),
(518, 708, 2, 2.00),
(519, 45, 2, 2.00),
(520, 158, 2, 2.00),
(521, 247, 2, 2.00),
(522, 315, 2, 2.00),
(523, 331, 2, 2.00),
(524, 345, 2, 2.00),
(525, 365, 2, 2.00),
(526, 382, 2, 2.00),
(527, 383, 2, 2.00),
(528, 405, 2, 2.00),
(529, 421, 2, 2.00),
(530, 424, 2, 2.00),
(531, 463, 2, 2.00),
(532, 480, 2, 2.00),
(533, 533, 2, 2.00),
(534, 545, 2, 2.00),
(535, 587, 2, 2.00),
(536, 625, 2, 2.00),
(537, 634, 2, 2.00),
(538, 49, 2, 2.00),
(539, 91, 2, 2.00),
(540, 92, 2, 2.00),
(541, 110, 2, 2.00),
(542, 111, 2, 2.00),
(543, 135, 2, 2.00),
(544, 153, 2, 2.00),
(545, 162, 2, 2.00),
(546, 169, 2, 2.00),
(547, 194, 2, 2.00),
(548, 324, 2, 2.00),
(549, 414, 2, 2.00),
(550, 578, 2, 2.00),
(551, 627, 2, 2.00),
(552, 683, 2, 2.00),
(553, 51, 2, 2.00),
(554, 121, 2, 2.00),
(555, 232, 2, 2.00),
(556, 441, 2, 2.00),
(557, 56, 2, 2.00),
(558, 73, 2, 2.00),
(559, 90, 2, 2.00),
(560, 107, 2, 2.00),
(561, 199, 2, 2.00),
(562, 220, 2, 2.00),
(563, 292, 2, 2.00),
(564, 314, 2, 2.00),
(565, 343, 2, 2.00),
(566, 364, 2, 2.00),
(567, 479, 2, 2.00),
(568, 485, 2, 2.00),
(569, 556, 2, 2.00),
(570, 565, 2, 2.00),
(571, 572, 2, 2.00),
(572, 591, 2, 2.00),
(573, 62, 2, 2.00),
(574, 74, 2, 2.00),
(575, 99, 2, 2.00),
(576, 462, 2, 2.00),
(577, 466, 2, 2.00),
(578, 475, 2, 2.00),
(579, 534, 2, 2.00),
(580, 568, 2, 2.00),
(581, 65, 2, 2.00),
(582, 85, 2, 2.00),
(583, 102, 2, 2.00),
(584, 113, 2, 2.00),
(585, 114, 2, 2.00),
(586, 122, 2, 2.00),
(587, 130, 2, 2.00),
(588, 131, 2, 2.00),
(589, 134, 2, 2.00),
(590, 188, 2, 2.00),
(591, 246, 2, 2.00),
(592, 257, 2, 2.00),
(593, 275, 2, 2.00),
(594, 282, 2, 2.00),
(595, 286, 2, 2.00),
(596, 347, 2, 2.00),
(597, 379, 2, 2.00),
(598, 419, 2, 2.00),
(599, 420, 2, 2.00),
(600, 423, 2, 2.00),
(601, 428, 2, 2.00),
(602, 430, 2, 2.00),
(603, 432, 2, 2.00),
(604, 457, 2, 2.00),
(605, 464, 2, 2.00),
(606, 469, 2, 2.00),
(607, 476, 2, 2.00),
(608, 621, 2, 2.00),
(609, 70, 2, 2.00),
(610, 200, 2, 2.00),
(611, 75, 2, 2.00),
(612, 93, 2, 2.00),
(613, 204, 2, 2.00),
(614, 270, 2, 2.00),
(615, 308, 2, 2.00),
(616, 404, 2, 2.00),
(617, 426, 2, 2.00),
(618, 456, 2, 2.00),
(619, 529, 2, 2.00),
(620, 79, 2, 2.00),
(621, 95, 2, 2.00),
(622, 98, 2, 2.00),
(623, 138, 2, 2.00),
(624, 165, 2, 2.00),
(625, 171, 2, 2.00),
(626, 195, 2, 2.00),
(627, 386, 2, 2.00),
(628, 605, 2, 2.00),
(629, 614, 2, 2.00),
(630, 105, 2, 2.00),
(631, 196, 2, 2.00),
(632, 214, 2, 2.00),
(633, 272, 2, 2.00),
(634, 297, 2, 2.00),
(635, 298, 2, 2.00),
(636, 305, 2, 2.00),
(637, 306, 2, 2.00),
(638, 312, 2, 2.00),
(639, 385, 2, 2.00),
(640, 451, 2, 2.00),
(641, 527, 2, 2.00),
(642, 554, 2, 2.00),
(643, 560, 2, 2.00),
(644, 567, 2, 2.00),
(645, 575, 2, 2.00),
(646, 600, 2, 2.00),
(647, 606, 2, 2.00),
(648, 611, 2, 2.00),
(649, 638, 2, 2.00),
(650, 645, 2, 2.00),
(651, 117, 2, 2.00),
(652, 136, 2, 2.00),
(653, 532, 2, 2.00),
(654, 655, 2, 2.00),
(655, 698, 2, 2.00),
(656, 701, 2, 2.00),
(657, 124, 2, 2.00),
(658, 150, 2, 2.00),
(659, 179, 2, 2.00),
(660, 375, 2, 2.00),
(661, 517, 2, 2.00),
(662, 624, 2, 2.00),
(663, 688, 2, 2.00),
(664, 703, 2, 2.00),
(665, 705, 2, 2.00),
(666, 706, 2, 2.00),
(667, 128, 2, 2.00),
(668, 250, 2, 2.00),
(669, 528, 2, 2.00),
(670, 157, 2, 2.00),
(671, 238, 2, 2.00),
(672, 571, 2, 2.00),
(673, 168, 2, 2.00),
(674, 242, 2, 2.00),
(675, 181, 2, 2.00),
(676, 225, 2, 2.00),
(677, 281, 2, 2.00),
(678, 333, 2, 2.00),
(679, 553, 2, 2.00),
(680, 559, 2, 2.00),
(681, 584, 2, 2.00),
(682, 620, 2, 2.00),
(683, 636, 2, 2.00),
(684, 656, 2, 2.00),
(685, 690, 2, 2.00),
(686, 208, 2, 2.00),
(687, 280, 2, 2.00),
(688, 561, 2, 2.00),
(689, 622, 2, 2.00),
(690, 652, 2, 2.00),
(691, 695, 2, 2.00),
(692, 209, 2, 2.00),
(693, 293, 2, 2.00),
(694, 330, 2, 2.00),
(695, 352, 2, 2.00),
(696, 455, 2, 2.00),
(697, 477, 2, 2.00),
(698, 478, 2, 2.00),
(699, 619, 2, 2.00),
(700, 499, 2, 2.00),
(701, 526, 2, 2.00),
(702, 623, 2, 2.00),
(703, 667, 2, 2.00),
(704, 673, 2, 2.00),
(705, 662, 2, 2.00),
(706, 699, 2, 2.00);

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
(3, 1, 'Français', 'FRAN'),
(4, 4, 'Cours de démonstration', 'TEST-PRESENCES-'),
(10, 4, 'HISTOIRE', 'HIST1'),
(11, 4, 'Comptabilité', 'CMPT1'),
(22, 4, 'Géographie', 'GEO1'),
(25, 4, 'Maladies Infectieuses', 'MALINF1'),
(26, 4, 'Documents Commerciaux', 'DOCOM1'),
(27, 4, 'Droit Fiscal', 'DF3'),
(28, 4, 'Math Financière', 'MATFIN1'),
(29, 4, 'Economie Financière', 'ECOFIN'),
(30, 4, 'INFORMATIQUE', 'INFO1');

-- --------------------------------------------------------

--
-- Table structure for table `demandes_abonnement_ecoles`
--

CREATE TABLE `demandes_abonnement_ecoles` (
  `id` int(10) UNSIGNED NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `demandeur_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `type_demande` enum('nouvel_abonnement','renouvellement') NOT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `statut` enum('En_attente','Approuvee','Refusee') NOT NULL DEFAULT 'En_attente',
  `traite_par` int(11) DEFAULT NULL,
  `traite_le` datetime DEFAULT NULL,
  `reponse` varchar(1000) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `derogations`
--

CREATE TABLE `derogations` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `date_fin` date NOT NULL,
  `motif` varchar(500) NOT NULL,
  `statut` enum('En_attente','Approuvee','Refusee') NOT NULL DEFAULT 'En_attente',
  `commentaire` varchar(500) DEFAULT NULL,
  `demandeur_id` int(11) NOT NULL,
  `validateur_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_at` timestamp NULL DEFAULT NULL,
  `demandeur_message_lu` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `derogations`
--

INSERT INTO `derogations` (`id`, `ecole_id`, `eleve_id`, `date_fin`, `motif`, `statut`, `commentaire`, `demandeur_id`, `validateur_id`, `created_at`, `decided_at`, `demandeur_message_lu`) VALUES
(1, 4, 616, '2026-10-25', 'le parent a appelé', 'Approuvee', NULL, 170, 165, '2026-10-21 22:07:04', '2026-10-21 22:14:48', 1),
(2, 4, 96, '2026-10-31', 'elle paye à la fin du mois', 'En_attente', NULL, 170, NULL, '2026-10-29 14:13:13', NULL, 1),
(3, 4, 10, '2026-10-31', 'Je vais payer à la fin du mois', 'Refusee', NULL, 163, 165, '2026-10-29 15:32:55', '2026-10-29 16:03:18', 1);

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

--
-- Dumping data for table `dettes_eleves`
--

INSERT INTO `dettes_eleves` (`id`, `eleve_id`, `frais_id`, `annee_scolaire_id`, `montant_initial`, `montant_restant`, `devise`, `date_creation`) VALUES
(1, 9, 2, 1, 45.00, 8.00, 'USD', '2026-08-09 16:48:30'),
(2, 9, 2, 1, 45.00, 10.00, 'USD', '2026-08-09 17:02:51'),
(3, 10, 14, 2, 112.00, 2.00, 'USD', '2026-08-26 23:04:06'),
(4, 10, 6, 2, 135.00, 90.00, 'USD', '2026-08-30 07:10:37'),
(5, 10, 11, 2, 15.00, 0.00, 'USD', '2026-09-21 17:12:22'),
(6, 11, 6, 2, 135.00, 108.50, 'USD', '2026-10-16 19:34:58'),
(7, 11, 11, 2, 15.00, 5.00, 'USD', '2026-10-16 19:45:40'),
(8, 9, 11, 2, 15.00, 2.00, 'USD', '2026-10-16 20:06:34'),
(9, 143, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(10, 240, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(11, 496, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(12, 503, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(13, 509, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(14, 550, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(15, 642, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(16, 670, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(17, 263, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(18, 327, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(19, 278, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(20, 285, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(21, 403, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(22, 408, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(23, 590, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(24, 299, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(25, 566, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(26, 354, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(27, 372, 1, 2, 24000.00, 24000.00, 'CDF', '2026-10-20 18:55:13'),
(28, 616, 6, 2, 135.00, 125.00, 'USD', '2026-10-20 19:07:22'),
(29, 425, 6, 2, 135.00, 120.00, 'USD', '2026-10-20 21:29:23'),
(30, 710, 5, 2, 24000.00, 24000.00, 'CDF', '2026-10-21 21:31:40'),
(31, 710, 8, 2, 70.00, 70.00, 'USD', '2026-10-21 21:31:40'),
(32, 710, 11, 2, 15.00, 15.00, 'USD', '2026-10-21 21:31:40'),
(33, 325, 6, 2, 135.00, 65.00, 'USD', '2026-10-22 10:32:55'),
(34, 10, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(35, 29, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(36, 71, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(37, 80, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(38, 88, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(39, 103, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(40, 142, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(41, 144, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(42, 163, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(43, 185, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(44, 193, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(45, 201, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(46, 202, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(47, 244, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(48, 245, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(49, 251, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(50, 252, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(51, 254, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(52, 256, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(53, 261, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(54, 319, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(55, 320, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(56, 321, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(57, 325, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(58, 326, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(59, 329, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(60, 351, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(61, 356, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(62, 363, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(63, 366, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(64, 374, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(65, 378, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(66, 384, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(67, 388, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(68, 392, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(69, 394, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(70, 398, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(71, 416, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(72, 422, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(73, 434, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(74, 436, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(75, 445, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(76, 446, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(77, 458, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(78, 483, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(79, 492, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(80, 498, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(81, 505, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(82, 510, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(83, 511, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(84, 512, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(85, 514, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(86, 522, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(87, 541, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(88, 563, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(89, 569, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(90, 570, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(91, 574, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(92, 585, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(93, 595, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(94, 603, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(95, 612, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(96, 615, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(97, 616, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(98, 632, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(99, 633, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(100, 635, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(101, 646, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(102, 665, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(103, 666, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(104, 682, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(105, 685, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(106, 700, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(107, 704, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(108, 11, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(109, 19, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(110, 60, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(111, 197, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(112, 390, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(113, 425, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(114, 597, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(115, 672, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(116, 12, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(117, 15, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(118, 17, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(119, 43, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(120, 59, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(121, 94, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(122, 127, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(123, 132, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(124, 155, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(125, 156, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(126, 161, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(127, 175, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(128, 177, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(129, 182, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(130, 186, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(131, 206, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(132, 213, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(133, 301, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(134, 362, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(135, 460, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(136, 486, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(137, 516, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(138, 537, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(139, 564, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(140, 581, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(141, 599, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(142, 13, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(143, 41, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(144, 52, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(145, 57, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(146, 58, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(147, 63, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(148, 82, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(149, 83, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(150, 86, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(151, 89, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(152, 96, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(153, 258, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(154, 273, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(155, 393, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(156, 397, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(157, 412, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(158, 415, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(159, 431, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(160, 433, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(161, 448, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(162, 449, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(163, 453, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(164, 459, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(165, 467, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(166, 484, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(167, 660, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(168, 684, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(169, 14, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(170, 72, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(171, 126, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(172, 152, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(173, 154, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(174, 190, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(175, 248, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(176, 260, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(177, 294, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(178, 344, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(179, 361, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(180, 470, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(181, 576, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(182, 593, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(183, 641, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(184, 693, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(185, 702, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(186, 16, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(187, 164, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(188, 174, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(189, 217, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(190, 223, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(191, 586, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(192, 650, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(193, 680, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(194, 18, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(195, 28, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(196, 30, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(197, 42, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(198, 48, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(199, 54, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(200, 61, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(201, 68, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(202, 159, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(203, 170, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(204, 198, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(205, 216, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(206, 230, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(207, 231, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(208, 237, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(209, 328, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(210, 353, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(211, 417, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(212, 427, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(213, 437, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(214, 450, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(215, 555, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(216, 557, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(217, 639, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(218, 648, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(219, 649, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(220, 678, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(221, 691, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(222, 696, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(223, 20, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(224, 24, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(225, 47, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(226, 50, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(227, 78, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(228, 87, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(229, 100, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(230, 109, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(231, 120, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(232, 123, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(233, 125, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(234, 140, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(235, 146, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(236, 151, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(237, 176, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(238, 178, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(239, 192, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(240, 227, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(241, 267, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(242, 271, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(243, 289, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(244, 303, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(245, 339, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(246, 348, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(247, 381, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(248, 387, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(249, 391, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(250, 396, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(251, 443, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(252, 468, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(253, 521, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(254, 543, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(255, 558, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(256, 562, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(257, 588, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(258, 592, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(259, 626, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(260, 669, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(261, 676, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(262, 21, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(263, 118, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(264, 22, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(265, 23, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(266, 31, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(267, 32, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(268, 38, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(269, 66, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(270, 76, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(271, 115, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(272, 129, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(273, 133, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(274, 137, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(275, 148, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(276, 160, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(277, 205, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(278, 224, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(279, 235, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(280, 264, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(281, 269, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(282, 399, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(283, 438, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(284, 440, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(285, 481, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(286, 536, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(287, 604, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(288, 607, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(289, 671, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(290, 677, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(291, 686, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(292, 689, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(293, 25, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(294, 33, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(295, 77, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(296, 101, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(297, 112, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(298, 147, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(299, 166, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(300, 183, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(301, 187, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(302, 234, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(303, 268, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(304, 276, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(305, 302, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(306, 338, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(307, 346, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(308, 350, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(309, 355, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(310, 370, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(311, 401, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(312, 461, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(313, 465, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(314, 507, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(315, 535, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(316, 539, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(317, 540, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(318, 542, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(319, 580, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(320, 583, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(321, 618, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(322, 644, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(323, 663, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(324, 675, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(325, 26, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(326, 44, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(327, 67, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(328, 149, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(329, 295, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(330, 452, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(331, 651, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(332, 658, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(333, 27, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(334, 40, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(335, 46, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(336, 145, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(337, 184, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(338, 189, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(339, 211, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(340, 218, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(341, 222, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(342, 228, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(343, 249, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(344, 253, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(345, 255, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(346, 265, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(347, 266, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(348, 274, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(349, 279, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(350, 283, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(351, 296, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(352, 300, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(353, 304, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(354, 309, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(355, 310, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(356, 311, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(357, 316, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(358, 317, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(359, 318, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(360, 323, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(361, 340, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(362, 341, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(363, 349, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(364, 357, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(365, 358, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(366, 360, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(367, 367, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(368, 368, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(369, 369, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(370, 371, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(371, 373, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(372, 377, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(373, 402, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(374, 406, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(375, 407, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(376, 410, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(377, 413, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(378, 418, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(379, 429, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(380, 435, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(381, 439, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(382, 444, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(383, 447, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(384, 471, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(385, 472, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(386, 473, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(387, 482, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(388, 489, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(389, 490, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(390, 491, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(391, 493, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(392, 494, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(393, 495, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(394, 497, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(395, 500, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(396, 501, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(397, 504, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(398, 513, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(399, 515, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(400, 518, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(401, 519, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(402, 520, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(403, 523, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(404, 524, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(405, 525, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(406, 530, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(407, 531, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(408, 546, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(409, 547, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(410, 548, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(411, 551, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(412, 552, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(413, 573, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(414, 577, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(415, 589, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(416, 596, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(417, 598, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(418, 601, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(419, 608, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(420, 610, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(421, 617, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(422, 628, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(423, 629, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(424, 631, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(425, 640, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(426, 643, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(427, 647, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(428, 654, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(429, 657, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(430, 661, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(431, 664, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(432, 707, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(433, 710, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(434, 34, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(435, 37, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(436, 39, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(437, 69, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(438, 81, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(439, 84, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(440, 104, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(441, 108, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(442, 116, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(443, 119, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(444, 167, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(445, 172, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(446, 180, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(447, 191, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(448, 203, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(449, 226, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(450, 233, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(451, 236, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(452, 239, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(453, 243, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(454, 262, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(455, 277, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(456, 284, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(457, 287, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(458, 288, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(459, 291, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(460, 307, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(461, 322, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(462, 335, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(463, 336, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(464, 337, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(465, 342, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(466, 359, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(467, 442, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(468, 474, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(469, 487, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(470, 488, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(471, 506, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(472, 538, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(473, 549, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(474, 582, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(475, 594, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(476, 602, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(477, 630, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(478, 637, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(479, 653, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(480, 659, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(481, 668, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(482, 674, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(483, 679, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(484, 681, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(485, 694, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(486, 697, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(487, 709, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(488, 35, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(489, 55, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(490, 97, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(491, 221, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:30'),
(492, 241, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(493, 411, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(494, 454, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(495, 692, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(496, 36, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(497, 53, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(498, 64, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(499, 106, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(500, 139, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(501, 141, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(502, 173, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(503, 207, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(504, 210, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(505, 212, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(506, 215, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(507, 219, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(508, 229, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(509, 259, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(510, 290, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(511, 313, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(512, 332, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(513, 334, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(514, 376, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(515, 380, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(516, 389, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(517, 395, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(518, 400, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(519, 409, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(520, 502, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(521, 508, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(522, 544, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(523, 579, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(524, 609, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(525, 613, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(526, 687, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(527, 708, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(528, 45, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(529, 158, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(530, 247, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(531, 315, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(532, 331, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(533, 345, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(534, 365, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(535, 382, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(536, 383, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(537, 405, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(538, 421, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(539, 424, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(540, 463, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(541, 480, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(542, 533, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(543, 545, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(544, 587, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(545, 625, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(546, 634, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(547, 49, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(548, 91, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(549, 92, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(550, 110, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(551, 111, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(552, 135, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(553, 153, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(554, 162, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(555, 169, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(556, 194, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(557, 324, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(558, 414, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(559, 578, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(560, 627, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(561, 683, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(562, 51, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(563, 121, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(564, 232, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(565, 441, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(566, 56, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(567, 73, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(568, 90, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(569, 107, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(570, 199, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(571, 220, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(572, 292, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(573, 314, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(574, 343, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(575, 364, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(576, 479, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(577, 485, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(578, 556, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(579, 565, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(580, 572, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(581, 591, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(582, 62, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(583, 74, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(584, 99, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(585, 462, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(586, 466, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(587, 475, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(588, 534, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(589, 568, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(590, 65, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(591, 85, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(592, 102, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(593, 113, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(594, 114, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(595, 122, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(596, 130, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(597, 131, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(598, 134, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(599, 188, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(600, 246, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(601, 257, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(602, 275, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(603, 282, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(604, 286, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(605, 347, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(606, 379, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(607, 419, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(608, 420, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(609, 423, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(610, 428, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(611, 430, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(612, 432, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(613, 457, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(614, 464, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(615, 469, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(616, 476, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(617, 621, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(618, 70, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(619, 200, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(620, 75, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(621, 93, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(622, 204, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(623, 270, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(624, 308, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(625, 404, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(626, 426, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(627, 456, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(628, 529, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(629, 79, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(630, 95, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(631, 98, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(632, 138, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(633, 165, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(634, 171, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(635, 195, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(636, 386, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(637, 605, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(638, 614, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(639, 105, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(640, 196, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(641, 214, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(642, 272, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(643, 297, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(644, 298, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(645, 305, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(646, 306, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(647, 312, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(648, 385, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(649, 451, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(650, 527, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(651, 554, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(652, 560, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(653, 567, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(654, 575, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(655, 600, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(656, 606, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(657, 611, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(658, 638, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(659, 645, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(660, 117, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(661, 136, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(662, 532, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(663, 655, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(664, 698, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(665, 701, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(666, 124, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(667, 150, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(668, 179, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(669, 375, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(670, 517, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(671, 624, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(672, 688, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(673, 703, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(674, 705, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(675, 706, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(676, 128, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(677, 250, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(678, 528, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(679, 143, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(680, 240, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(681, 496, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(682, 503, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(683, 509, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(684, 550, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(685, 642, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(686, 670, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(687, 157, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(688, 238, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(689, 571, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(690, 168, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(691, 242, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(692, 181, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(693, 225, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(694, 281, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(695, 333, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(696, 553, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(697, 559, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(698, 584, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(699, 620, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(700, 636, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(701, 656, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(702, 690, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(703, 208, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(704, 280, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(705, 561, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(706, 622, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(707, 652, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(708, 695, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(709, 209, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(710, 263, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(711, 327, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(712, 278, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(713, 285, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(714, 403, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(715, 408, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(716, 590, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(717, 293, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(718, 299, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(719, 566, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(720, 330, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(721, 352, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(722, 354, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(723, 372, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(724, 455, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(725, 477, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(726, 478, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(727, 619, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(728, 499, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(729, 526, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(730, 623, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(731, 667, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(732, 673, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(733, 662, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(734, 699, 4, 2, 1.00, 1.00, 'USD', '2026-10-22 12:45:31'),
(735, 10, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(736, 29, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(737, 71, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(738, 80, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(739, 88, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(740, 103, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(741, 142, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(742, 144, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(743, 163, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(744, 185, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(745, 193, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(746, 201, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(747, 202, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(748, 244, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(749, 245, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(750, 251, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(751, 252, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(752, 254, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(753, 256, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(754, 261, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(755, 319, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(756, 320, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(757, 321, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(758, 325, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(759, 326, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(760, 329, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(761, 351, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(762, 356, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(763, 363, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(764, 366, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(765, 374, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(766, 378, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(767, 384, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(768, 388, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(769, 392, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(770, 394, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(771, 398, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(772, 416, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(773, 422, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(774, 434, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(775, 436, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(776, 445, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(777, 446, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(778, 458, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(779, 483, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(780, 492, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(781, 498, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(782, 505, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(783, 510, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(784, 511, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(785, 512, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(786, 514, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(787, 522, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(788, 541, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(789, 563, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(790, 569, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(791, 570, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(792, 574, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(793, 585, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(794, 595, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(795, 603, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(796, 612, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(797, 615, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(798, 616, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(799, 632, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(800, 633, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(801, 635, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(802, 646, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(803, 665, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(804, 666, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:43'),
(805, 682, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(806, 685, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(807, 700, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(808, 704, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(809, 11, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(810, 19, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(811, 60, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(812, 197, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(813, 390, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(814, 425, 15, 2, 1.00, 0.00, 'USD', '2026-10-22 13:35:44'),
(815, 597, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(816, 672, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(817, 12, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(818, 15, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(819, 17, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(820, 43, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(821, 59, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(822, 94, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(823, 127, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(824, 132, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(825, 155, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(826, 156, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(827, 161, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(828, 175, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(829, 177, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(830, 182, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(831, 186, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(832, 206, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(833, 213, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(834, 301, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(835, 362, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(836, 460, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(837, 486, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(838, 516, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(839, 537, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(840, 564, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(841, 581, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(842, 599, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(843, 13, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(844, 41, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(845, 52, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(846, 57, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(847, 58, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(848, 63, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(849, 82, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(850, 83, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(851, 86, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(852, 89, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(853, 96, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(854, 258, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(855, 273, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(856, 393, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(857, 397, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44');
INSERT INTO `dettes_eleves` (`id`, `eleve_id`, `frais_id`, `annee_scolaire_id`, `montant_initial`, `montant_restant`, `devise`, `date_creation`) VALUES
(858, 412, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(859, 415, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(860, 431, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(861, 433, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(862, 448, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(863, 449, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(864, 453, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(865, 459, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(866, 467, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(867, 484, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(868, 660, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(869, 684, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(870, 14, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(871, 72, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(872, 126, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(873, 152, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(874, 154, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(875, 190, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(876, 248, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(877, 260, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(878, 294, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(879, 344, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(880, 361, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(881, 470, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(882, 576, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(883, 593, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(884, 641, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(885, 693, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(886, 702, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(887, 16, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(888, 164, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(889, 174, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(890, 217, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(891, 223, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(892, 586, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(893, 650, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(894, 680, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(895, 18, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(896, 28, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(897, 30, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(898, 42, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(899, 48, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(900, 54, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(901, 61, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(902, 68, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(903, 159, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(904, 170, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(905, 198, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(906, 216, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(907, 230, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(908, 231, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(909, 237, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(910, 328, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(911, 353, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(912, 417, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(913, 427, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(914, 437, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(915, 450, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(916, 555, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(917, 557, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(918, 639, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(919, 648, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(920, 649, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(921, 678, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(922, 691, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(923, 696, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(924, 20, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(925, 24, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(926, 47, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(927, 50, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(928, 78, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(929, 87, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(930, 100, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(931, 109, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(932, 120, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(933, 123, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(934, 125, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(935, 140, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(936, 146, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(937, 151, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(938, 176, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(939, 178, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(940, 192, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(941, 227, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(942, 267, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(943, 271, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(944, 289, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(945, 303, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(946, 339, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(947, 348, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(948, 381, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(949, 387, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(950, 391, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(951, 396, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(952, 443, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(953, 468, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(954, 521, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(955, 543, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(956, 558, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(957, 562, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(958, 588, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(959, 592, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(960, 626, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(961, 669, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(962, 676, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(963, 21, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(964, 118, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(965, 22, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(966, 23, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(967, 31, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(968, 32, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(969, 38, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(970, 66, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(971, 76, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(972, 115, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(973, 129, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(974, 133, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(975, 137, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(976, 148, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(977, 160, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(978, 205, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(979, 224, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(980, 235, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(981, 264, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(982, 269, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(983, 399, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(984, 438, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(985, 440, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(986, 481, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(987, 536, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(988, 604, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(989, 607, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(990, 671, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(991, 677, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(992, 686, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(993, 689, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(994, 25, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(995, 33, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(996, 77, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(997, 101, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(998, 112, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(999, 147, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1000, 166, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1001, 183, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1002, 187, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1003, 234, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1004, 268, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1005, 276, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1006, 302, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1007, 338, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1008, 346, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1009, 350, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1010, 355, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1011, 370, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1012, 401, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1013, 461, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1014, 465, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1015, 507, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1016, 535, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1017, 539, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1018, 540, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1019, 542, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1020, 580, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1021, 583, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1022, 618, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1023, 644, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1024, 663, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1025, 675, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1026, 26, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1027, 44, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1028, 67, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1029, 149, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1030, 295, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1031, 452, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1032, 651, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1033, 658, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1034, 27, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1035, 40, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1036, 46, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1037, 145, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1038, 184, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1039, 189, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1040, 211, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1041, 218, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1042, 222, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1043, 228, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1044, 249, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1045, 253, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1046, 255, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1047, 265, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1048, 266, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1049, 274, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1050, 279, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1051, 283, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1052, 296, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1053, 300, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1054, 304, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1055, 309, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1056, 310, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1057, 311, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1058, 316, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1059, 317, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1060, 318, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1061, 323, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1062, 340, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1063, 341, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1064, 349, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1065, 357, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1066, 358, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1067, 360, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1068, 367, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1069, 368, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1070, 369, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1071, 371, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1072, 373, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1073, 377, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1074, 402, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1075, 406, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1076, 407, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1077, 410, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1078, 413, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1079, 418, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1080, 429, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1081, 435, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1082, 439, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1083, 444, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1084, 447, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1085, 471, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1086, 472, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1087, 473, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1088, 482, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1089, 489, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1090, 490, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1091, 491, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1092, 493, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1093, 494, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1094, 495, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1095, 497, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1096, 500, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1097, 501, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1098, 504, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1099, 513, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1100, 515, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1101, 518, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1102, 519, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1103, 520, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1104, 523, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1105, 524, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1106, 525, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1107, 530, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1108, 531, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1109, 546, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1110, 547, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1111, 548, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1112, 551, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1113, 552, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1114, 573, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1115, 577, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1116, 589, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1117, 596, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1118, 598, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1119, 601, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1120, 608, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1121, 610, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1122, 617, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1123, 628, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1124, 629, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1125, 631, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1126, 640, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1127, 643, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1128, 647, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1129, 654, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1130, 657, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1131, 661, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1132, 664, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1133, 707, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1134, 710, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1135, 34, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1136, 37, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1137, 39, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1138, 69, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1139, 81, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1140, 84, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1141, 104, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1142, 108, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1143, 116, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1144, 119, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1145, 167, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1146, 172, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1147, 180, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1148, 191, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1149, 203, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1150, 226, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1151, 233, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1152, 236, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1153, 239, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1154, 243, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1155, 262, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1156, 277, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1157, 284, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1158, 287, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1159, 288, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1160, 291, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1161, 307, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1162, 322, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1163, 335, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1164, 336, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1165, 337, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1166, 342, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1167, 359, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1168, 442, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1169, 474, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1170, 487, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1171, 488, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1172, 506, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1173, 538, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1174, 549, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1175, 582, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1176, 594, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1177, 602, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1178, 630, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1179, 637, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1180, 653, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1181, 659, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1182, 668, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1183, 674, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1184, 679, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1185, 681, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1186, 694, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1187, 697, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1188, 709, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1189, 35, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1190, 55, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1191, 97, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1192, 221, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1193, 241, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1194, 411, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1195, 454, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1196, 692, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1197, 36, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1198, 53, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1199, 64, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1200, 106, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1201, 139, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1202, 141, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1203, 173, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1204, 207, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1205, 210, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1206, 212, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1207, 215, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1208, 219, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1209, 229, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1210, 259, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1211, 290, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1212, 313, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1213, 332, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1214, 334, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1215, 376, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1216, 380, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1217, 389, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1218, 395, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1219, 400, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1220, 409, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1221, 502, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1222, 508, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1223, 544, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1224, 579, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1225, 609, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1226, 613, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1227, 687, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1228, 708, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1229, 45, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1230, 158, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1231, 247, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1232, 315, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1233, 331, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1234, 345, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1235, 365, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1236, 382, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1237, 383, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1238, 405, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1239, 421, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1240, 424, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1241, 463, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1242, 480, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1243, 533, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1244, 545, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1245, 587, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1246, 625, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1247, 634, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1248, 49, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1249, 91, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1250, 92, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1251, 110, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1252, 111, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1253, 135, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1254, 153, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1255, 162, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1256, 169, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1257, 194, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1258, 324, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1259, 414, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1260, 578, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1261, 627, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1262, 683, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1263, 51, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1264, 121, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1265, 232, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1266, 441, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1267, 56, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1268, 73, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1269, 90, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1270, 107, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1271, 199, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1272, 220, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1273, 292, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1274, 314, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1275, 343, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1276, 364, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1277, 479, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1278, 485, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1279, 556, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1280, 565, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1281, 572, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1282, 591, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1283, 62, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1284, 74, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1285, 99, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1286, 462, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1287, 466, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1288, 475, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1289, 534, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1290, 568, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1291, 65, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1292, 85, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1293, 102, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1294, 113, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1295, 114, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1296, 122, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1297, 130, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1298, 131, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1299, 134, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1300, 188, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1301, 246, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1302, 257, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1303, 275, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1304, 282, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1305, 286, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1306, 347, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1307, 379, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1308, 419, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1309, 420, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1310, 423, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1311, 428, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1312, 430, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1313, 432, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1314, 457, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1315, 464, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1316, 469, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1317, 476, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1318, 621, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1319, 70, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1320, 200, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1321, 75, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1322, 93, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1323, 204, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1324, 270, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1325, 308, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1326, 404, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1327, 426, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1328, 456, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1329, 529, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1330, 79, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1331, 95, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1332, 98, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1333, 138, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1334, 165, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1335, 171, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1336, 195, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1337, 386, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1338, 605, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1339, 614, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1340, 105, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1341, 196, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1342, 214, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1343, 272, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1344, 297, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1345, 298, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1346, 305, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1347, 306, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1348, 312, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1349, 385, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1350, 451, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1351, 527, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1352, 554, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1353, 560, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1354, 567, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1355, 575, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1356, 600, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1357, 606, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1358, 611, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1359, 638, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1360, 645, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1361, 117, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1362, 136, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1363, 532, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1364, 655, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1365, 698, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1366, 701, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1367, 124, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1368, 150, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1369, 179, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1370, 375, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1371, 517, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1372, 624, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1373, 688, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1374, 703, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1375, 705, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1376, 706, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1377, 128, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1378, 250, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1379, 528, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1380, 143, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1381, 240, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1382, 496, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1383, 503, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1384, 509, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1385, 550, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1386, 642, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1387, 670, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1388, 157, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1389, 238, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1390, 571, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1391, 168, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1392, 242, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1393, 181, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1394, 225, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1395, 281, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1396, 333, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1397, 553, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1398, 559, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1399, 584, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1400, 620, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1401, 636, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1402, 656, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1403, 690, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1404, 208, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1405, 280, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1406, 561, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1407, 622, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1408, 652, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1409, 695, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1410, 209, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1411, 263, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1412, 327, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1413, 278, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1414, 285, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1415, 403, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1416, 408, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1417, 590, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1418, 293, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1419, 299, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1420, 566, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1421, 330, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1422, 352, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1423, 354, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1424, 372, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1425, 455, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1426, 477, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1427, 478, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1428, 619, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1429, 499, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1430, 526, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1431, 623, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1432, 667, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1433, 673, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1434, 662, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1435, 699, 15, 2, 1.00, 1.00, 'USD', '2026-10-22 13:35:44'),
(1436, 10, 16, 2, 1.00, 0.20, 'USD', '2026-10-29 15:09:27');

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
(4, 'XAF', 'Franc CFA BEAC', 0.001700, 0, '2026-07-04 22:33:35'),
(5, 'XOF', 'Franc CFA BCEAO', 0.001700, 0, '2026-07-04 22:33:35');

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
-- Table structure for table `ecole_derogation_recipients`
--

CREATE TABLE `ecole_derogation_recipients` (
  `ecole_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecole_derogation_recipient_settings`
--

CREATE TABLE `ecole_derogation_recipient_settings` (
  `ecole_id` int(11) NOT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecole_notification_access`
--

CREATE TABLE `ecole_notification_access` (
  `ecole_id` int(11) NOT NULL,
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecole_notification_access`
--

INSERT INTO `ecole_notification_access` (`ecole_id`, `sms_enabled`, `whatsapp_enabled`, `updated_by_user_id`, `updated_at`) VALUES
(4, 0, 1, 143, '2026-10-29 12:43:36');

-- --------------------------------------------------------

--
-- Table structure for table `ecole_notification_preferences`
--

CREATE TABLE `ecole_notification_preferences` (
  `ecole_id` int(11) NOT NULL,
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `agent_saisie_id` int(11) DEFAULT NULL,
  `utilisateur_saisie_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecritures_comptables_eleves`
--

INSERT INTO `ecritures_comptables_eleves` (`id`, `compte_eleve_id`, `frais_id`, `caisse_banque_id`, `type_mouvement`, `montant`, `reference_recu`, `date_operation`, `libelle`, `agent_saisie_id`, `utilisateur_saisie_id`) VALUES
(1, 1, 1, NULL, 'DEBIT', 150.00, NULL, '2026-06-23 15:03:23', 'Facturation Minerval 1er Trimestre', 3, NULL),
(2, 1, 1, 1, 'CREDIT', 100.00, 'REC-2026-001', '2026-06-23 15:03:23', 'Acompte Minerval perçu en espèces', 3, NULL),
(4, 3, 4, NULL, 'DEBIT', 1.00, NULL, '2026-07-04 23:30:18', 'Facturation inscription: FRAIS DE CONSTRUCTION', 1, NULL),
(6, 4, 4, NULL, 'CREDIT', 1.00, 'REC-20260705020036-706', '2026-07-05 00:00:36', 'FRAIS DE CONSTRUCTION - 1.00 USD', 1, NULL),
(7, 5, 2, NULL, 'CREDIT', 1.00, 'REC-20260705123817-645', '2026-07-05 10:38:17', 'Frais technique - 45.00 USD', 1, NULL),
(8, 5, 2, NULL, 'CREDIT', 23.00, 'REC-20260705214321-691', '2026-07-05 19:43:21', 'Frais technique - 45.00 USD', 1, NULL),
(9, 6, 6, NULL, 'DEBIT', 135.00, NULL, '2026-08-26 20:46:28', 'Facturation inscription: Frais Scolaires -secondaire', 1, NULL),
(10, 6, 11, NULL, 'DEBIT', 15.00, NULL, '2026-08-26 20:46:28', 'Facturation inscription: Frais Tricot', 1, NULL),
(11, 6, 14, NULL, 'DEBIT', 112.00, NULL, '2026-08-26 23:04:06', 'Facturation: jsdc', 1, NULL),
(12, 6, 14, NULL, 'CREDIT', 1.00, 'REC-20260827010919-437', '2026-08-26 23:09:19', 'Frais de l\'Etat Secondaire - 112.00 CDF', 1, NULL),
(13, 6, 14, NULL, 'CREDIT', 100.00, 'REC-20260827012124-437', '2026-08-26 23:21:24', 'Frais de l\'Etat Secondaire - 112.00 CDF', 1, NULL),
(14, 6, 6, NULL, 'CREDIT', 24.00, 'REC-20260830091037-118', '2026-08-30 07:10:37', 'Frais Scolaires -secondaire - 135.00 USD', 1, NULL),
(15, 6, 11, NULL, 'CREDIT', 10.00, 'REC-20260921191222-422', '2026-09-21 17:12:22', 'Frais Tricot - 15.00 USD', 3, NULL),
(16, 6, 6, NULL, 'CREDIT', 10.00, 'REC-20261016122820-603', '2026-10-16 10:28:20', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(17, 6, 11, NULL, 'CREDIT', 5.00, 'REC-20261016142458-964', '2026-10-16 12:24:58', 'Frais Tricot - 15.00 USD', NULL, 165),
(18, 7, 14, NULL, 'DEBIT', 30000.00, NULL, '2026-10-16 19:01:54', 'Facturation inscription: Frais de l\'Etat Secondaire', 1, NULL),
(19, 7, 6, NULL, 'DEBIT', 135.00, NULL, '2026-10-16 19:01:54', 'Facturation inscription: Frais Scolaires -secondaire', 1, NULL),
(20, 7, 11, NULL, 'DEBIT', 15.00, NULL, '2026-10-16 19:01:54', 'Facturation inscription: Frais Tricot', 1, NULL),
(21, 7, 6, NULL, 'CREDIT', 14.50, 'REC-20261016213458-502', '2026-10-16 19:34:58', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(22, 7, 11, NULL, 'CREDIT', 10.00, 'REC-20261016214540-229', '2026-10-16 19:45:40', 'Frais Tricot - 15.00 USD', NULL, 165),
(23, 6, 14, NULL, 'CREDIT', 9.00, 'REC-20261016214706-731', '2026-10-16 19:47:06', 'Frais de l\'Etat Secondaire - 112.00 CDF', NULL, 165),
(24, 6, 6, NULL, 'CREDIT', 10.00, 'REC-20261016215631-627', '2026-10-16 19:56:31', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(25, 7, 6, NULL, 'CREDIT', 12.00, 'REC-20261016215740-251', '2026-10-16 19:57:40', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(26, 5, 11, NULL, 'CREDIT', 4.00, 'REC-20261016220634-258', '2026-10-16 20:06:34', 'Frais Tricot - 15.00 USD', NULL, 165),
(27, 5, 11, NULL, 'CREDIT', 7.00, 'REC-20261016221621-749', '2026-10-16 20:16:21', 'Frais Tricot - 15.00 USD', NULL, 165),
(28, 5, 11, NULL, 'CREDIT', 1.00, 'REC-20261018122323-426', '2026-10-18 10:23:23', 'Frais Tricot - 15.00 USD', NULL, 165),
(29, 5, 2, NULL, 'CREDIT', 1.00, 'REC-20261018123212-217', '2026-10-18 10:32:12', 'Frais technique Nutrition - 45.00 USD', NULL, 165),
(30, 5, 11, NULL, 'CREDIT', 1.00, 'REC-20261018123517-670', '2026-10-18 10:35:17', 'Frais Tricot - 15.00 USD', NULL, 165),
(31, 5, 2, NULL, 'CREDIT', 1.00, 'REC-20261018135520-324', '2026-10-18 11:55:20', 'Frais technique Nutrition - 45.00 USD', NULL, 165),
(32, 6, 6, NULL, 'CREDIT', 1.00, 'REC-20261018141511-144', '2026-10-18 12:15:11', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(33, 5, 2, NULL, 'CREDIT', 2.00, 'REC-20261018141816-661', '2026-10-18 12:18:16', 'Frais technique Nutrition - 45.00 USD', NULL, 165),
(34, 1, 1, NULL, 'CREDIT', 50.00, 'REG-FRAIS-1-ea0dcc11de', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(35, 8, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-459628284a', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(36, 9, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-fa1dad7219', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(37, 10, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-fb02507326', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(38, 11, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-913bd52fab', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(39, 12, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-5b1b3464ad', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(40, 13, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-ebf9efc171', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(41, 14, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-4d498a164d', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(42, 15, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-0a80185126', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(43, 16, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-0d73fd0858', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(44, 17, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-dc2e60cf5e', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(45, 18, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-43e4dad8b9', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(46, 19, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-cb66ab366e', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(47, 20, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-db9756cf47', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(48, 21, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-f89491ebcf', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(49, 22, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-c9da0b1583', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(50, 23, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-4e33305012', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(51, 24, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-89dda49478', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(52, 25, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-a72cdee49e', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(53, 26, 1, NULL, 'DEBIT', 24000.00, 'REG-FRAIS-1-767e25665e', '2026-10-20 18:55:13', 'Régularisation de portée : section', 7, NULL),
(54, 27, 6, NULL, 'CREDIT', 10.00, 'REC-20261020210722-479', '2026-10-20 19:07:22', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(55, 28, 6, NULL, 'CREDIT', 15.00, 'REC-20261020232923-546', '2026-10-20 21:29:23', 'Frais Scolaires -secondaire - 135.00 USD', NULL, 165),
(56, 29, 5, NULL, 'DEBIT', 24000.00, NULL, '2026-10-21 21:31:40', 'Facturation inscription: Frais de l\'Etat Primaire', 3, NULL),
(57, 29, 8, NULL, 'DEBIT', 70.00, NULL, '2026-10-21 21:31:40', 'Facturation inscription: Frais Scolaires Primaire', 3, NULL),
(58, 29, 11, NULL, 'DEBIT', 15.00, NULL, '2026-10-21 21:31:40', 'Facturation inscription: Frais Tricot', 3, NULL),
(59, 3, 4, NULL, 'CREDIT', 1.00, 'REG-FRAIS-4-50adeba0c6', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(60, 6, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bff6ff5456', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(61, 30, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-352a9ae555', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(62, 31, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e9827cd661', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(63, 32, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-72cc7c73a8', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(64, 33, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e20bf93c7a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(65, 34, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-87960a0ea4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(66, 35, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-315cad7152', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(67, 36, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f4ba207887', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(68, 37, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7caa6292e5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(69, 38, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-26a4b79093', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(70, 39, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2f895bc63a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(71, 40, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-be3391ac92', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(72, 41, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-09fbcc3a74', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(73, 42, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4ce5afc117', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(74, 43, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2502e04902', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(75, 44, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-71d8b91e6c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(76, 45, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-27efc9852a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(77, 46, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f863c3a686', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(78, 47, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c8fdc26234', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(79, 48, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-64aed0f476', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(80, 49, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-42f09abbf6', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(81, 50, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3afef19dc9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(82, 51, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6d52e09a2c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(83, 52, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-03dabdb9fb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(84, 53, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-27f3afc503', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(85, 54, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-472d1d42fd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(86, 55, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0baab06034', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(87, 56, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3411769acc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(88, 57, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9b2255c81d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(89, 58, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-51ca09d0dc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(90, 59, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3ddf6334f4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(91, 60, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-347d81a8c4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(92, 61, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b51dd736cd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(93, 62, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c1789f10bc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(94, 63, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-618b04a4d2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(95, 64, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-34d27d258c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(96, 65, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4c2b2d367f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(97, 66, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-097b64f1af', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(98, 67, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-52b47cc6e1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(99, 68, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c73a55fb83', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(100, 69, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a6eb216ee6', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(101, 70, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fe8da94cb9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(102, 71, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-806bdf88b8', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(103, 72, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-06c397d095', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(104, 73, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1b8aac412f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(105, 74, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a2670f200a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(106, 75, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-264465dc1c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(107, 76, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2a58b5c51c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(108, 77, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-518ab513d5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(109, 78, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-36769e4cac', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(110, 79, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f9e98263a0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(111, 80, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7b3d331639', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(112, 81, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8429b500e3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(113, 82, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b87c2de13f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(114, 83, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3818b37a73', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(115, 84, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-096de652dd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(116, 85, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ea6ddb02ab', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(117, 86, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5f6e09fac7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(118, 87, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f219b9cf18', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(119, 88, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c390b65fc3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(120, 89, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b71dec586c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(121, 90, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c1f115257b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(122, 91, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ad4dad68ca', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(123, 27, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-953b43003d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(124, 92, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-22e721630a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(125, 93, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-758e80c4c7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(126, 94, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5bf35b5358', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(127, 95, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9b3968a9b4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(128, 96, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-243b51bbcc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(129, 97, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bdbdaab137', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(130, 98, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ae43ee61ba', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(131, 99, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-70a733a707', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(132, 100, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c15d9073bd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(133, 101, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f70ef9bb4d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(134, 7, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6fc921f815', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(135, 102, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d9817032fe', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(136, 103, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bfed0de697', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(137, 104, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ba4846738b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(138, 105, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8188f93246', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(139, 28, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d2f81c5840', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(140, 106, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b26cb24194', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(141, 107, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-74f0630411', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(142, 108, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-27105090ad', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(143, 109, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4749ecfe5b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(144, 110, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bedf959b7e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(145, 111, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d53befced7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(146, 112, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-16ccf4b0a0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(147, 113, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6e2ca5fa04', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(148, 114, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3fe8e14924', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(149, 115, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f80146d772', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(150, 116, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-24f78e524a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(151, 117, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4356423852', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(152, 118, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3818a56efc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(153, 119, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f5413e7020', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(154, 120, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-609c1de53c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(155, 121, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-685fa13830', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(156, 122, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4cb7ac25da', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(157, 123, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cd55328591', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(158, 124, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a82fe3d2da', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(159, 125, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a8bfe83b51', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(160, 126, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b0a0b5586d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(161, 127, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-df81e909b9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(162, 128, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-42ac65302f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(163, 129, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-079ff271f1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(164, 130, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f242d5b9cc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(165, 131, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d2dcf9d81f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(166, 132, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e6adaa7247', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(167, 133, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ea023b37ba', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(168, 134, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a57056a2aa', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(169, 135, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2bc6fda2ce', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(170, 136, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-179e92690b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(171, 137, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f990039bd8', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(172, 138, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1cba7b232f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(173, 139, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-51a5e2524d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(174, 140, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-104ef59cd9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(175, 141, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f32c5ac807', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(176, 142, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-13a26806a4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(177, 143, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-521d5104e2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(178, 144, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d9044abaa3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(179, 145, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f0b4918b04', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(180, 146, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b5f1a616f3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(181, 147, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4582f5c825', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(182, 148, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-77c1b3cf24', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(183, 149, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-071d02fb8c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(184, 150, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f41aabdb65', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(185, 151, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cd944bcf19', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(186, 152, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8571d218a2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(187, 153, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ad70834c30', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(188, 154, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-16a04576a4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(189, 155, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2dc9fd2e8c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(190, 156, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-32f676d52d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(191, 157, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-53ccece072', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(192, 158, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-23af9dc292', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(193, 159, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-69943c70cc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(194, 160, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8f249a603d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(195, 161, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-249c63cecc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(196, 162, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4cb8089a48', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(197, 163, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9f43b6ec9c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(198, 164, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b23433d824', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(199, 165, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-83eba47c19', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(200, 166, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8102c62806', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(201, 167, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-87c4662779', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(202, 168, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ab6ce01c8d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(203, 169, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-918e26c9a9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(204, 170, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-73c019ec2f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(205, 171, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-faad389093', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(206, 172, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ed80ce3679', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(207, 173, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f0fd499070', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(208, 174, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1684da541f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(209, 175, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d6b1f0020a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(210, 176, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d869ef46f4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(211, 177, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f6845e03d1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(212, 178, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-97b5d8394a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(213, 179, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0bcfac8f13', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(214, 180, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b3fa532797', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(215, 181, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f104bdd070', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(216, 182, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a00056ae87', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(217, 183, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cbb76fef4c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(218, 184, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fb81743e5a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(219, 185, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5889f6c9e0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(220, 186, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-04bda884ba', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(221, 187, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a09a7f9508', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(222, 188, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-36f7c4d1de', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(223, 189, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2f41ce637c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(224, 190, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e9b5d5d50d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(225, 191, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4c019380c2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(226, 192, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-394eab96db', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(227, 193, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5dcdcd3a07', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(228, 194, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f2447a70da', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(229, 195, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e939432571', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(230, 196, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-90434c3c38', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(231, 197, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bd87f48739', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(232, 198, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b1e9e54f34', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(233, 199, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c8a3aa3920', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(234, 200, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a473ac0955', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(235, 201, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bc7164c762', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(236, 202, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-de198d6ce3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(237, 203, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5c588f4d16', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(238, 204, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-49d9db6458', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(239, 205, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6c270411bc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(240, 206, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-23d6e31d1a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(241, 207, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5fa40fa4e3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(242, 208, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-423b6642fb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(243, 209, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8316922288', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(244, 210, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b5f448aa71', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(245, 211, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-53ef059d57', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(246, 212, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f169731f10', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(247, 213, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4353fe4dbc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(248, 214, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5acd73452b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(249, 215, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-be697aafbe', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(250, 216, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-51e3e303f4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(251, 217, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0f8980e041', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(252, 218, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cdebb29081', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(253, 219, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-092d53f967', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(254, 220, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-25ffdec1cd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(255, 221, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1957da5027', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(256, 222, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ab2a597898', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(257, 223, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-36671c8309', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(258, 224, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-492aede376', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(259, 225, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5b99ace4c1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(260, 226, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bd3d8c24a8', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(261, 227, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-44351f38ae', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(262, 228, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a1922f79b4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(263, 229, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f0118d1f18', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(264, 230, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b5356bb728', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(265, 231, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d97d58785a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(266, 232, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-07720a8d84', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(267, 233, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-640093d3e6', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(268, 234, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a5e61d8018', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(269, 235, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-338f1ba67d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(270, 236, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5621a70b12', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(271, 237, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cc4c052211', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(272, 238, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-52c35c7ae8', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(273, 239, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d4e96cde65', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(274, 240, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c8f2564b18', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(275, 241, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ccf7652fbb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(276, 242, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-208f1df216', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(277, 243, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6ef64595da', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(278, 244, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-99a9b4b74b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(279, 245, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5dcecdda24', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(280, 246, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4dabdfa8be', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(281, 247, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-42228e5bbf', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(282, 248, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-54db5fb7bf', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(283, 249, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bb26f6253f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(284, 250, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2eb8a3f462', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(285, 251, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e13c189454', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(286, 252, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8afa8a7322', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(287, 253, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6860c52e27', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(288, 254, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-54a722dd72', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(289, 255, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8a1862e484', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(290, 256, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-452f7d056d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(291, 257, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0cb086d011', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(292, 258, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-371eadf7ce', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(293, 259, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3c0d411698', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(294, 260, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c1f7455161', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(295, 261, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d9e5d296df', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(296, 262, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1fda328c61', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(297, 263, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8c76b1a7da', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(298, 264, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d3e1f1c0cd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(299, 265, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-29a822739a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(300, 266, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-805a614605', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(301, 267, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-41e6dc5a24', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(302, 268, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ad5c4ed954', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(303, 269, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5dc380746b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(304, 270, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-514b96a7f1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(305, 271, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0c110ab17e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(306, 272, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-683923f35f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(307, 273, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0f1e23cdee', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(308, 274, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d9bbda6154', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(309, 275, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0aedeefbed', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(310, 276, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-872cc56c45', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(311, 277, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2e818f50a7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(312, 278, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1bb3469af0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(313, 279, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ba971c89b7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(314, 280, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-05214af3aa', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(315, 281, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7dbd0a8121', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(316, 282, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-38ae2514c7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(317, 283, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-35ff2d899a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(318, 284, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dfacc7ef62', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(319, 285, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-eb5f136430', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(320, 286, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ef3d8d6cd6', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(321, 287, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e8049b8afb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(322, 288, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1b092a0b88', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(323, 289, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1a76002b62', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(324, 290, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-35b4b70af2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(325, 291, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c5644a5862', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(326, 292, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-aa7d6b0843', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(327, 293, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ca2e68b91a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(328, 294, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7dbd3f17a0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(329, 295, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-305782c28b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(330, 296, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d51385957f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(331, 297, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1ac234c104', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(332, 298, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2dafda3dcc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(333, 299, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-82088d9244', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(334, 300, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-23c2781eb7', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(335, 301, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a4342d8d92', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(336, 302, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-15a23f246f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(337, 303, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d70b81e207', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(338, 304, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fb466b8672', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(339, 305, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f2fb0a513e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(340, 306, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-111f502bf4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(341, 307, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c827be2d5f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(342, 308, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b044d07f91', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(343, 309, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-df9c9f0332', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(344, 310, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-64f881f849', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(345, 311, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-04999ff2ae', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(346, 312, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5e1ebcaaf3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(347, 313, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3e960c7089', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(348, 314, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4d056747cd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(349, 315, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6ab8488836', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(350, 316, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bfee4d0e8d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(351, 317, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e408351ebc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(352, 318, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9db753a940', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(353, 319, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9a420f81d6', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(354, 320, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e1695c7832', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(355, 321, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fd93b39980', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(356, 322, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fd23a49e66', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(357, 323, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bf6e212e22', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(358, 324, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-acfdf285ad', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(359, 325, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5d8df227eb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(360, 326, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-81daac5f83', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(361, 327, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-49c1fcd768', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(362, 328, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b8364e6fa5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(363, 329, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-70d09b0aca', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(364, 330, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b0d8495864', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(365, 331, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d6a2d336c4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(366, 332, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0479d1e1f2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(367, 333, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a54fe74d63', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(368, 334, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7f58dbfa5b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(369, 335, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-026d8706c0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(370, 336, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-eaac458963', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(371, 337, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6009242d0d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(372, 338, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-081d7dccf0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL);
INSERT INTO `ecritures_comptables_eleves` (`id`, `compte_eleve_id`, `frais_id`, `caisse_banque_id`, `type_mouvement`, `montant`, `reference_recu`, `date_operation`, `libelle`, `agent_saisie_id`, `utilisateur_saisie_id`) VALUES
(373, 339, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-da8d1d01d0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(374, 340, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-02ce640d6f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(375, 341, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-10936c34ff', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(376, 342, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-066817aa63', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(377, 343, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9da3852a14', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(378, 344, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fdd061e7d1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(379, 345, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-01ceae6a62', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(380, 346, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a5bb127fb1', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(381, 347, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a4e2e1ad93', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(382, 348, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0f908d8c79', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(383, 349, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7f6e3f4951', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(384, 350, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1d2738b379', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(385, 351, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b1e29afe22', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(386, 352, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d01497cd58', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(387, 353, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5ea1079635', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(388, 354, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-634f7e966d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(389, 355, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dd6c45e3b4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(390, 356, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6af1f4e4de', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(391, 357, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4a10d7859d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(392, 358, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bfada826f4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(393, 359, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bb3c98a2fd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(394, 360, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b7b89f64bf', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(395, 361, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d8e1eaae65', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(396, 362, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0f32f9140b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(397, 363, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-28206c1620', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(398, 364, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f5628ec29e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(399, 365, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a1fc4536f3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(400, 366, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-49f05d1c23', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(401, 367, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ea03e1638a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(402, 368, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-50358e9372', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(403, 369, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-35618a8632', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(404, 370, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3aec260248', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(405, 371, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e261868919', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(406, 372, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-62fbd257b2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(407, 373, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7bbc4d2e45', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(408, 374, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4b9e1c3b5e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(409, 375, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-764f1a000c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(410, 376, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2e07d84460', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(411, 377, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5a2b56fd3a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(412, 378, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8937aa516e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(413, 379, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3c24591a52', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(414, 380, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-71c4de6d6e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(415, 381, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bfbe56cee2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(416, 382, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f3c7faca4c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(417, 383, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9c5d7ebb73', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(418, 384, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1a40fe2f44', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(419, 385, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f5510b2c93', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(420, 386, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e08e94c49f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(421, 387, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-18d9adaa0d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(422, 388, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1fd91740ec', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(423, 389, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e2f84cff35', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(424, 390, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0a738833ab', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(425, 391, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-71bf1d0466', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(426, 392, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c7209dc6bc', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(427, 393, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9ea0489348', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(428, 394, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fb83df7d4a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(429, 395, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ad2ee58483', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(430, 396, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-318dcfffa9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(431, 397, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c50422011f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(432, 398, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7442422168', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(433, 399, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cb4e5723d4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(434, 400, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e1e8289b82', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(435, 401, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-325a9f8d75', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(436, 402, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2b75b5db63', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(437, 403, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3ed23a31ad', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(438, 404, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-02fcfb6beb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(439, 405, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a6a037915e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(440, 406, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b96415e932', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(441, 407, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1fb96216a4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(442, 408, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-aba4bca6b5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(443, 409, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fdaa0648d8', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(444, 410, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7e17f99cd4', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(445, 411, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f3a3f4f45e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(446, 412, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-916e8713d0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(447, 413, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6f2cee2af2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(448, 414, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6d37c9a2fb', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(449, 415, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-718a12920e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(450, 416, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a716b022a0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(451, 417, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a2d2b55710', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(452, 418, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-519caa0ad9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(453, 419, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fd71c5ba26', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(454, 420, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b9b4865030', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(455, 421, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-974ab7df0d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(456, 422, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-396e169704', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(457, 423, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f6daad0e96', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(458, 424, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0b847933a5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(459, 29, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4b39b5ca80', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(460, 425, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4f31b6c71e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(461, 426, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2febd53c6a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(462, 427, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b7563843b3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(463, 428, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-fcbaad2159', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(464, 429, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6cf4dbc9e0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(465, 430, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a3450784ca', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(466, 431, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-291a348987', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(467, 432, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8663d4a836', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(468, 433, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-868a441027', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(469, 434, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4532be7a9f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(470, 435, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3f9af4a0e9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(471, 436, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9572d4bb4e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(472, 437, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2e138a290c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(473, 438, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7c1415b479', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(474, 439, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f685aafc64', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(475, 440, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3f3044a5ed', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(476, 441, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-92d10c2735', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(477, 442, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-db7180b69d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(478, 443, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a7be179959', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(479, 444, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-516ae71020', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(480, 445, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-09f6ee9b79', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(481, 446, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2bb1971a5b', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(482, 447, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6777e1d539', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(483, 448, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5d507f11bf', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(484, 449, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f8fbc6cdc3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(485, 450, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-43377833bd', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(486, 451, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-92f6d42516', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(487, 452, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e0d58ebd6c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(488, 453, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-98e5be04e5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(489, 454, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-80bcc8a944', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(490, 455, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1a6f3c8812', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(491, 456, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4a3bf1f22d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(492, 457, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ebe3d421b9', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(493, 458, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6d0f368cbe', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(494, 459, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-85ebb659f0', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(495, 460, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d6e831130f', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(496, 461, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-293dd6196e', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(497, 462, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-faa0dd9196', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(498, 463, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-115ada98e2', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(499, 464, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ccc091a78d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(500, 465, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-71f456ed63', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(501, 466, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3293b73614', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(502, 467, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3432987a77', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(503, 468, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-83f10dfb7a', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(504, 469, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-47ae1e3256', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(505, 470, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d079b4068d', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(506, 471, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-052f3b1204', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(507, 472, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-46e5d1194c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(508, 473, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-22f477c4c5', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(509, 474, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b32367f688', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(510, 475, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1a7377c511', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(511, 476, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0933ec3ae3', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(512, 477, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1474b6697c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(513, 478, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-38c7ca7363', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(514, 479, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-40d6ef923c', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(515, 480, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c301ea9536', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(516, 481, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-71c1eebb73', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(517, 482, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cd8cd70e01', '2026-10-22 12:45:30', 'Régularisation de portée : école entière', 7, NULL),
(518, 483, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0805bcc448', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(519, 484, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f4f661ea23', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(520, 485, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6ee48808fa', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(521, 486, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-56eaed997a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(522, 487, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ea9de0df06', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(523, 488, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5ef6d83f43', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(524, 489, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6431cc925f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(525, 490, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e4ea72ea00', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(526, 491, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-44e0116f33', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(527, 492, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-723ee49e2e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(528, 493, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d0612dab69', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(529, 494, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-50d1a66d67', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(530, 495, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1a1ffc41be', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(531, 496, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f4e3fad0f0', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(532, 497, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3c0ae34fc3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(533, 498, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3fba8d824e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(534, 499, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-46b7c96e5c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(535, 500, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a903081f75', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(536, 501, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ae6c9892ca', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(537, 502, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d309c94cde', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(538, 503, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f2f03c564a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(539, 504, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-368c81ca9d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(540, 505, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c814a575a5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(541, 506, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-be6d260b7d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(542, 507, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c7a4a38684', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(543, 508, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d0c75f1a17', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(544, 509, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9937266151', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(545, 510, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1c20b782cf', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(546, 511, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bca1d3f43a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(547, 512, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9320a45b80', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(548, 513, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-eb3eb1a0b9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(549, 514, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a8748f9c72', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(550, 515, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-36a78716b9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(551, 516, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-efb3fa4184', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(552, 517, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c2a8f1d616', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(553, 518, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f9444a111b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(554, 519, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-421396f0b7', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(555, 520, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c791c05822', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(556, 521, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c6561a439e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(557, 522, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0609e62a1f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(558, 523, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-372f75376e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(559, 524, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5c7419667c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(560, 525, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9cbb54933e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(561, 526, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9dcf02b207', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(562, 527, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-39d1b0a2cf', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(563, 528, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3fd03d7d58', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(564, 529, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-df3649ba02', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(565, 530, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-df82a48b39', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(566, 531, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2301fa0c1d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(567, 532, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-77b293215a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(568, 533, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c0008e4155', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(569, 534, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a3e00dbb11', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(570, 535, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-17002c3bf5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(571, 536, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dab4bbf2a5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(572, 537, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-13b0026a0d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(573, 538, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d134a6e3cd', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(574, 539, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8d645061b3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(575, 540, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a3ade17937', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(576, 541, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-75b68383d1', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(577, 542, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-16520aa722', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(578, 543, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a77c55e084', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(579, 544, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-83fd99aa17', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(580, 545, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e48e2b1cd3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(581, 546, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2073e2200d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(582, 547, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bef9be749d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(583, 548, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6f64df48c2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(584, 549, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-91ad46153e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(585, 550, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5b14c54ce8', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(586, 551, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ade06a88d6', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(587, 552, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2aeef8960d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(588, 553, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-18ec394747', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(589, 554, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-08f0aa4f63', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(590, 555, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f5d83d7116', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(591, 556, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-475f0d7971', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(592, 557, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-00f6508347', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(593, 558, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5b30d1c8f4', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(594, 559, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-803be78693', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(595, 560, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6623efa8a9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(596, 561, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ccf13ba80b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(597, 562, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9857c6f23b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(598, 563, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c92e1da6e9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(599, 564, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-eaf1bfaaa7', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(600, 565, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-aa3f5419c8', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(601, 566, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-80b0e9968f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(602, 567, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f24c8f4019', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(603, 568, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-87cbe7f44d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(604, 569, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-feb7712cd9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(605, 570, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9988b00600', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(606, 571, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3692043c9a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(607, 572, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bdd5f8b436', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(608, 573, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cb16088fa3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(609, 574, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-86d0bd4619', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(610, 575, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7721c1418d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(611, 576, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-187c6881f1', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(612, 577, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8da4f1899a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(613, 578, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-81e54a4256', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(614, 579, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cb855fbff5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(615, 580, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c1d81fd469', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(616, 581, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-efd982b84f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(617, 582, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-997911f2b2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(618, 583, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b22e81ec1e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(619, 584, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cc4a73ed04', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(620, 585, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-35c834d48e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(621, 586, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-64207a10b2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(622, 587, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f6d3cdb8ef', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(623, 588, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4e860e06ed', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(624, 589, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c3e1eb5262', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(625, 590, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-072254de75', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(626, 591, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-03432f8bfc', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(627, 592, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b7399d0a92', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(628, 593, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4b7eabca3a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(629, 594, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3866948744', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(630, 595, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-28069475f3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(631, 596, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4ccc30afc8', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(632, 597, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4f92dfaff8', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(633, 598, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a7935e32fb', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(634, 599, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-565f56352d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(635, 600, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7b9538e31d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(636, 601, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-de01050e48', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(637, 602, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e03aaf6f43', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(638, 603, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-00029a201a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(639, 604, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e6b8de83c6', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(640, 605, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c19325c59a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(641, 606, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9a9ff42d66', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(642, 607, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2b3450b511', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(643, 608, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-273ac1f228', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(644, 609, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5cab9a5ce5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(645, 610, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f874305cdf', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(646, 611, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e2389b122d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(647, 612, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2249561270', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(648, 613, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c6ebc049ad', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(649, 614, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f07b77bfc2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(650, 615, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dcdb9366ad', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(651, 616, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cee4fe9708', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(652, 617, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d7dfce5ff1', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(653, 618, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b8f34ab22c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(654, 619, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b51aa0ebd4', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(655, 620, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dc9f26cd9d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(656, 621, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-38fe079f80', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(657, 622, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f228e024c9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(658, 623, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7913848bad', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(659, 624, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-808f89bce7', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(660, 625, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5459366029', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(661, 626, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d015892294', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(662, 627, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7ee3cbd838', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(663, 628, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c3925bebd0', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(664, 629, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c59b03adaf', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(665, 630, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e648e8b8e7', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(666, 631, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-36fb005c25', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(667, 632, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4bfda3735e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(668, 633, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-398d052450', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(669, 634, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3852fe95c0', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(670, 635, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4997146537', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(671, 636, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-acee06a6cc', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(672, 637, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-03144fe4b8', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(673, 638, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d249befd7f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(674, 639, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-f60f0e23dc', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(675, 640, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-41091eb78f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(676, 641, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0aff04b4f3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(677, 642, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-25da818937', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(678, 643, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c1c4de75fc', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(679, 644, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4917a666c6', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(680, 645, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-633479feb3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(681, 646, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2269c337ae', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(682, 647, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-978e86a40f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(683, 648, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2d4cec60c5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(684, 649, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dea401be53', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(685, 650, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-863d436b43', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(686, 651, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ece5a0c8e0', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(687, 652, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-51dd1a5cdd', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(688, 653, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-0d71d90f5e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(689, 654, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e15c67fb7a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(690, 655, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e032b05c4f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(691, 656, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-97deae4274', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(692, 657, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-495499437e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(693, 658, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bed294b8b1', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(694, 659, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dd2897bb0c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(695, 660, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e509b2759f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(696, 661, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2d918b80b2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(697, 662, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-b7bd6c0c1b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(698, 663, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9e09c4632a', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(699, 664, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9450b239f5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(700, 665, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2984887c07', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(701, 666, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-30d08f538c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(702, 667, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5c24e99551', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(703, 668, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6f9d5edc1b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(704, 669, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e27b018e1d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(705, 8, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-67d3e3a549', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(706, 9, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ea595f66f5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(707, 10, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-5072225a74', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(708, 11, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8748b4dae1', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(709, 12, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d78b51bf7f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(710, 13, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cb5d18883f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(711, 14, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7926e2ae61', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(712, 15, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-01b1f6da49', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(713, 670, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c7fc32d429', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(714, 671, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9a1bd88077', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(715, 672, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a198d65639', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(716, 673, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-bb7d8c34eb', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(717, 674, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-718f70ab9c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(718, 675, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-134e4d4e64', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(719, 676, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e14fb92863', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(720, 677, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-72868bca6b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(721, 678, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4d4bddb28f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(722, 679, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-df2c17956e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(723, 680, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ee89b2ffb7', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(724, 681, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-90d974fdf2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(725, 682, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cf2218113b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(726, 683, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1eb94a4d88', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(727, 684, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1d73ba7eb0', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(728, 685, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8ad6b3107b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(729, 686, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-3bb766ddb6', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(730, 687, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9553c0ab7e', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(731, 688, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-df832e910f', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(732, 689, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-14af3a0bfd', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(733, 690, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-c3ec881b2b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(734, 691, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-cc20e610ea', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(735, 692, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-9b2911ee8d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(736, 16, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-e73648a292', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(737, 17, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-6487d337a6', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(738, 18, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-95f740fcd9', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL);
INSERT INTO `ecritures_comptables_eleves` (`id`, `compte_eleve_id`, `frais_id`, `caisse_banque_id`, `type_mouvement`, `montant`, `reference_recu`, `date_operation`, `libelle`, `agent_saisie_id`, `utilisateur_saisie_id`) VALUES
(739, 19, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1670b9f83d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(740, 20, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-dda32fd657', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(741, 21, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-1d7a417186', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(742, 22, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8a0c450815', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(743, 693, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4b9bf06c4d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(744, 23, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a950d057a5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(745, 24, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-99dbb3f1e3', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(746, 694, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-8b94b50ac6', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(747, 695, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ac46b2de0c', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(748, 25, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-2a76d174c0', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(749, 26, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-4fed6d24be', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(750, 696, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-070a96b053', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(751, 697, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-ccb96e83a5', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(752, 698, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-21df50a9c2', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(753, 699, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d372a0d1e1', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(754, 700, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-95b0ce55ac', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(755, 701, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-07b67b556b', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(756, 702, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-a2e18dc58d', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(757, 703, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-d494d62511', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(758, 704, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-7fb0bd2261', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(759, 705, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-26132cd713', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(760, 706, 4, NULL, 'DEBIT', 1.00, 'REG-FRAIS-4-db309128e8', '2026-10-22 12:45:31', 'Régularisation de portée : école entière', 7, NULL),
(761, 6, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8c73e20ae0', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(762, 30, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2c1b8fad54', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(763, 31, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dd8c62f6e3', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(764, 32, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cc1cc09402', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(765, 33, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d54dcd706a', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(766, 34, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-169fd5668d', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(767, 35, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cce47afd23', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(768, 36, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f692bf6358', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(769, 37, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-06504eb682', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(770, 38, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eed620dc8b', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(771, 39, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a31eca5f07', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(772, 40, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-efbbd674e3', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(773, 41, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cea5d1c1c5', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(774, 42, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a7d8a8429f', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(775, 43, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3de9e50ff1', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(776, 44, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-af3baf9ffe', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(777, 45, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5cd76b5126', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(778, 46, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8413640838', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(779, 47, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1108ba12d6', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(780, 48, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ffec8456e0', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(781, 49, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-85d6c6adda', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(782, 50, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-93b3a0918d', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(783, 51, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-55a3bf5eca', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(784, 52, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fbecb18763', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(785, 53, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-30e100b9b1', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(786, 54, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2e1626183e', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(787, 55, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c51fad97f2', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(788, 56, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4c63d96827', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(789, 57, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d05150f5af', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(790, 58, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9c058b0ed0', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(791, 59, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4ec8b70216', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(792, 60, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5964cf115a', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(793, 61, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-06df1cae5e', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(794, 62, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9087547d07', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(795, 63, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9578931e93', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(796, 64, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eb2eaaffae', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(797, 65, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-174391f7a4', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(798, 66, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-43bdf3f63e', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(799, 67, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-becf89d725', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(800, 68, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c665e0426b', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(801, 69, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ba7ce5878d', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(802, 70, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9dd20791d4', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(803, 71, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-466a61a9c0', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(804, 72, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9796b533c2', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(805, 73, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8b6f1e210d', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(806, 74, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b54684c616', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(807, 75, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a2a3b5c4a8', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(808, 76, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-90387a2868', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(809, 77, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ef3af653c3', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(810, 78, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-35c136eaee', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(811, 79, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-81872decbc', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(812, 80, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4bce8fc5ef', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(813, 81, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bff93237ae', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(814, 82, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bdc360d8a3', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(815, 83, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-59a817ca55', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(816, 84, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-838e041554', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(817, 85, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1ecfd878bb', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(818, 86, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-12088f50ff', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(819, 87, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0bcea40311', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(820, 88, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1a32cf7fc5', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(821, 89, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2dd1f30a78', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(822, 90, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-687238c655', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(823, 91, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-95576cfdf4', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(824, 27, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-31af54b583', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(825, 92, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ee1ed36edc', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(826, 93, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0a6f4f1a4f', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(827, 94, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cfc1f91a89', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(828, 95, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b2b4f6ebbd', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(829, 96, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-195b35b6f4', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(830, 97, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c436c3c63d', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(831, 98, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a02b04e4e2', '2026-10-22 13:35:43', 'Régularisation de portée : école entière', 7, NULL),
(832, 99, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-47657d5ea8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(833, 100, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8c301dab03', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(834, 101, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4bcdb43496', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(835, 7, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b03c4f9f43', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(836, 102, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-53a388647b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(837, 103, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-60f3ec26b2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(838, 104, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-713dc27873', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(839, 105, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-646d267ef4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(840, 28, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f0e3262d53', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(841, 106, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e77d05067d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(842, 107, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-60f939c646', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(843, 108, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-61e12b2e44', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(844, 109, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1d4113d30c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(845, 110, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-87557b407c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(846, 111, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-26589679ff', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(847, 112, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f5b392908f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(848, 113, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-56a75648f2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(849, 114, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8d698e3a28', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(850, 115, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b0dabe0d9f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(851, 116, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-39b5110133', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(852, 117, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3ad0c67362', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(853, 118, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e83f560267', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(854, 119, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-900d7c948f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(855, 120, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c27830f575', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(856, 121, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4facd3e1d3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(857, 122, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2a13bc18c0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(858, 123, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0597631867', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(859, 124, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7983a070d0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(860, 125, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ceba41f6f3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(861, 126, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-394a2d0c0d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(862, 127, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5ee9dbd7c7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(863, 128, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-124ab2e1ba', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(864, 129, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-edc56ce7b4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(865, 130, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1ffdd544c8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(866, 131, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-078027f2fb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(867, 132, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9a9cbd027c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(868, 133, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9046577c82', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(869, 134, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8e5a473009', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(870, 135, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-87f7a548d2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(871, 136, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e01562372d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(872, 137, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-30b9e43790', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(873, 138, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-913ce87d56', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(874, 139, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f47312b329', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(875, 140, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-65a4163fdf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(876, 141, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7d72fa638c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(877, 142, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4a60cfb3be', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(878, 143, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6f2f10e442', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(879, 144, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bc62b73a4c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(880, 145, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bf2e36393b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(881, 146, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-09d8e71b59', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(882, 147, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e7a16320ce', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(883, 148, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7897940a40', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(884, 149, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fd06983920', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(885, 150, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bcf8f4d2a4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(886, 151, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8437c479d1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(887, 152, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8419c3a89b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(888, 153, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-79ea9aa5bd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(889, 154, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f228174a7d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(890, 155, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-99968214cf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(891, 156, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2bc05b639c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(892, 157, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-79d44ea457', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(893, 158, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-091bbb62ca', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(894, 159, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-458ecd57ae', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(895, 160, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9d15f51d86', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(896, 161, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-94824ef721', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(897, 162, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-abb3be8f27', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(898, 163, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c3b34a91bc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(899, 164, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7a73f23411', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(900, 165, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-00116bfd71', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(901, 166, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cba59f181c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(902, 167, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-43fd8189d3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(903, 168, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a8aa9ca928', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(904, 169, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bf3c07105f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(905, 170, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c48756158b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(906, 171, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f52e6394d1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(907, 172, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-93d766a964', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(908, 173, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-be2289863b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(909, 174, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3934ae52ec', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(910, 175, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dccc9467a9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(911, 176, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3c7f158a12', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(912, 177, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cadd7e2896', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(913, 178, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0f476878ca', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(914, 179, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-aa91913d34', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(915, 180, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-871ea88ead', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(916, 181, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ed28710f1e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(917, 182, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8ea279ec49', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(918, 183, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e3357057c4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(919, 184, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-59032ae09a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(920, 185, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-399c0ab062', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(921, 186, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-38a928171a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(922, 187, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7fb5a489cd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(923, 188, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d07d9a5406', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(924, 189, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eca768dd57', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(925, 190, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-87011b2468', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(926, 191, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b469393dfd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(927, 192, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-76c581fb58', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(928, 193, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-49705656f8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(929, 194, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ddb74f2e82', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(930, 195, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-89ee6be185', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(931, 196, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2c08b842bc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(932, 197, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f42d764956', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(933, 198, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d432994533', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(934, 199, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-920617547f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(935, 200, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3c02b6c770', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(936, 201, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4447f64a83', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(937, 202, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c46a1e610a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(938, 203, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-da7fb7d22a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(939, 204, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-be6eef0990', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(940, 205, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fdfe36a12f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(941, 206, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d2f3dcf20b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(942, 207, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-809ec23c72', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(943, 208, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fa94d9b93e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(944, 209, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-00bb46acd7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(945, 210, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d9f7eed287', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(946, 211, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8936fbd656', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(947, 212, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e3d9929b52', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(948, 213, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ba6b43fb4c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(949, 214, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f83d157b4b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(950, 215, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9fb7c4f689', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(951, 216, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-98602a0ecd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(952, 217, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-83885fdd38', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(953, 218, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4f5ab90c9c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(954, 219, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-78882eb6e3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(955, 220, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7f29144f1f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(956, 221, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eec80e5e7d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(957, 222, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c3e0bd208f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(958, 223, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2aa2849238', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(959, 224, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5f7c1c1e09', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(960, 225, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ed501cbf91', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(961, 226, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6383acb61f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(962, 227, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-642718ee5d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(963, 228, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-549c22a605', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(964, 229, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fd901c465c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(965, 230, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-333be07b6e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(966, 231, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0c4bef802a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(967, 232, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d627a3332e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(968, 233, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9ac521947c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(969, 234, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5b2427c4a0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(970, 235, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bea5bb51bd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(971, 236, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fd218cf88c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(972, 237, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a2a848e9ec', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(973, 238, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-95a45bb975', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(974, 239, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cc36c3c0e6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(975, 240, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dc456a80e1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(976, 241, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-094507588e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(977, 242, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a1c413aaab', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(978, 243, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b19409d5a8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(979, 244, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a7ad92d041', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(980, 245, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3fd24a791c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(981, 246, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-80adc984a3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(982, 247, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b8cb079261', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(983, 248, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-65def290c2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(984, 249, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-db7de8722f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(985, 250, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fc256560bb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(986, 251, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-29f8588988', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(987, 252, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-523f574b98', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(988, 253, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7fbcf8a400', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(989, 254, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-803ce95c47', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(990, 255, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c504a79bd1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(991, 256, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-08879605c5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(992, 257, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1ac0290beb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(993, 258, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-72bd9ba3a8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(994, 259, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-488a5877b7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(995, 260, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e7d45034f4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(996, 261, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-73af36818d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(997, 262, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-054f640cdb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(998, 263, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-476d37a11d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(999, 264, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ec3f36fd10', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1000, 265, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d820c33a9f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1001, 266, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cc98b51162', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1002, 267, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-aabfd814c8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1003, 268, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cd6bced2b9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1004, 269, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8496afaccb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1005, 270, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-86216b4726', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1006, 271, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-aac4033d98', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1007, 272, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c3422f86a9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1008, 273, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-007f4b1d0b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1009, 274, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-36b84cb888', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1010, 275, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a39e0515c7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1011, 276, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-90b345d9ae', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1012, 277, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ac64374752', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1013, 278, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a716d4329f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1014, 279, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-23f6c73a6a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1015, 280, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-19bd2a289f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1016, 281, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5e0856084d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1017, 282, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-baad34c9cb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1018, 283, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-176f8df569', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1019, 284, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e3ba14eb96', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1020, 285, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-43cf6c57ed', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1021, 286, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f38941b08d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1022, 287, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ef0ad4fe1b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1023, 288, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2fe7619a51', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1024, 289, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-74659c5107', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1025, 290, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fcd54f6638', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1026, 291, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b5c6173846', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1027, 292, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b786dfa78f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1028, 293, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-82c47d896d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1029, 294, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-38dee99a68', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1030, 295, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4834691413', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1031, 296, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8db6a61e13', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1032, 297, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dc318da8ef', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1033, 298, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ac6bcc1273', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1034, 299, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c96100115b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1035, 300, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6d66211f22', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1036, 301, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-88283ecc16', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1037, 302, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-21d5e55420', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1038, 303, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-faf63868e9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1039, 304, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1226846496', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1040, 305, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dcf98179cc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1041, 306, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2c0dfb0404', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1042, 307, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b287a2c029', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1043, 308, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b3c5c17961', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1044, 309, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cf3e8e192e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1045, 310, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e3bc9014b4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1046, 311, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-65b0f0fe24', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1047, 312, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f1f417dcf9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1048, 313, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-461d8c7785', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1049, 314, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-94e6bc200b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1050, 315, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4fdbf0eef0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1051, 316, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-37f5421f8b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1052, 317, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b24197c7df', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1053, 318, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-674dd9e97d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1054, 319, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4408a59ff6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1055, 320, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-aaf73b7217', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1056, 321, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8c4985d6cf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1057, 322, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c7c65942c6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1058, 323, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4066539378', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1059, 324, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e3d64bd2b7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1060, 325, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3bc31e3a4e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1061, 326, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-599532ee17', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1062, 327, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dd16e59f67', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1063, 328, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-69d2eac2e7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1064, 329, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-be92a92abf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1065, 330, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3a7abbdd38', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1066, 331, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e535dba7b6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1067, 332, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7fad4de7e3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1068, 333, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cbb2b71f7b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1069, 334, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5af213e405', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1070, 335, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e30f2a5810', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1071, 336, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-137d836bc2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1072, 337, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d1fcb23d83', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1073, 338, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-153eaa0f58', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1074, 339, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-976c987a4f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1075, 340, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-41818c5b46', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1076, 341, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c84eee98d9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1077, 342, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9c72718773', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1078, 343, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ff8408e520', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1079, 344, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-938300e532', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1080, 345, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-64b18f0aad', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1081, 346, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-755ea3ad37', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1082, 347, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a4f228063c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1083, 348, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f91fb95da0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1084, 349, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6d1329c99e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1085, 350, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-aaace0da3f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1086, 351, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-db800c4526', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1087, 352, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-50bca76f6d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1088, 353, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3418d9035b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1089, 354, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b93be55979', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1090, 355, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-268f51d284', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1091, 356, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8a2afe2720', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1092, 357, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ff1cf016b9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1093, 358, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-efcb51bd1f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1094, 359, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-864ad920aa', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1095, 360, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-77810a67f2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1096, 361, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-04367ec465', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1097, 362, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9fd4fe882d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1098, 363, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2529bfe425', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL);
INSERT INTO `ecritures_comptables_eleves` (`id`, `compte_eleve_id`, `frais_id`, `caisse_banque_id`, `type_mouvement`, `montant`, `reference_recu`, `date_operation`, `libelle`, `agent_saisie_id`, `utilisateur_saisie_id`) VALUES
(1099, 364, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-801ae036c0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1100, 365, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-628950401a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1101, 366, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-858662b053', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1102, 367, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-da9610b098', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1103, 368, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5ea3921c08', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1104, 369, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-327bf89bf7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1105, 370, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b7c11fe816', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1106, 371, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c1b8da3fe2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1107, 372, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-80a8384f4f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1108, 373, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b8dbde0a96', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1109, 374, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a8368ca940', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1110, 375, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ce035d7596', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1111, 376, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d45c23ed9d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1112, 377, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eebff96dd1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1113, 378, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c2d278b421', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1114, 379, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fcb7153e83', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1115, 380, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3c6b0efa8f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1116, 381, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e44f8cdb53', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1117, 382, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dba81722b4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1118, 383, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a61c3cfd68', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1119, 384, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2a8f43eba2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1120, 385, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9a5f7bde92', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1121, 386, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cf8c03c86b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1122, 387, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-66743594d4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1123, 388, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-61e477f82c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1124, 389, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a78adcd46a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1125, 390, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4fd071f4c9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1126, 391, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-32c19b2560', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1127, 392, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d631c7e310', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1128, 393, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2f6f265b17', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1129, 394, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-834c16dd02', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1130, 395, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-11a58854e0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1131, 396, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9c9b756194', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1132, 397, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9eb124ff0d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1133, 398, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-13f95f2db7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1134, 399, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2aa448a022', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1135, 400, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7e89abbe46', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1136, 401, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c57543df3a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1137, 402, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cef04211e1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1138, 403, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2ec1def4ea', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1139, 404, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5f117103cd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1140, 405, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e72ec29796', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1141, 406, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0baec57229', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1142, 407, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8be9206bb2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1143, 408, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eed2bce3e7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1144, 409, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-42857b2252', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1145, 410, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b896646775', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1146, 411, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ab4c404ce0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1147, 412, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3fc6344e78', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1148, 413, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f5264f74d8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1149, 414, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-23c0313a80', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1150, 415, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4e155e1e22', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1151, 416, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-37e1c88f7e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1152, 417, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f695b16364', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1153, 418, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-155821d9dd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1154, 419, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c7d2fd7fe1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1155, 420, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b8026ec01b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1156, 421, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-48dc73ad77', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1157, 422, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ed662678e5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1158, 423, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d73720ab57', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1159, 424, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-309e9557d7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1160, 29, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a7e058c6e1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1161, 425, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-99bd7015e5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1162, 426, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-620f6916d9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1163, 427, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8dbfd63992', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1164, 428, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0410801a79', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1165, 429, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-86488be8b6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1166, 430, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-343e2b5a80', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1167, 431, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-84f725404b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1168, 432, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6073c6a47c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1169, 433, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d27cf0b594', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1170, 434, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7932f926f2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1171, 435, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-27efd2f26e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1172, 436, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cacd3e8814', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1173, 437, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c97a54db4c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1174, 438, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-667614a631', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1175, 439, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c2eef6dd9d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1176, 440, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d60bd8605a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1177, 441, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a7f108c811', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1178, 442, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-48a661cb99', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1179, 443, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-78c3154b45', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1180, 444, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6e2ba8c250', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1181, 445, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7c9f7a5a8c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1182, 446, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dd1cbe7895', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1183, 447, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1196c5f6d8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1184, 448, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-df32afb029', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1185, 449, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-586130bacc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1186, 450, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-802b7b6525', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1187, 451, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d19b2e1ef8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1188, 452, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fd44e16d20', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1189, 453, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-475cea82be', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1190, 454, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-076287fa0f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1191, 455, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8577967370', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1192, 456, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0fd7b83c0c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1193, 457, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-134bf62865', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1194, 458, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c38ce19b2e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1195, 459, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7f0860443d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1196, 460, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-39024573f6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1197, 461, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-109b49e3f9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1198, 462, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7bb92693fe', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1199, 463, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cfd6faad28', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1200, 464, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-acd19b13d8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1201, 465, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a9b5086a17', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1202, 466, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-63d52d1b8d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1203, 467, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ebf3d153c3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1204, 468, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9425d70370', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1205, 469, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fb0e0de9c8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1206, 470, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e5f92e0261', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1207, 471, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b2501a53c5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1208, 472, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-577476c603', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1209, 473, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f0bc3265e6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1210, 474, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-19504e690d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1211, 475, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-691c4606f7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1212, 476, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-55722db343', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1213, 477, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2bd14264b7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1214, 478, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ebbdfaae0b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1215, 479, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dc379af978', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1216, 480, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e8faa0720d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1217, 481, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3c0ff34c47', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1218, 482, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-51b2c1a041', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1219, 483, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3015a7a63c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1220, 484, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e573d43d18', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1221, 485, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a2754d4e49', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1222, 486, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-91576aed98', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1223, 487, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-01173c9104', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1224, 488, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-852d815e77', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1225, 489, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7d6dadada7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1226, 490, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6cca2962dc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1227, 491, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f8e67973fb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1228, 492, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6acbfb248b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1229, 493, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b36c50d90d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1230, 494, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0eacf0ffad', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1231, 495, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5fe5f11fdb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1232, 496, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6e2d0a050a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1233, 497, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8c6170ee60', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1234, 498, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-75210229ee', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1235, 499, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-89cb53628e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1236, 500, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0bbd7c3fe8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1237, 501, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d2f38b634b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1238, 502, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-32683a51bc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1239, 503, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-88dcd2d489', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1240, 504, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-38671a4e47', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1241, 505, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d2cbb22dd6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1242, 506, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d3983d1ea7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1243, 507, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-28e02c733f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1244, 508, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eab2d79304', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1245, 509, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6c3404b76c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1246, 510, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ff9925d2e7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1247, 511, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fbe61b5687', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1248, 512, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9b5eaa90d7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1249, 513, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dd95b95f7b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1250, 514, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c9c24f56b5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1251, 515, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fa1e76270c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1252, 516, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-17f6547379', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1253, 517, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-488da61669', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1254, 518, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e0cd66fea9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1255, 519, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a8d746df47', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1256, 520, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0e95c50326', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1257, 521, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a767ac61b5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1258, 522, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-27455bde17', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1259, 523, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-64994121c1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1260, 524, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-85fc50aa04', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1261, 525, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d0af4abdf7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1262, 526, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-163cc63a5f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1263, 527, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-528e2e0c33', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1264, 528, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-af69607286', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1265, 529, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9c9483da52', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1266, 530, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f06226fddc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1267, 531, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d58e40d0a7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1268, 532, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f38676d92c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1269, 533, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c223a24a53', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1270, 534, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-73f3a112f9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1271, 535, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-072cd2ddf4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1272, 536, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-00e974d693', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1273, 537, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4aa079d7cd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1274, 538, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9bf259db17', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1275, 539, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4efc37d1e5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1276, 540, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-500ff8b155', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1277, 541, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-72b9a3c5d1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1278, 542, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f32b84445b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1279, 543, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9e5d8fa38a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1280, 544, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7b8e045c3d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1281, 545, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e397797aee', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1282, 546, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-19c8580ea9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1283, 547, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c2bf8bc41b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1284, 548, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d32f66fc15', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1285, 549, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7d7bd68068', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1286, 550, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6455bb05c0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1287, 551, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7ac0dd2b94', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1288, 552, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4cb649fe6b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1289, 553, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2114181c2b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1290, 554, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f86b1cb66d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1291, 555, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f9c0048a1b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1292, 556, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-709e1126d3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1293, 557, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-39ecec253f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1294, 558, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-254b308170', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1295, 559, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c84661acf8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1296, 560, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3a9ebab26c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1297, 561, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-69197aadbf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1298, 562, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a02bfa2db6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1299, 563, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9268281164', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1300, 564, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6efb995a3e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1301, 565, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-80520a2506', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1302, 566, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-76cb643ed3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1303, 567, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-838d858677', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1304, 568, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-20513ce87a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1305, 569, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4934ffcabf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1306, 570, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f371bc3cc6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1307, 571, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-133688e23b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1308, 572, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4a9512e48e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1309, 573, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-95a457d06e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1310, 574, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0fe48ce512', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1311, 575, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e6d92bb5cf', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1312, 576, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f208941fff', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1313, 577, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e9413c5ac2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1314, 578, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9ad7588662', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1315, 579, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9e29e6d12f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1316, 580, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b9070195fc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1317, 581, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3d36df5cd4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1318, 582, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-faaf8e7cb1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1319, 583, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-60995b9989', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1320, 584, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-13b93bd5f4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1321, 585, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7f8e4bc792', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1322, 586, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2ba92807c1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1323, 587, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fd421cb093', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1324, 588, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-77e2c94b35', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1325, 589, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-922398ab11', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1326, 590, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-132a75b279', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1327, 591, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2199d52818', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1328, 592, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c5dc16b6c5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1329, 593, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ce23202048', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1330, 594, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-52d8173aa7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1331, 595, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c3ba50a8c1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1332, 596, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-478049c979', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1333, 597, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2d0f0ccbc6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1334, 598, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4529b2368a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1335, 599, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6424b7680a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1336, 600, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-519094376d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1337, 601, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4c4f629015', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1338, 602, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-607a458686', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1339, 603, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bf6bffb404', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1340, 604, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7a8744efd0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1341, 605, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bbfd33d259', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1342, 606, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6a8f5e7a5c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1343, 607, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0aca70d019', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1344, 608, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d18b13f050', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1345, 609, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-30f5a481ac', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1346, 610, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9aba1c2990', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1347, 611, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e51c9b73e5', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1348, 612, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bb6fe19dc0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1349, 613, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e56f953f0e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1350, 614, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-90455067cd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1351, 615, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1addebb1f8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1352, 616, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2d8dc19e2b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1353, 617, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-362cf17663', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1354, 618, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-453272215c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1355, 619, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cdabb32921', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1356, 620, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-da11e7b535', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1357, 621, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-87b5d4016a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1358, 622, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-420742eb71', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1359, 623, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e322f0d110', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1360, 624, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b4685643ad', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1361, 625, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4b53881aff', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1362, 626, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c35ea641e3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1363, 627, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d3f004b3d1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1364, 628, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-185716510f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1365, 629, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-7749ebae4b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1366, 630, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-83b29192bc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1367, 631, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9af30a31b2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1368, 632, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5b65f17469', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1369, 633, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-662b11b7fd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1370, 634, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f6060ee44b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1371, 635, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9920d67abe', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1372, 636, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-aab184068b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1373, 637, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5715b5f242', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1374, 638, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a6e3055664', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1375, 639, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d994912cd1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1376, 640, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b691dd9539', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1377, 641, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3025053ba4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1378, 642, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-85ebdd0dcd', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1379, 643, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8ea5c7a429', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1380, 644, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ab55d41969', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1381, 645, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8723ef8dbc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1382, 646, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e376b3dc21', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1383, 647, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-bfd08d9bb3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1384, 648, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-8261e8ede3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1385, 649, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-29388d9842', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1386, 650, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dfe158fc33', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1387, 651, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d9654e4493', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1388, 652, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1e120c320c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1389, 653, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f7bdf703a7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1390, 654, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ba53665cc2', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1391, 655, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5aeae423e8', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1392, 656, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5d52e3853b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1393, 657, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fbc25fda72', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1394, 658, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-f6ea4d6816', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1395, 659, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-47602177e7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1396, 660, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-fc2df8beea', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1397, 661, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-78682afbc0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1398, 662, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d067ed68e3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1399, 663, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d8238a2c9a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1400, 664, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4230099fac', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1401, 665, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-a7d63b6c3b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1402, 666, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-984754ccfc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1403, 667, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9a4c7c49b3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1404, 668, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-abb83b2974', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1405, 669, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-04c8767199', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1406, 8, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4c9562d809', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1407, 9, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d379a4c8e0', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1408, 10, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5b4321cc7e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1409, 11, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-822a201cbe', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1410, 12, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-704f4e5539', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1411, 13, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-dad6dbaef9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1412, 14, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-eb2cdd1256', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1413, 15, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-cea5476818', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1414, 670, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-671e14cc09', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1415, 671, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1ddfd367b6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1416, 672, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5f7237ed3e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1417, 673, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-80ec063279', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1418, 674, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d36d7c65d1', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1419, 675, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b33aa9564b', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1420, 676, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0b280cb83f', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1421, 677, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-686bec0474', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1422, 678, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-9290f02c93', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1423, 679, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5b925e20c7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1424, 680, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1c9bb5bc19', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1425, 681, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-14b76fdf3e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1426, 682, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-82f1651dea', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1427, 683, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-95f1d8e7b3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1428, 684, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5154fe0abb', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1429, 685, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d3889cd398', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1430, 686, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-3997a07b80', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1431, 687, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-6ff4edb213', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1432, 688, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c8e2fb45fc', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1433, 689, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d733c0212c', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1434, 690, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-1d1e12f354', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1435, 691, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-171ecea459', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1436, 692, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ebda859c9a', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1437, 16, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5b279b1cf7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1438, 17, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d4ce7bb4a6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1439, 18, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-801423db21', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1440, 19, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-e74f404298', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1441, 20, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c1dc5231b9', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1442, 21, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-83d2c15e89', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1443, 22, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-d77dba5651', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1444, 693, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5bb2e2649e', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1445, 23, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-ebe70fb4c3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1446, 24, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-39e9246e98', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1447, 694, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4410b9b724', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1448, 695, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-2257b03623', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1449, 25, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-10fb5d71d6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1450, 26, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5edc8a0a68', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1451, 696, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-584bba42d6', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1452, 697, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-43735dddee', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1453, 698, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-11c8b08c36', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1454, 699, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-4e4a0f7447', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1455, 700, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-63923eeaa3', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1456, 701, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-c7928a0e14', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL);
INSERT INTO `ecritures_comptables_eleves` (`id`, `compte_eleve_id`, `frais_id`, `caisse_banque_id`, `type_mouvement`, `montant`, `reference_recu`, `date_operation`, `libelle`, `agent_saisie_id`, `utilisateur_saisie_id`) VALUES
(1457, 702, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-0fc2e3561d', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1458, 703, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b411a522c7', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1459, 704, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-5f41845a72', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1460, 705, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-56284ae266', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1461, 706, 15, NULL, 'DEBIT', 1.00, 'REG-FRAIS-15-b0e965fca4', '2026-10-22 13:35:44', 'Régularisation de portée : école entière', 7, NULL),
(1462, 28, 15, NULL, 'CREDIT', 1.00, 'REC-20261029141953-559', '2026-10-29 13:19:53', 'Cahier de Communication - 1.00 USD', NULL, 165),
(1463, 6, 16, NULL, 'CREDIT', 0.80, 'REC-20261029160927-280', '2026-10-29 15:09:27', 'Logo - 1.00 USD', NULL, 165);

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
(9, 'ANGELA-20260705123651-584c73', 'ANGELA', 'BAHATI', 'MIRIAM', 'F', 'GOMA', 'CONGOLAISE', 'HZHSDCSJ', '2017-02-12', 5, 'BAHATI', 'GRACE', 'Nord-Kivu', 'Beni', 'Beni-Centre', 'Group X', 'Village X1', '', NULL, 'actif', 4),
(10, 'NKUB00245', 'NKUBA', 'KASKOTI', 'JOHN', 'M', 'KANANGA', 'CONGOLAISE', 'Nakuwadde, Kampala', '2019-01-12', 6, 'KASKOOTI', 'ANGE', 'Équateur', '', '', '', '', '', NULL, 'actif', 4),
(11, 'KASE93856', 'KASEREKA', 'KALUME', 'JOHN', 'M', 'BUNIA', 'CONGOLAISE', 'Goma\r\nNdosho', '2015-11-11', 9, 'KIZOMBO JOSEPH', 'Nicole KIZOMBO', 'Nord-Kivu', 'Goma', 'Sector 1', 'Group A', 'Village 1', '', NULL, 'actif', 4),
(12, NULL, 'FURAHA', 'BYABUZE', 'BLANDINE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2017-02-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8347', NULL, 'actif', 4),
(13, NULL, 'NZIGIRE', 'TUISHI', 'ORNELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANIG, NTINGI, 032', '2012-03-14', NULL, 'TUISHI YUNVA', 'FURAHA GRACE', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '8348', NULL, 'actif', 4),
(14, NULL, 'MUHYAHYA', 'MUBWEBWE', 'DANIEL', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 012', '2010-10-23', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8353', NULL, 'actif', 4),
(15, NULL, 'MUPOLE', 'BALALUKA', 'VICTOIRE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 013', '2018-03-18', NULL, NULL, NULL, 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8360', NULL, 'actif', 4),
(16, NULL, 'SINGIRA', 'MATATA', 'HONORE', 'M', 'GEMENA', 'CONGOLAISE', 'NGANGI, KAKOTI, 932', '2011-07-26', NULL, NULL, NULL, 'EQUATEUR', 'GEMENA', NULL, NULL, NULL, '8361', NULL, 'actif', 4),
(17, NULL, 'ALUMA', 'KITO', 'ELYSEE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2017-07-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8193', NULL, 'actif', 4),
(18, NULL, 'ALUMA', 'KUNGWA', 'LYSA', 'F', 'GOMA', 'CONGOLAISE', NULL, '2017-07-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8194', NULL, 'actif', 4),
(19, NULL, 'ANGE', 'MENGA', 'MUKONGWA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2012-08-16', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8133', NULL, 'actif', 4),
(20, NULL, 'ANNE', 'BUSHENYULA', 'NOUVELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 023', '2019-12-26', NULL, 'BUSHENYULA BIENVENU', 'IRENE NYAMUSHIYA', 'SUD-KIVU', 'UVIRA', NULL, NULL, NULL, '8342', NULL, 'actif', 4),
(21, NULL, 'ANOCIATA', 'BAHATI', 'ARGINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, TINGI', '2013-07-02', NULL, 'BAHATI', 'RACHEL', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8058', NULL, 'actif', 4),
(22, NULL, 'ASIFIWE', 'BATABWIRANA', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 931', '2014-08-02', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8233', NULL, 'actif', 4),
(23, NULL, 'AYUNVA', 'MURHULA', 'PLAMEDI', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI', '2015-04-24', NULL, 'MURHULA', 'ALPHONCINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8157', NULL, 'actif', 4),
(24, NULL, 'BANYANGA', 'KIBANJA', 'MICHAELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 392', '2020-06-21', NULL, 'MUHINDO JONAS', 'BINDU BANYANGA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8227', NULL, 'actif', 4),
(25, NULL, 'BEHEBISU', 'MUSALIA', 'ESTIME', 'F', 'PINGA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2020-04-01', NULL, NULL, NULL, 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8163', NULL, 'actif', 4),
(26, NULL, 'BELEKA', 'LONGANGE', 'MARIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2008-12-04', NULL, 'BONGANGE', 'KITUMUAINI', 'EQUATEUR', 'IKELA', NULL, NULL, NULL, '8292', NULL, 'actif', 4),
(27, NULL, 'BENITA', 'UBUTO', 'JOHANAH', 'F', 'BWEREMANA', 'CONGOLAISE', NULL, '2021-01-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8240', NULL, 'actif', 4),
(28, NULL, 'BINJA', 'CHIMANUKA', 'JEMIMAH', 'F', 'GOMA', 'CONGOLAISE', 'KAYOTI, DES TECHNICIENS', '2018-11-05', NULL, 'CHIMANUKA MOISE', 'GERLUC', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8092', NULL, 'actif', 4),
(29, NULL, 'BUZALAWA', 'WATIMBWA', 'LEONARDA', 'F', 'KAMITUGA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2011-07-20', NULL, 'LEANDRE WATIMBWA', 'JEANNETTE FRANCOISE', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8252', NULL, 'actif', 4),
(30, NULL, 'CHITO', 'KABUMBANYUNGU', 'DIVINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2014-09-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8250', NULL, 'actif', 4),
(31, NULL, 'DOROTHEA', 'NYACHAKO', 'CONSTANCE', 'F', 'PINGA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2017-10-26', NULL, 'MUSALIA', 'RIZIKI', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8162', NULL, 'actif', 4),
(32, NULL, 'ELISABETH', 'BIRINDWA', 'KIVINE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 311', '2015-08-08', NULL, 'KIGOMBE', 'DIVINE BAHATI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8234', NULL, 'actif', 4),
(33, NULL, 'FARADJA', 'BAZIGA', 'PRISCA', 'F', 'GOMA', 'CONGOLAISE', NULL, '2020-12-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8114', NULL, 'actif', 4),
(34, NULL, 'FAZILI', 'MATENDA', 'KERENE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2012-03-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8257', NULL, 'actif', 4),
(35, NULL, 'FURAHA', 'MAFULUKO', 'KELVINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 13', '2013-06-28', NULL, NULL, NULL, 'SUD-KIVU', 'KAVUMU', NULL, NULL, NULL, '8268', NULL, 'actif', 4),
(36, NULL, 'JASMINE', 'NDONGA', 'MWINJA', 'F', 'BULENGA', 'CONGOLAISE', 'NGANGI, KAYEMBE, 12', '2015-09-15', NULL, 'AMANI BALEKAGE', 'SIFA MANEGABE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8066', NULL, 'actif', 4),
(37, NULL, 'KABARA', 'KABUMBANYUNGU', 'QUETSIAH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2014-06-02', NULL, 'KABUMBANYUNGU', 'N\'SIMIRE', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8247', NULL, 'actif', 4),
(38, NULL, 'KABUO', 'KATAKA', 'MEDA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 78', '2015-08-06', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8148', NULL, 'actif', 4),
(39, NULL, 'KAHAMBU', 'NZOVOLI', 'ABIGAEL', 'F', 'GOMA', 'CONGOLAISE', NULL, '2013-10-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8215', NULL, 'actif', 4),
(40, NULL, 'KEMBWA', 'MUNGANGA', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 02', '2020-05-12', NULL, 'DUSENGE', 'DEBORAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8125', NULL, 'actif', 4),
(41, NULL, 'KYALA', 'MAKUMBA', 'VANESSA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 012', '2009-03-10', NULL, NULL, NULL, 'SUD-KIVU', 'UVIRA', NULL, NULL, NULL, '8324', NULL, 'actif', 4),
(42, NULL, 'LAILAH', 'WETEMWAMI', 'ASANATI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2016-04-28', NULL, 'MUSTAFA', 'ASIATI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8035', NULL, 'actif', 4),
(43, NULL, 'LUMOO', 'LUANDA', 'DIVINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOWA', '2015-03-22', NULL, 'LUANDA STEVE', 'VIVIANE MUNYOMO', 'SUD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8128', NULL, 'actif', 4),
(44, NULL, 'MAGAJU', 'BAVURIKI', 'DORCAS', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2009-11-08', NULL, 'MAGAJU ENOCK', 'MUNGALA LUCIE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8177', NULL, 'actif', 4),
(45, NULL, 'MALIYETU', 'MUBALIRWA', 'BELAMI', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 1²', '2009-01-12', NULL, NULL, NULL, 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8106', NULL, 'actif', 4),
(46, NULL, 'MENGA', 'MUKONGWA', 'OLIVE', 'F', 'GOMA', 'CONGOLAISE', 'NGANI, NTINGI, 92', '2021-10-03', NULL, 'MENGA', 'FEZA KITANDI', 'SUD-KIVU', 'MASISI', NULL, NULL, NULL, '8130', NULL, 'actif', 4),
(47, NULL, 'MIUJIZA', 'BATASEMA', 'MIRACLE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI III, NTINGI', '2019-09-04', NULL, 'OMBENI BATASEMA DIDIER', 'NZIGIRE NTACHO ALICE', 'SANGURU', 'KATANA', NULL, NULL, NULL, '8142', NULL, 'actif', 4),
(48, NULL, 'MIUJIZA', 'NACHOFI', 'LYDIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 214', '2012-09-25', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8236', NULL, 'actif', 4),
(49, NULL, 'MUGOLI', 'MANEGABE', 'JULIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, KAKOTI, 83', '2020-11-01', NULL, 'BYAMUNGU MANEGABE', 'CHRISTINE BIRONDO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8120', NULL, 'actif', 4),
(50, NULL, 'MUGOLI', 'MUGISHO', 'ELYSEE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 02', '2020-01-03', NULL, 'MUGISHO ELIEZER', 'MWAMINI BIZI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8108', NULL, 'actif', 4),
(51, NULL, 'MUGOLI', 'SAFARI', 'JEANNETTE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2015-07-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8077', NULL, 'actif', 4),
(52, NULL, 'MULUMBA', 'MINANI', 'ANNE-MARIE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 59', '2010-02-02', NULL, 'MATTHEIU MULUMBA', 'JEANETTE BADERHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8136', NULL, 'actif', 4),
(53, NULL, 'MUNYERENKANA', 'CIRUZA', 'EMINENCE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2017-03-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8261', NULL, 'actif', 4),
(54, NULL, 'MWANGAZA', 'MUGISHO', 'GLADYS', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 02', '2016-03-03', NULL, 'MUGISHO ELIEZER', 'MWAMINI BIZI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8117', NULL, 'actif', 4),
(55, NULL, 'NOELA', 'BAUMA', 'BLANDINE', 'F', 'MASISI', 'CONGOLAISE', 'NGANGI III, KAYEMBE, 73', '2012-12-25', NULL, 'BALINGENE BUSANGA', 'BINDU BAUMA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8319', NULL, 'actif', 4),
(56, NULL, 'NSIMIRE', 'MUGISHO', 'GRACE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 02', '2018-07-09', NULL, 'MUGISHO ELIEZER', 'MWAMINI BIZI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8116', NULL, 'actif', 4),
(57, NULL, 'N\'SIMIRE', 'SEBURO', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS', '2010-03-17', NULL, 'ELIYA', 'VALENCE', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '8330', NULL, 'actif', 4),
(58, NULL, 'NTAYEOLIKAMUNGU', 'KUSINZA', 'PASCALINE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 304', '2010-04-12', NULL, 'KUSINZA SHANGALUME', 'BALEMBA CHIREGANO', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '8297', NULL, 'actif', 4),
(59, NULL, 'NURIATH', 'WETEMWAMI', 'FURAHA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2016-03-03', NULL, 'MUSTAFA', 'DJAZILA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8034', NULL, 'actif', 4),
(60, NULL, 'NYANCHIRA', 'LUANDA', 'STEPHIE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOWA, 273', '2012-02-19', NULL, 'LUANDA STEVE', 'VIVIANE MUNYOMO', 'SUD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8127', NULL, 'actif', 4),
(61, NULL, 'NZABARA', 'KAZINGUFU', 'COLINAH', 'F', 'KIWANJA', 'CONGOLAISE', NULL, '2014-08-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8107', NULL, 'actif', 4),
(62, NULL, 'NZIGIRE', 'BULONZA', 'CARINE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2009-08-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8289', NULL, 'actif', 4),
(63, NULL, 'PEMBA', 'KASONGO', 'MELANIE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, LUKWETI, 013', '2008-07-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8280', NULL, 'actif', 4),
(64, NULL, 'REHEMA', 'BAHATI', 'ELYSE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, VITSHUMBI, 16', '2018-06-24', NULL, 'BAHATI LWABOSHO', 'MAPENZI CIDORO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8079', NULL, 'actif', 4),
(65, NULL, 'REHEMA', 'WAKABALA', 'ANNA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2007-04-02', NULL, 'ISMAEL WAKABAL', 'ADIDJA MASIRI', 'NORD-KIVU', 'NYIRAGONGO', NULL, NULL, NULL, '8222', NULL, 'actif', 4),
(66, NULL, 'RIZIKI', 'CIRUZA', 'MARIE-JEANNE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2015-03-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8260', NULL, 'actif', 4),
(67, NULL, 'SALIMA', 'LONGANGE', 'PLAMEDI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2010-09-30', NULL, 'LONGANGI', 'KITUMAINI JUMA', 'EQUATEUR', 'MONGALA', NULL, NULL, NULL, '8291', NULL, 'actif', 4),
(68, NULL, 'SARAH', 'BAZIGA', 'LYLYANNE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2019-03-17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8113', NULL, 'actif', 4),
(69, NULL, 'SILA', 'KAMUNTU', 'ESTHER', 'F', 'MINOVA', 'CONGOLAISE', 'MUGUNGA, MAPENDO, 13', '2013-09-02', NULL, 'KAMUNTU', 'N\'SIMIRE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8104', NULL, 'actif', 4),
(70, NULL, 'ZITA', 'TABENA', 'BRIGITTE', 'F', 'SHABUNDA', 'CONGOLAISE', 'KATOYI, ZIZI, 009', '2020-10-28', NULL, 'TABENA WAKUZYA', 'APENDEKI DORCAS', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '8032', NULL, 'actif', 4),
(71, NULL, 'FEZA', 'BUROKO', 'MARIE', 'F', 'GOMA', 'CONGOLAISE', 'HIMBI, KISANGANI, 112', '2012-11-21', NULL, 'BUROKO BUCHEKABIRI', 'MEMITA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7482', NULL, 'actif', 4),
(72, NULL, 'N\'SIMIRE', 'MINANI', 'CARINE', 'F', 'IDJWI', 'CONGOLAISE', 'KATOYI, KILIMANJARO, 40', '2011-12-25', NULL, 'TUOMBE MASIMANGO', 'KIBALONZA CHIKURU', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7485', NULL, 'actif', 4),
(73, NULL, 'BAHATI', 'NYAKANA', 'ADELINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 213', '2019-06-10', NULL, 'BAHATI NYAKANA', 'NABINTU RUTAKANGWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7554', NULL, 'actif', 4),
(74, NULL, 'MARIAMU', 'BULABULA', 'PLAMEDI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2007-09-22', NULL, 'KALINDULA', 'CHANGA', 'MANIEMA', 'KINDU', NULL, NULL, NULL, '7569', NULL, 'actif', 4),
(75, NULL, 'RIZIKI', 'MBAYO', 'RUTH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 104', '2007-09-08', NULL, 'GHISLAIN MBAYO', 'GLORIEUSE KABUNGO', 'MANIEMA', 'KASONGO', NULL, NULL, NULL, '7579', NULL, 'actif', 4),
(76, NULL, 'KAHINDO', 'MUKONDANO', 'NEEMA', 'F', 'BUTEMBO', 'CONGOLAISE', 'NGANGI III, NTINGI, 58', '2016-02-24', NULL, 'NZANZU SIKAVYAHOLO GRACE', 'NZIAVAKE MUPOLE PENDEZA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7589', NULL, 'actif', 4),
(77, NULL, 'KAVIRA', 'MUKONDANO', 'JUSY', 'F', 'KINSHASA', 'CONGOLAISE', 'NGANGI, NTINGI, 58', '2018-09-26', NULL, 'NZANZU SIKAVYAHOLO GRACE', 'NZIAVAKE MUPOLE PENDEZA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7588', NULL, 'actif', 4),
(78, NULL, 'MASOKA', 'FAZILI', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 642', '2019-10-05', NULL, 'FAZILI MAMBO', 'ANGE BAHATI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7587', NULL, 'actif', 4),
(79, NULL, 'ANDEMA', 'MURHULA', 'DIVINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 21', '2017-02-14', NULL, 'MURHULA CHIZUNGU', 'NYOTA MUKESHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7594', NULL, 'actif', 4),
(80, NULL, 'ANSIMA', 'MURHULA', 'CHARMANTE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 21', '2015-06-16', NULL, 'MURHULA CHIZUNGU', 'NYOTA MUKESHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7593', NULL, 'actif', 4),
(81, NULL, 'NEEMA', 'HAMULI', 'MASTAKI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 092', '2012-09-25', NULL, 'HAMULI MASTAKI', 'MARIA YAMFASHIJE', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7590', NULL, 'actif', 4),
(82, NULL, 'FURAHA', 'NDATABAYE', 'MARTH', 'F', 'BUKAVU', 'CONGOLAISE', 'HIMBI, BARAKA, 12', '2009-04-04', NULL, 'ISAAC NDATABAYE', 'IRENE MUZABANYA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7631', NULL, 'actif', 4),
(83, NULL, 'KAVIRA', 'KAMATE', 'YADA', 'F', 'GOMA', 'CONGOLAISE', 'MAJENGO, MULINDWA, 320', '2007-11-27', NULL, 'MUMBERE BETHEL', 'KYAKUMWA LOUISE', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7636', NULL, 'actif', 4),
(84, NULL, 'KESHI', 'BANGA', 'LAURIETTE', 'F', 'WALIKALE', 'CONGOLAISE', 'NGANGI, NTINGI, 311', '2013-07-15', NULL, 'FRANCOIS ILUMBA', 'CHANCE BIHANGO', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7637', NULL, 'actif', 4),
(85, NULL, 'NABWEZA', 'BUHENDWA', 'JOSEPHINE', 'F', 'WALUNGU', 'CONGOLAISE', 'NGANGI, NTINGI, 023', '2008-12-08', NULL, 'BUHENDWA', 'KINJA BYAMUNGU', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '7633', NULL, 'actif', 4),
(86, NULL, 'SALAMA', 'NDOOLE', 'AURELIE', 'F', 'GOMA', 'CONGOLAISE', 'KASIKA, BIKINDWE, 0064', '2010-04-21', NULL, 'NDOOLE MATATA', 'NDOOLE DUNIA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7660', NULL, 'actif', 4),
(87, NULL, 'USHINDI', 'BIJIRA', 'KAMUZINZI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 40', '2019-10-01', NULL, 'BIJIRA KAMUZINZI', 'AZIZA KASI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7654', NULL, 'actif', 4),
(88, NULL, 'NEEMA', 'ILUMBA', 'GRACE', 'F', 'LUBUTU', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2015-07-01', NULL, 'ILUMBA', 'CHANCE BIHANGO', 'MANIEMA', 'LUBUTU', NULL, NULL, NULL, '7662', NULL, 'actif', 4),
(89, NULL, 'DOGO', 'MUHIMA', 'NOELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 129', '2009-12-25', NULL, 'DOGO', 'ANITA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7701', NULL, 'actif', 4),
(90, NULL, 'FARIJIKA', 'MUTARAHAZA', 'GRACIEUSE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 82', '2019-07-25', NULL, 'BIENDA MUTARAHAZA', 'FURAHA MASUDI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7708', NULL, 'actif', 4),
(91, NULL, 'ASHUZA', 'RUBENGA', 'DELICE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TEECHNICIENS, 21', '2019-07-02', NULL, 'ADELARD RUBENGA', 'MASORO BYAMUHANYA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7718', NULL, 'actif', 4),
(92, NULL, 'MARIAMU', 'MUSA', 'MELISSA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 023', '2020-06-28', NULL, 'MUSA BINDA', 'MWANVUWA LUKUSA', 'SUD-KIVU', 'FIZI', NULL, NULL, NULL, '7714', NULL, 'actif', 4),
(93, NULL, 'MBOMBO', 'TSHIMANGA', 'EMILIANNE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2009-02-26', NULL, 'TSHIANGA', 'KATUNGU SAFI', 'TSHOPO', NULL, NULL, NULL, NULL, '7739', NULL, 'actif', 4),
(94, NULL, 'MITONGA', 'TSHIMANGA', 'KETSIAH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2016-09-23', NULL, 'TSIANGA TSHIMANGA', 'KATUNGU SAFI', 'TSHOPO', NULL, NULL, NULL, NULL, '7740', NULL, 'actif', 4),
(95, NULL, 'TUMBA', 'TSHIMANGA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2011-02-09', NULL, 'TSHIONGO TSHIMANGA', 'KATUNGU SAFI', 'TSHOPO', NULL, NULL, NULL, NULL, '7741', NULL, 'actif', 4),
(96, NULL, 'BAHATI', 'KANGOLINGOLI', 'JOSEPHINE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 223', '2009-05-07', NULL, 'AUGUSTIN', 'BULONZA BAHATI AMIERANCE', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '7745', NULL, 'actif', 4),
(97, NULL, 'MELINE', 'MITULA', 'BEATRICE', 'F', 'KAMITUGA', 'CONGOLAISE', NULL, '2009-03-11', NULL, 'MITULA', 'VENANCIE', NULL, NULL, NULL, NULL, NULL, '7750', NULL, 'actif', 4),
(98, NULL, 'BANDU', 'SOFIA', 'CHRISTELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 23', '2018-02-02', NULL, 'GUILLAUME', 'KASKOTI', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7764', NULL, 'actif', 4),
(99, NULL, 'MASHUKANO', 'BAHATI', 'ANGE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2009-09-10', NULL, 'MASUKANO BAHATI', 'FEZA BAHATI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7756', NULL, 'actif', 4),
(100, NULL, 'MASHUKANO', 'SALUMU', 'GALE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 23', '2020-08-02', NULL, 'MASHUKANO FAIDA', 'FEZA BAHATI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7758', NULL, 'actif', 4),
(101, NULL, 'OLAME', 'MUHIGIRWA', 'SHEKINAH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2018-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7761', NULL, 'actif', 4),
(102, NULL, 'SIFA', 'SAFARI', 'KERENE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI', '2022-03-06', NULL, 'ALAIN', 'ALICE BEGA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7752', NULL, 'actif', 4),
(103, NULL, 'FEZA', 'BUROKO', 'MARIE', 'F', 'GOMA', 'CONGOLAISE', 'HIMBI, KISANGANI, 112', '2012-11-21', NULL, 'BUROKO BUCHEKABIRI', 'MEMITA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7770', NULL, 'actif', 4),
(104, NULL, 'TULIA', 'BADERHA', 'ABIGAEL', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2011-04-06', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7769', NULL, 'actif', 4),
(105, NULL, 'OLAME', 'RUTAHA', 'QUEEN', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, MAENDELO, 11', '2021-03-02', NULL, 'RUTAHA CHRISTIAN', 'NOELA BISIMWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7787', NULL, 'actif', 4),
(106, NULL, 'MUHIIRWA', 'BALINDA', 'GABRIELLA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOHWA, 029', '2015-04-14', NULL, 'BALINDA', 'ESPERANCE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7791', NULL, 'actif', 4),
(107, NULL, 'ANSIMA', 'BALOLEBWAMI', 'GRADY', 'F', 'BUKAVU', 'CONGOLAISE', 'NGNAGI 3', '2018-06-14', NULL, 'JULIEN BALOLEBWAMI', 'REHEMA NDALA', 'HAUT KATANGA', 'HAUT KATANGA', NULL, NULL, NULL, '7804', NULL, 'actif', 4),
(108, NULL, 'KWINJA', 'ZIHALIRWA', 'ASIFIWE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-10-10', NULL, 'ZIHALIRWA KALUMUNA', 'MWAMINI SHISHIBANJI', 'SUD-KIVU', 'kabare', NULL, NULL, NULL, '7805', NULL, 'actif', 4),
(109, NULL, 'MUNYERENKANA', 'BALOLEBWAMI', 'Merdi', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI 3, TINGI, 23', '2019-07-10', NULL, 'BALOLEBWAMI JULIEN', 'REHEMA NDALA', 'HAUT KATANGA', 'HAUT KATANGA', NULL, NULL, NULL, '7803', NULL, 'actif', 4),
(110, NULL, 'ANDEMA', 'RUKARA', 'NGUZA', 'F', 'KISANGANI', 'CONGOLAISE', 'NGANGI, BIHIRWA, 011', '2020-12-20', NULL, 'MURHULA', 'CHRISTINE', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '7860', NULL, 'actif', 4),
(111, NULL, 'BAKARI', 'LATIFA', 'ALICE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS', '2020-02-05', NULL, 'BAKARI DJUMA', 'GLORIA MUGOLI', 'SUD-KIVU', 'FIZI', NULL, NULL, NULL, '7837', NULL, 'actif', 4),
(112, NULL, 'WITANENE', 'WABULASA', 'JOSEPHINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2019-04-24', NULL, NULL, NULL, 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7829', NULL, 'actif', 4),
(113, NULL, 'ADJATI', 'BALEKAGE', 'JOSEPHINE', 'F', 'BULENGA', 'CONGOLAISE', 'NGANGI, KAYEMBE, 12', '2012-11-15', NULL, 'AMANI BALEKAGE', 'SIFA MANEGABE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7887', NULL, 'actif', 4),
(114, NULL, 'ANIFA', 'BALEKAGE', 'AMIDA', 'F', 'BULENGA', 'CONGOLAISE', 'NGANGI, KAYEMBE, 12', '2009-11-24', NULL, 'AMANI BALEKAGE', 'SIFA MANEGABE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7886', NULL, 'actif', 4),
(115, NULL, 'KIVALWA', 'LUVUNDU', 'VANESSA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI', '2016-09-12', NULL, 'TSHONGO', 'KAVIRA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7874', NULL, 'actif', 4),
(116, NULL, 'MUKUNDWA', 'LUGASE', 'PRISCILLE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, MAENDELEO, 21', '2010-06-26', NULL, 'SAMWEL KALEGAMIRE', 'CHIBALONZA CHIHAMYA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7915', NULL, 'actif', 4),
(117, NULL, 'NSIMIRE', 'BEBE', 'LAURIENNE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2011-12-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7914', NULL, 'actif', 4),
(118, NULL, 'PEMBA', 'MAKOSO', 'ESTHER', 'F', 'MASISI', 'CONGOLAISE', 'KATOYI, VISOKE, 21', '2015-05-17', NULL, 'MAKOSO', 'BINDU', 'BAS-UELE', NULL, NULL, NULL, NULL, '7894', NULL, 'actif', 4),
(119, NULL, 'SHAKIRA', 'BALEKAGE', 'MARCELINE', 'F', 'BULENGA', 'CONGOLAISE', 'NGANGI, KAYEMBE, 12', '2012-03-08', NULL, 'AMANI BALEKAGE', 'SIFA MANEGABE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7885', NULL, 'actif', 4),
(120, NULL, 'ZAWADI', 'BINDU', 'LYDIA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2018-04-28', NULL, 'KAURO BINDU', 'EMILIE CHIBALONZA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7883', NULL, 'actif', 4),
(121, NULL, 'ABIGAEL', 'BISIMWA', 'HAPPY', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2016-08-19', NULL, NULL, NULL, 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7933', NULL, 'actif', 4),
(122, NULL, 'WAKUBENGA', 'JUMA', 'QUETIAH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2012-12-20', NULL, 'WAKUBENGA', 'CLARISSE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7941', NULL, 'actif', 4),
(123, NULL, 'MAOMBI', 'IMANI', 'PRECIEUSE', 'F', 'PINGA', 'CONGOLAISE', 'NGANGI III, NTINGI, 93', '2020-09-23', NULL, 'MUZALIYA', 'RIZIKI', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7963', NULL, 'actif', 4),
(124, NULL, 'MUNEZERO', 'DUSENGE', 'CALEB', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 023', '2012-11-20', NULL, 'DUSENGE', 'DEBORAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7986', NULL, 'actif', 4),
(125, NULL, 'ASHURA', 'MUBAWA', 'BAIBIKA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 143', '2019-11-28', NULL, 'KIKANDI MUBAWA ABDOUL', 'ZAINABO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8019', NULL, 'actif', 4),
(126, NULL, 'BARAKA', 'MUNGUARAHABA', 'GLOIRE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, LUASHI, 034', '2013-05-28', NULL, 'MUGUARAHABA PATRICK', 'FURAHA NAMUSI', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '7995', NULL, 'actif', 4),
(127, NULL, 'MAKITO', 'KABUNGULU', 'VERDIANAH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI III', '2018-12-18', NULL, 'OMERE', 'FAIDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7968', NULL, 'actif', 4),
(128, NULL, 'MASIKA', 'SANGO', 'SHIMENE', 'F', 'KIROTSHE', 'CONGOLAISE', 'KATOYI, KISIBANGI, 23', '2006-01-24', NULL, 'OLIVIER', 'KAVUGHO SANGO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8011', NULL, 'actif', 4),
(129, NULL, 'N\'SIMIRE', 'IRENGE', 'PATRICIA', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2015-04-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7970', NULL, 'actif', 4),
(130, NULL, 'UWEZO', 'RHULINABO', 'NOELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI', '2010-12-25', NULL, 'MBOGO BAHIGA', 'ZAHABU YOLEN', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8006', NULL, 'actif', 4),
(131, NULL, 'NABINTU', 'KALIMIRA', 'SYNTHIA', 'F', 'BUZI', 'CONGOLAISE', 'NGANGI, NTINGI, 90', '2010-09-22', NULL, 'GILBERT MAOMBI', 'BWEMA MULEZI', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '8052', NULL, 'actif', 4),
(132, NULL, 'ANSIMA', 'KATENGURA', 'GUILAINE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, MAENDELEO, 871', '2017-02-26', NULL, 'MURHULA KATENGURA GUILAIN', 'RIZIKI RHULINABO MEDIATRICE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8062', NULL, 'actif', 4),
(133, NULL, 'BEMIRENE', 'KANDUNDAO', 'SAMUELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 043', '2016-02-03', NULL, 'BAKABAANITSA KANDUNDAO', 'UWEZO ALICE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8197', NULL, 'actif', 4),
(134, NULL, 'KANDUNDAO', 'HANGI', 'SARAH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 021', '2010-08-01', NULL, 'BITASIMWA HANGI', 'NZIGIRE MWAMBUSA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8189', NULL, 'actif', 4),
(135, NULL, 'FURAHA', 'CIRUZA', 'CLOTILDE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, KISIBANGI, 219', '2020-06-25', NULL, 'CIRUZA MUHINDO', 'MUGOLI CHANCELINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8263', NULL, 'actif', 4),
(136, NULL, 'NSIMIRE', 'NAMEGABE', 'LAURIENNE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 012', '2011-12-07', NULL, 'NAMEGABE BUHENDWA', 'FURAHA MUGARUKA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8337', NULL, 'actif', 4),
(137, NULL, 'BAENI', 'MAFULUKO', 'JOYCE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 342', '2015-03-03', NULL, 'BAYOMBA MUHIMA', 'HANGI BWIRA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8338', NULL, 'actif', 4),
(138, NULL, 'AHANA', 'MUHINDO', 'MARVIN', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2016-10-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8259', NULL, 'actif', 4),
(139, NULL, 'AKILIMALI', 'AMANI', 'JOSPIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 029', '2018-04-15', NULL, 'AMANI', 'REBECCA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8344', NULL, 'actif', 4),
(140, NULL, 'AKONKWA', 'MUHINDO', 'JAMES', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2019-05-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8262', NULL, 'actif', 4),
(141, NULL, 'AKONKWA', 'MURHULA', 'PLACIDE', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI', '2017-12-29', NULL, 'MURHULA', 'ALOHONCINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8175', NULL, 'actif', 4),
(142, NULL, 'AKONKWA', 'MYANZIRO', 'ALLIANCE', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2014-01-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8180', NULL, 'actif', 4),
(143, NULL, 'ALIOMBA', 'BATASEMA', 'PAULIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI III, NTINGI, 9230', '2021-11-04', NULL, 'OMBENI BATASEMA DIDIER', 'ALICE NZIGIRE NTACHO', 'SUD-KIVU', 'KATANA', NULL, NULL, NULL, '8141', NULL, 'actif', 4),
(144, NULL, 'ASIFIWE', 'KAMETRE', 'GASTON', 'M', 'GOMA', 'CONGOLAISE', NULL, '2012-06-20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8275', NULL, 'actif', 4),
(145, NULL, 'BAGUMA', 'SHABANI', 'JEAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI III', '2020-05-10', NULL, 'SHABANI', 'BUTU', 'NORD-KIVU', NULL, NULL, NULL, NULL, '8204', NULL, 'actif', 4),
(146, NULL, 'BAHATI', 'AMANI', 'JULIEN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI III', '2020-12-24', NULL, 'AMANI', 'REBECCA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8345', NULL, 'actif', 4),
(147, NULL, 'BALUME', 'BALISHA', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', 'NDOSHO, LUAPULA', '2019-10-14', NULL, 'BALISHA JEAN PIERRE', 'KIVATI ESTHER', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8308', NULL, 'actif', 4),
(148, NULL, 'BARAKA', 'BAHATI', 'JORDAN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, VITSHUMBI, 16', '2016-04-06', NULL, 'BAHATI LWABOSHI', 'MAPENZI CIDORO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8078', NULL, 'actif', 4),
(149, NULL, 'BISIMWA', 'HERI', 'GLOILE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 092', '2010-11-02', NULL, 'HERI MATABISHI', 'FRANCINE MULUME', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '8199', NULL, 'actif', 4),
(150, NULL, 'BISIMWA', 'MUDHERWA', 'MOISE', 'M', 'GOMA', 'CONGOLAISE', NULL, '2008-10-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8184', NULL, 'actif', 4),
(151, NULL, 'BUUMA', 'BALISHA', 'RUBIN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2022-08-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8306', NULL, 'actif', 4),
(152, NULL, 'BYAMUNGU', 'LUBULA', 'URBAIN', 'M', 'MINOVA', 'CONGOLAISE', NULL, '2010-09-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8195', NULL, 'actif', 4),
(153, NULL, 'CHIZA', 'KABUMBANYUNGU', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', NULL, '2020-05-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8249', NULL, 'actif', 4),
(154, NULL, 'ELISHA', 'BAZIGA', 'GLOIRE', 'M', 'MINOVA', 'CONGOLAISE', NULL, '2011-02-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8111', NULL, 'actif', 4),
(155, NULL, 'ELIYA', 'BAZIGA', 'JUSTIN', 'M', 'MINOVA', 'CONGOLAISE', NULL, '2017-01-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8112', NULL, 'actif', 4),
(156, NULL, 'IDI', 'RAMAZANI', 'HUSSEIN', 'M', 'RUBAYA', 'CONGOLAISE', 'NGANGI, NTINGI, 70', '2016-07-07', NULL, 'MUGISHA RAMAZANI', 'ASIYATI ARUNA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8213', NULL, 'actif', 4),
(157, NULL, 'IMANI', 'LUAGALIKA', 'GRADI', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2010-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8179', NULL, 'actif', 4),
(158, NULL, 'KANYERE', 'LUSASA', 'SHARON', 'M', 'GOMA', 'CONGOLAISE', NULL, '2012-09-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8151', NULL, 'actif', 4),
(159, NULL, 'KASEREKA', 'MBAWEKA', 'JOEL', 'M', 'GOMA', 'CONGOLAISE', NULL, '2015-12-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8143', NULL, 'actif', 4),
(160, NULL, 'LUMOO', 'BALISHA', 'PASCAL', 'M', 'GOMA', 'CONGOLAISE', NULL, '2016-03-22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8307', NULL, 'actif', 4),
(161, NULL, 'MAJALIWA', 'MATENDA', 'DANIEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 0293', '2017-02-26', NULL, 'MATENDA', 'RIZIKI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8258', NULL, 'actif', 4),
(162, NULL, 'MBAKU', 'KABUYAYA', 'CHARLES', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DE TECHNICIEN', '2020-06-05', NULL, 'MBAKU', 'ESTHER', 'MANIEMA', 'KIBOMBO', NULL, NULL, NULL, '8086', NULL, 'actif', 4),
(163, NULL, 'MEMA', 'MATENDA', 'LUC', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 007', '2015-01-28', NULL, 'MATENDA', 'RIZIKI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8256', NULL, 'actif', 4),
(164, NULL, 'MUGISHO', 'KAMUZINZI', 'BEN', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2009-02-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8323', NULL, 'actif', 4),
(165, NULL, 'MUHINDO', 'KISYANGA', 'JOSEPH', 'M', 'GOMA', 'CONGOLAISE', NULL, '2018-12-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8149', NULL, 'actif', 4),
(166, NULL, 'MUKANDIRWA', 'MUHINDO', 'MACHAEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 392', '2018-11-14', NULL, 'MUHINDO JONAS', 'BINDU BANYANGA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8226', NULL, 'actif', 4),
(167, NULL, 'MULIRO', 'YASONI', 'CONSTANTIN', 'M', 'PINGA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2013-05-31', NULL, NULL, NULL, 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8161', NULL, 'actif', 4),
(168, NULL, 'MULYANGA', 'KABUMBANYUNGU', 'EMILE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI', '2011-09-09', NULL, 'KITOGA KABUMBANYUNGU', 'N\'SIMIRE MWACHOKOLA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8246', NULL, 'actif', 4),
(169, NULL, 'MURHULA', 'MUSHAGALUSA', 'GLOIRE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 273', '2019-10-22', NULL, 'MUSHAGALUSA KIRIZA', 'ESHTER SHUKURU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8266', NULL, 'actif', 4),
(170, NULL, 'MWENYEMALI', 'MINANI', 'JUSLAIN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2016-06-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8271', NULL, 'actif', 4),
(171, NULL, 'NGABO', 'MUSAFIRI', 'CELESTIN', 'M', 'WALIKALE', 'CONGOLAISE', 'NGANGI', '2018-11-21', NULL, 'OLIVIER MUSAFIRI', 'ALICE ZAWADI', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8029', NULL, 'actif', 4),
(172, NULL, 'OLAME', 'KARUBANDIKA', 'JEAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, KAYEMBE, 83', '2011-11-23', NULL, 'CHIRIMWAMI KARUBANDIKA', 'CHIZA RUGOMBA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8140', NULL, 'actif', 4),
(173, NULL, 'SONGA', 'KABUMBANYUNGU', 'DIVIN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2016-09-09', NULL, NULL, NULL, 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8248', NULL, 'actif', 4),
(174, NULL, 'WENGA', 'KINYONYI', 'DANIEL', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, KISIBANGI, 021', '2010-06-30', NULL, NULL, NULL, 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8303', NULL, 'actif', 4),
(175, NULL, 'ZULINGA', 'MANOSE', 'ANDRE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KASINDI, 092', '2012-10-29', NULL, 'MANOSE', 'NICOLE FURAHA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8091', NULL, 'actif', 4),
(176, NULL, 'AISHIMUNGU', 'YOSHUWA', 'DARCEL', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 023', '2020-09-05', NULL, 'YOSHUWA CHRISTIAN', 'KOKO ESTHER', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7487', NULL, 'actif', 4),
(177, NULL, 'MYAYUKU', 'SUZA', 'DIEUDINNEE', 'M', 'MATADI', 'CONGOLAISE', 'NGANGI, NTINGI, 023', '2017-06-29', NULL, 'SUZA RENE', 'EMELEMEKIYA BAZINGA', 'KONGO CENTRAL', 'LUHOZI', NULL, NULL, NULL, '7483', NULL, 'actif', 4),
(178, NULL, 'CHASINGA', 'MUSHAMUKA', 'JACQUES', 'M', 'KISANGANI', 'CONGOLAISE', 'NGANI, NITNGI, 0123', '2019-04-09', NULL, 'NAMIGOGO', 'BENKANDA KOSE MARIE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7512', NULL, 'actif', 4),
(179, NULL, 'DUMEDUME', 'MUSHAMUKA', 'JEREMIE', 'M', 'WALIKALE', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2010-02-19', NULL, 'NAMIGOGO MUSHAMUKA', 'MARIE BEKANDA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7514', NULL, 'actif', 4),
(180, NULL, 'ATENDA', 'KASISI', 'KELVIN', 'M', 'GOMA', 'CONGOLAISE', 'KIZIBA 2, ZIZI, 292', '2012-10-07', NULL, 'MANENO RUHEMBI', 'SIFA OMBENI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7534', NULL, 'actif', 4),
(181, NULL, 'CHAKO', 'MULIRO', 'EMMANUEL', 'M', 'PINGA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2011-12-24', NULL, 'MUKANDIRWA MUSALIYA', 'RIZIKI MANGAZA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7570', NULL, 'actif', 4),
(182, NULL, 'ASSUMANI', 'MBAYO', 'TRISTAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 102', '2017-12-08', NULL, 'ASSUMANI MBAYO', 'GLORIEUSE KABUNGO', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '7577', NULL, 'actif', 4),
(183, NULL, 'ASUMANI', 'MBAYO', 'JASON', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 122', '2019-07-17', NULL, 'GHYSLAIN MBAYO', 'GLORIEUSE', 'MANIEMA', 'KASONGO', NULL, NULL, NULL, '7576', NULL, 'actif', 4),
(184, NULL, 'MURHULA', 'MUSHAGALUSA', 'GLOIRE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 273', '2019-10-22', NULL, 'MUSHAGALUSA KIRIZA', 'ESHTER SHUKURU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7584', NULL, 'actif', 4),
(185, NULL, 'HAMULI', 'MASTAKI', 'NELSON', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 092', '2010-03-03', NULL, 'HAMULI MASTAKI', 'MARIA YAMFASHIJE', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7591', NULL, 'actif', 4),
(186, NULL, 'IRAGI', 'CHIRHUZA', 'MOISE', 'M', 'NUMBI', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 21', '2015-06-15', NULL, 'MURHULA CHIZUNGU', 'NYOTA MUKESHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7595', NULL, 'actif', 4),
(187, NULL, 'AHMADI', 'NDOOLE', 'JOSEPH', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 90', '2019-10-17', NULL, 'NDOOLE MAKELE', 'FATUMA RAJABU', 'NORD-KIVU', 'MAMSISI', NULL, NULL, NULL, '7605', NULL, 'actif', 4),
(188, NULL, 'KIBANJA', 'BANDU', 'JEAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2013-05-05', NULL, 'BANDU SONGO', 'REBECCA JUMA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7598', NULL, 'actif', 4),
(189, NULL, 'SONGO', 'BANDU', 'BIENVENU', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2020-01-01', NULL, 'BANDU SONGO', 'REBECCA JUMA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7599', NULL, 'actif', 4),
(190, NULL, 'KESHI', 'MBURA', 'LAURENT', 'M', 'WALIKALE', 'CONGOLAISE', 'NGANGI, NTINGI, 102', '2011-07-01', NULL, 'FRANCOIS ILUMBA', 'CHANCE BIHANGO', 'SUD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7638', NULL, 'actif', 4),
(191, NULL, 'USHINDI', 'NDOOLE', 'GRACE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, BIKINDWE, 64', '2013-05-13', NULL, 'NDOOLE MATATA', 'NDOOLE DUNIA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7661', NULL, 'actif', 4),
(192, NULL, 'KINGOMBE', 'ILUMBA', 'ALOFO', 'M', 'LUBUTU', 'CONGOLAISE', 'NGANGI, NTINGI, 92', '2020-09-03', NULL, 'FRANCOIS ILUMBA', 'CHANCE  BIHANGO', 'NORD-KIVU', 'LUBUTU', NULL, NULL, NULL, '7663', NULL, 'actif', 4),
(193, NULL, 'MUSHAGALUSA', 'CHISHUGI', 'JAPHET', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 34', '2012-11-04', NULL, 'CHISHUGI CHIZUNGU', 'LUSAMBO BUHENDWA', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '7666', NULL, 'actif', 4),
(194, NULL, 'ABAHO', 'BUGALE', 'URIEL', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 43', '2020-05-20', NULL, 'MAOMBI BUGALE CHRISTIAN', 'UWEZO LWABOSHI', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7700', NULL, 'actif', 4),
(195, NULL, 'FARAJA', 'MUTARAHAZA', 'BLESSING', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 82', '2017-05-13', NULL, 'BIENDA MUTARAHAZA', 'FURAHA MASUDI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7707', NULL, 'actif', 4),
(196, NULL, 'ABAHO', 'BUGALE', 'URIEL', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 12', '2020-05-20', NULL, 'MAOMBI BUGALE CHRISTIAN', 'UWEZO LWABOSHI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7732', NULL, 'actif', 4),
(197, NULL, 'KARIMU', 'MUSSA', 'AMZA', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2011-11-11', NULL, 'MUSSA', 'LEILAH', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '7727', NULL, 'actif', 4),
(198, NULL, 'MUGISHO', 'BAHATI', 'ARNOLDE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI III, TINGI, 931', '2015-04-17', NULL, 'BAHATI', 'RACHEL', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7733', NULL, 'actif', 4),
(199, NULL, 'RAPAHEL', 'KAMBAZI', 'FUNDIKO', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 012', '2018-08-25', NULL, 'EMMANUEL KAMBAZI', 'FATUMA HANGI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7742', NULL, 'actif', 4),
(200, NULL, 'GAEL', 'KAMBAZI', 'RWEFU', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 012', '2020-08-25', NULL, 'EMMANUEL KAMBAZI', 'FATUMA HANGI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7743', NULL, 'actif', 4),
(201, NULL, 'KABUNGULU', 'JUMA', 'VAINQUEUR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 012', '2014-07-14', NULL, 'KABUNGULU JUMA OMER', 'FAIDA NGESO', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '7751', NULL, 'actif', 4),
(202, NULL, 'ABDOU', 'RAMAZANI', 'DJUMA', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI III, NTINGI, 70', '2014-11-14', NULL, 'MUGISHA RAMAZANI', 'ASIYATI ARUNA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7760', NULL, 'actif', 4),
(203, NULL, 'MASHUKANO', 'BARONYI', 'DANIEL', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2008-04-24', NULL, 'MASHUKANO FAIDA', 'FEZA BAHATI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7757', NULL, 'actif', 4),
(204, NULL, 'NKUBA', 'KASKOTI', 'DANIEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 13', '2009-07-28', 6, 'GUILLAUME', 'KASKOTI', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7765', NULL, 'actif', 4),
(205, NULL, 'SHENGOMA', 'BAKUSU', 'LAIC', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2011-06-09', NULL, 'SHENGOMA FABRICE', 'MAOMBI NYAMWAKA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7767', NULL, 'actif', 4),
(206, NULL, 'KASILEMBO', 'LUSAMBYA', 'CLEMENT', 'M', 'GOMA', 'CONGOLAISE', NULL, '2015-09-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7774', NULL, 'actif', 4),
(207, NULL, 'MILINGANYO', 'MUNENE', 'DANIEL', 'M', 'GOMA', 'CONGOLAISE', NULL, '2017-02-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7775', NULL, 'actif', 4),
(208, NULL, 'MILINGANYO', 'MUNENE', 'KENEDY', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 212', '2012-08-23', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7772', NULL, 'actif', 4),
(209, NULL, 'MILINGANYO', 'MUNENE', 'YANNICK', 'M', 'GOMA', 'CONGOLAISE', 'KATPYI, ZIZI, 212', '2008-08-27', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7773', NULL, 'actif', 4),
(210, NULL, 'KIBANJA', 'BALINDA', 'EMERY', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 093', '2016-06-30', NULL, 'BALINDA', 'ESPERANCE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7790', NULL, 'actif', 4),
(211, NULL, 'BAHATI', 'BUSANYA', 'JOSUE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2021-09-03', NULL, 'BUSANYA KABALI', 'SIUZIKE NABURAMBI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7825', NULL, 'actif', 4),
(212, NULL, 'KAMBALE', 'SENGEMOYA', 'KALEBU', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2015-07-07', NULL, 'KAKULE DANIEL', 'NAOMIE KIBONGE', 'NORD-KIVU', 'BENI', NULL, NULL, NULL, '7817', NULL, 'actif', 4),
(213, NULL, 'MWISHA', 'BAENI', 'GRADY', 'M', 'BENI', 'CONGOLAISE', 'KATOYI, ZIZI, 24', '2017-09-19', NULL, 'CHRISTIAN KABONGOLI', 'ARIDJA KAHINDO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7816', NULL, 'actif', 4),
(214, NULL, 'TUMAINI', 'HATEGEKA', 'JUNIOR', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2021-04-09', NULL, 'SHUKURU HATEGEKA', 'REHEMA BUSANYA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7824', NULL, 'actif', 4),
(215, NULL, 'FARAYI', 'KASASI', 'MICHEL', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2017-08-17', NULL, NULL, NULL, 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7830', NULL, 'actif', 4),
(216, NULL, 'MAGAJU', 'BAVURIKI', 'BENJAMIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, KAKOTI, 066', '2013-02-07', NULL, 'BAKURIKI MAGAJU', 'LUCIE MUNGALA', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '7834', NULL, 'actif', 4),
(217, NULL, 'BANZA', 'TSHIKALA', 'PARVIS', 'M', 'KISANGANI', 'CONGOLAISE', 'KATOYI, NDOWE', '2012-10-10', NULL, 'BANZA TSHIKALA PAPY', 'TUTUMSIFU NDOOLE MELYSSA', 'LOMAMI', 'KOLWEZI', NULL, NULL, NULL, '7918', NULL, 'actif', 4),
(218, NULL, 'DAMAS', 'RAMAZANI', 'JORDAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2021-05-02', NULL, 'EDMOND RAMAZANI', 'ELODY RAMAZANI', 'MANIEMA', 'KASONGO', NULL, NULL, NULL, '7913', NULL, 'actif', 4),
(219, NULL, 'ERAYI', 'KATAKEYA', 'JULIEN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2016-07-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7866', NULL, 'actif', 4),
(220, NULL, 'KAYUMBU', 'BENJAMIN', 'GIFT', 'M', 'GOMA', 'CONGOLAISE', NULL, '2019-05-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7877', NULL, 'actif', 4),
(221, NULL, 'MBELEESENGO', 'MAKOSO', 'MOISE', 'M', 'MASISI', 'CONGOLAISE', 'KATOYI, VISOKE, 336', '2014-10-25', NULL, 'MABYALA MAKOSO', 'AMANI BINDU', 'BAS-UELE', NULL, NULL, NULL, NULL, '7895', NULL, 'actif', 4),
(222, NULL, 'MURHULA', 'MANEGABE', 'JUSLAIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2021-04-07', NULL, NULL, NULL, 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7891', NULL, 'actif', 4),
(223, NULL, 'SAMUEL', 'KASONGO', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 031', '2011-08-20', NULL, 'WILLYAM WABULASA', 'FURAHA MUUMA', 'SUD-KIVU', 'FIZI', NULL, NULL, NULL, '7878', NULL, 'actif', 4),
(224, NULL, 'ISENGA', 'AMANI', 'DANIEL', 'M', 'UVIRA', 'CONGOLAISE', 'KAYOYI, DES TECHNICIENS, 041', '2014-10-29', NULL, 'ISENGA', 'ASHA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7954', NULL, 'actif', 4),
(225, NULL, 'KIBANJA', 'BANDU', 'JEAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2013-05-05', NULL, 'BANDU SONGO', 'REBECCA JUMA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7940', NULL, 'actif', 4),
(226, NULL, 'MURHULA', 'IRENGE', 'PATRICK', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, KISIBANGI, 21', '2012-11-01', NULL, 'IRENGE JOSUE', 'RODA MWAMINI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7969', NULL, 'actif', 4),
(227, NULL, 'SONGO', 'BANDU', 'BIENVENU', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2020-01-01', NULL, 'BANDU SONGO', 'REBECCA JUMA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7942', NULL, 'actif', 4),
(228, NULL, 'ELIYA', 'IMANI', 'PROMESSE', 'M', 'PINGA', 'CONGOLAISE', 'NGANGI III, NTINGI, 93', '2021-06-26', NULL, 'IMANI', 'RACHEL', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7962', NULL, 'actif', 4),
(229, NULL, 'MUBAWA', 'SAFARI', 'SHABANI', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 143', '2018-05-05', NULL, 'KIKANDI MUBAWA ABDOUL', 'ZAINABO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8018', NULL, 'actif', 4),
(230, NULL, 'ANSIMA', 'IRENGE', 'PATRICE', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2016-11-16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7971', NULL, 'actif', 4),
(231, NULL, 'ASINGE', 'MUHITWA', 'PAULIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2009-04-09', NULL, 'BENOIS MUHIRWA', 'BUNYERE BULEMBE', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7996', NULL, 'actif', 4),
(232, NULL, 'FARADJA', 'KABUNGULU', 'VEDASTE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI', '2016-05-16', NULL, 'OMERE', 'FAIDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7967', NULL, 'actif', 4),
(233, NULL, 'KALONJI', 'MULOLWA', 'ALPHA', 'M', 'GOMA', 'CONGOLAISE', 'KASIKA, BIKWINDE, 012', '2012-11-12', NULL, 'KALONJI MUSA', 'VALENCE BITONDO', 'KASAI', 'MBUJI-MAI', NULL, NULL, NULL, '8009', NULL, 'actif', 4),
(234, NULL, 'KUBALI', 'KIMBO', 'GLADYS', 'M', 'SHABUNDA', 'CONGOLAISE', 'KATOYI, ZIZI, 009', '2018-08-29', NULL, 'WAKUAZYA TABENA', 'APENDEKI DORCAS', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '8007', NULL, 'actif', 4),
(235, NULL, 'MWISHA', 'MUBAWA', 'YAKUTI', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI', '2016-07-10', NULL, 'ABDOUL MUBAWA', 'ZAINABO MWAKALABA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8017', NULL, 'actif', 4),
(236, NULL, 'UWEZO', 'CHIKURU', 'JOSPIN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2011-01-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7972', NULL, 'actif', 4),
(237, NULL, 'AKONKWA', 'BASHANGWA', 'SAMY', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 93', '2016-11-08', NULL, 'BAASHANGWA', 'FRANCOISE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8074', NULL, 'actif', 4),
(238, NULL, 'BAUMA', 'HANGI', 'JOSIAS', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 021', '2008-10-01', NULL, 'BITASIMWA HANGI', 'NZIGIRE MWAMBUSA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8188', NULL, 'actif', 4),
(239, NULL, 'NDANGAHEMUKA', 'HANGI', 'JOSUE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 021', '2011-12-03', NULL, 'BITASIMWA HANGI', 'NZIGIRE MWAMBUSA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8190', NULL, 'actif', 4),
(240, NULL, 'SOFIA', 'KALUME', 'DANIELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI III, USHIRIKA, 931', '2023-04-11', NULL, 'ERIC KALUME', 'DORCAS MIRINDI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7490', NULL, 'actif', 4),
(241, NULL, 'BAHATI', 'MATABARO', 'CHANCE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2008-03-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7493', NULL, 'actif', 4),
(242, NULL, 'KASHAGARIRO', 'NURU', 'OLIVE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 01', '2010-02-02', NULL, 'JEAN-MARIE MUSHEBA', 'ZAWADI BAHOSHOLA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7492', NULL, 'actif', 4),
(243, NULL, 'IMANI', 'KIKANDI', 'RACHEL', 'F', 'MASISI', 'CONGOLAISE', 'KATOYI, KISIBANGI, 932', '2009-10-09', NULL, 'KIKANDI MWETSA', 'CADETTE BAZUNGU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7496', NULL, 'actif', 4),
(244, NULL, 'LUTONDE', 'MUKAMBA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'TURUNGA, INUKA, 82', '2015-02-02', NULL, 'MUKAMBA KASEKE LEON', 'JUSTINE MASIRI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7499', NULL, 'actif', 4),
(245, NULL, 'DOGO', 'MUHIMA', 'ADELINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2010-01-02', NULL, 'DOGO MUHIMA', 'ANITA SIZA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7502', NULL, 'actif', 4),
(246, NULL, 'NABINTU', 'BALEMBA', 'RUTH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2011-01-14', NULL, 'BALEMBA JUSTIN', 'BULAMBO CLAUDINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7504', NULL, 'actif', 4);
INSERT INTO `eleves` (`id`, `matricule`, `nom`, `postnom`, `prenom`, `genre`, `lieu_naissance`, `nationalite`, `adresse`, `date_naissance`, `parent_id`, `nom_pere`, `nom_mere`, `province_origine`, `territoire`, `secteur`, `groupement`, `village`, `num_permanent`, `photo`, `statut_eleve`, `ecole_id`) VALUES
(247, NULL, 'BINJA', 'SHAMAVU', 'JULIENNE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, MUTUMAI, 83', '2010-07-16', NULL, 'JUSTIN SHAMAVU', 'NSIMIRE ELODY', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7519', NULL, 'actif', 4),
(248, NULL, 'BUSOKA', 'WILONDJA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, LOWASHI, 932', '2008-09-15', NULL, 'WILONDJA MICHEL', 'ELYSEE CHIZA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7521', NULL, 'actif', 4),
(249, NULL, 'ASIFIWE', 'MULUMEODERHWA', 'DAVINA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, ZIZI, 92', '2021-02-23', NULL, 'DAVID KAMANZI', 'GARAYABIRANAGO PASCALINE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7524', NULL, 'actif', 4),
(250, NULL, 'KATUNGU', 'VINGI', 'ESPERANCE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 93', '2009-09-24', NULL, 'VINGI ROBERT', 'MARCELINE', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7526', NULL, 'actif', 4),
(251, NULL, 'REHEMA', 'LUKABA', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, PINGA, 18', '2014-06-06', NULL, 'NTAMBUKA MESHACK', 'KIZOMBO JOSEPH', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7527', NULL, 'actif', 4),
(252, NULL, 'SABRINA', 'BUGUZI', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 64', '2013-03-13', NULL, 'CLAUDE', 'ANTOINETTE FURAHA', 'NORD-KIVU', 'BWISHA', NULL, NULL, NULL, '7528', NULL, 'actif', 4),
(253, NULL, 'BINJA', 'BUHENDWA', 'JANELLE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NINGI, 32', '2022-08-08', NULL, 'JIMMY BUHENDWA', 'IDA BILUNGA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7532', NULL, 'actif', 4),
(254, NULL, 'MUNYIRAGI', 'BAHATI', 'ORELIE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2013-05-20', NULL, 'BAHATI MWEZE', 'NYAMUTO ODACCINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7536', NULL, 'actif', 4),
(255, NULL, 'MBAYO', 'ASSUMANI', 'ROXANE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 120', '2021-09-10', NULL, 'GYSLAIN MBAYO', 'GLORIEUSE', 'MANIEMA', 'KABAMBARE', NULL, NULL, NULL, '7545', NULL, 'actif', 4),
(256, NULL, 'N\'SHOBOLE', 'BATASEMA', 'NOELLA', 'F', 'KABARE', 'CONGOLAISE', 'NGANGI, NTINGI, 91', '2012-12-20', NULL, 'CLAUDE BATASEMA', 'MARIE BUGANDWA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7546', NULL, 'actif', 4),
(257, NULL, 'ASIFIWE', 'KITUMAINI', 'BESEARCH', 'F', 'MASISI', 'CONGOLAISE', 'NDOSHO, KAKO, 83', '2012-07-06', NULL, 'KITUMAINI MAHESHE', 'BWIRA CHANCE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7547', NULL, 'actif', 4),
(258, NULL, 'WETEWABO', 'FURAHA', 'ROSALIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, KASINGA, 93', '2010-01-11', NULL, 'JACQUES FURAHA', 'ASINA LUKOO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7548', NULL, 'actif', 4),
(259, NULL, 'NABINTU', 'MUNGANGA', 'ORNELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2018-03-15', NULL, 'EMMANUEL MUNGANGA', 'NEEMA MORISHO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7551', NULL, 'actif', 4),
(260, NULL, 'FAIDA', 'MUZUSA', 'SANDRA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2012-05-31', NULL, 'DUNIA MUZUSA', 'PASCALINE BINDU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7556', NULL, 'actif', 4),
(261, NULL, 'BIBENTYO', 'MULEKUZI', 'JASMINE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 912', '2013-07-25', NULL, 'MULEKUZI CHIZUNGU', 'MARLENE FAIDA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7559', NULL, 'actif', 4),
(262, NULL, 'MWENYEMALI', 'BUGANDWA', 'ELISABETH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 42', '2011-12-15', NULL, 'MUNGANGA BUGANDWA', 'NEEMA BAGUMA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7560', NULL, 'actif', 4),
(263, NULL, 'KAHINDO', 'MUSONI', 'ANGELINAH', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 21', '2022-08-30', NULL, 'MUSOKNI KAMAVU ALBERT', 'MATHE NZIAVAKE PASCALINE', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7562', NULL, 'actif', 4),
(264, NULL, 'ANSIMA', 'KIRABUKO', 'NICOLE', 'F', 'IDJWI', 'CONGOLAISE', 'NGANGI, NTINGI', '2014-08-28', NULL, 'MISAGO KIRABUKO', 'MWAMINI', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7564', NULL, 'actif', 4),
(265, NULL, 'NABINTU', 'CHIRUZA', 'ORNELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 231', '2021-10-31', NULL, 'CHIRUZA JEAN6CLAUDE', 'RACHEL AKSANT', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7567', NULL, 'actif', 4),
(266, NULL, 'AMPIRE', 'CHIRUZA', 'ORTANCE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 231', '2020-09-10', NULL, 'CHIRUZA JEAN CLAUDE', 'RACHEL AKSANTI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7568', NULL, 'actif', 4),
(267, NULL, 'TUMAINI', 'RASHIDI', 'TUMA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2021-08-12', NULL, 'RASHIDI PATIENT', 'MAPENDO MULONDANI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7571', NULL, 'actif', 4),
(268, NULL, 'MAKERA', 'RASHIDI', 'LUMIERE', 'F', 'BARAKA', 'CONGOLAISE', NULL, '2015-07-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7572', NULL, 'actif', 4),
(269, NULL, 'MANGAZA', 'RASHIDI', 'ORTHENCE', 'F', 'BARAKA', 'CONGOLAISE', NULL, '2013-12-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7573', NULL, 'actif', 4),
(270, NULL, 'KASHINDI', 'RASHIDI', 'ALINE', 'F', 'BARAKA', 'CONGOLAISE', NULL, '2010-08-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7575', NULL, 'actif', 4),
(271, NULL, 'AMANI', 'LAMBERT', 'ALAIN', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 92', '2018-06-24', NULL, 'INDIA LAMBERT', 'ADELA MUZUSA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7583', NULL, 'actif', 4),
(272, NULL, 'USHINDI', 'FAZILI', 'RUTH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2020-09-07', NULL, 'FAZILI MAMBO', 'ANGE BAHATI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7586', NULL, 'actif', 4),
(273, NULL, 'BULENDA', 'FAIDA', 'JEANNETTE', 'F', 'WALIKALE', 'CONGOLAISE', 'KATOYI, LOWASHI, 93', '2007-12-09', NULL, 'LUBUNDA JACQUES', 'REHEMA FURAHA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7592', NULL, 'actif', 4),
(274, NULL, 'AMBIKA', 'MURHULA', 'ALLIANCE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 21', '2020-11-09', NULL, 'MURHULA CHIZUNGU', 'NYOTA MUKESHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7596', NULL, 'actif', 4),
(275, NULL, 'WAKUBENGA', 'JUMA', 'QUETSIAH', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 73', '2011-06-07', NULL, 'JUMA KABOKO', 'CLARISSE JUMA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7600', NULL, 'actif', 4),
(276, NULL, 'NYOTA', 'BUHENDWA', 'NURIATI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 923', '2018-09-29', NULL, 'JIMMY BUHENDWA', 'IDA BILUNGA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7601', NULL, 'actif', 4),
(277, NULL, 'BUUMA', 'NDOOLE', 'ZULFATI', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, MWESO, 21', '2014-01-08', NULL, 'NDOOLE MAKELELE', 'MAMY RAJABU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7606', NULL, 'actif', 4),
(278, NULL, 'MUNYERENKANA', 'KASI', 'KEVINE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2024-04-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7609', NULL, 'actif', 4),
(279, NULL, 'NYOTA', 'MUHINDO', 'DEBORAH', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 23', '2020-11-21', NULL, 'MUHINDO MWENDAPOLE', 'FURAHA MIRUMBI', 'NORD-KIVU', 'WALIKALA', NULL, NULL, NULL, '7610', NULL, 'actif', 4),
(280, NULL, 'KAVIRA', 'KYEUSI', 'D\'ARC', 'F', 'KIBIRIZI', 'CONGOLAISE', 'KATOYI, MAENDELEO, 93', '2009-01-13', NULL, 'KAMBALE MUSUBAO', 'KAVUGHO ELENA', 'NORD-KIVU', 'LUKANGA', NULL, NULL, NULL, '7611', NULL, 'actif', 4),
(281, NULL, 'PONGA', 'KYANGA', 'JOSEPHINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 17', '2009-03-18', NULL, 'ZEPHIRIN KYANGA', 'YVONE BWIRA', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '7615', NULL, 'actif', 4),
(282, NULL, 'LEA', 'MAFARA', 'JOYEUSE', 'F', 'MASISI', 'CONGOLAISE', 'KATOYI, KISIBANGI, 93', '2012-05-18', NULL, 'MAFARA FREDDY', 'FURAHA AZIZA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7616', NULL, 'actif', 4),
(283, NULL, 'ASIFIWE', 'LUTWAMUZIRE', 'GABRIELLA', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 92', '2021-07-30', NULL, 'FREDDY LUTWAMUZIRE', 'FURAHA BANWESIZE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7617', NULL, 'actif', 4),
(284, NULL, 'BYAMUNGU', 'NTAMPAKA', 'GRACE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 29', '2012-05-02', NULL, 'NTAMPAKA', 'CHIKURU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7619', NULL, 'actif', 4),
(285, NULL, 'NABINTU', 'MALEKERA', 'CHRITEVIT', 'F', 'KINSHASA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2022-04-27', NULL, 'CHUBAKA MALEKERA', 'SEZOMURIMA NSIKU LUCIE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7623', NULL, 'actif', 4),
(286, NULL, 'KANDIMU', 'SEFU', 'ANGELANI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2010-11-15', NULL, 'SEFU IBRAHIM', 'RODA KIKA', 'MANIEMA', 'LUBUTU', NULL, NULL, NULL, '7625', NULL, 'actif', 4),
(287, NULL, 'MAUWA', 'SEFU', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', NULL, '2012-09-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7626', NULL, 'actif', 4),
(288, NULL, 'IBRAHIM', 'SEFU', 'WHISLEY', 'F', 'GOMA', 'CONGOLAISE', NULL, '2013-12-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7627', NULL, 'actif', 4),
(289, NULL, 'GODELIVE', 'MUKOBELWA', 'GIVIMIEL', 'F', 'GOMA', 'CONGOLAISE', NULL, '2018-08-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7630', NULL, 'actif', 4),
(290, NULL, 'MASIKA', 'KIZUNGU', 'REBECCA', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2018-11-19', NULL, 'CHIZUNGU', 'LIND AMUGOMOKA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7635', NULL, 'actif', 4),
(291, NULL, 'AMINA', 'JUMA', 'KAMARA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, WALIKALE, 32', '2011-10-14', NULL, 'JUMA KAMARA', 'XAVERINE SIKUJUWA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7639', NULL, 'actif', 4),
(292, NULL, 'CLEMENCE', 'BISONGA', 'EVODY', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2016-03-30', NULL, 'CLEMENT BISONGA', 'MARIE NONDO', 'MANIEMA', 'KASONGO', NULL, NULL, NULL, '7642', NULL, 'actif', 4),
(293, NULL, 'FURAHINI', 'WATELANINWA', 'CHRISTINE', 'F', 'KAVUMU', 'CONGOLAISE', 'KATOYI, BWITO, 006', '2010-02-05', NULL, 'WATELANINWA MUKUNGILWA PAPY', 'MWINJA ESTHER', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7645', NULL, 'actif', 4),
(294, NULL, 'GRACE', 'WATELANINWA', 'SANDRINE', 'F', 'KAVUMU', 'CONGOLAISE', 'KATOYI, BWITO, 006', '2012-02-07', NULL, 'WATELANINWA MUKUNGILWA', 'MWINJA ESTHER', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7646', NULL, 'actif', 4),
(295, NULL, 'ASHA', 'SAFI', 'FRANCINE', 'F', 'HOMBO', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2009-09-10', NULL, 'DESTIN SHEKANDA', 'FRANCINE BIRERE', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7653', NULL, 'actif', 4),
(296, NULL, 'FAIDA', 'MATABARO', 'ELISABETH', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 012', '2019-08-23', NULL, 'MUGWIZA MATABARO', 'MAOMBI SAIDATI', 'SUD-KIVU', 'KABARI', NULL, NULL, NULL, '7656', NULL, 'actif', 4),
(297, NULL, 'ZAWADI', 'MIRUMBI', 'ROSALIE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 92', '2021-01-19', NULL, 'PATRICK', 'SAHAR MIRUMBI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7664', NULL, 'actif', 4),
(298, NULL, 'KANYERE', 'SIVENDERANA', 'GRACIELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2022-03-22', NULL, 'KAKULE SIVENDENRANA', 'GRACIA SIVASIMA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7665', NULL, 'actif', 4),
(299, NULL, 'ANSIMA', 'FARAJA', 'DIVINE', 'F', 'KAJANGU', 'CONGOLAISE', 'NGANGI, BIHIRWA, 931', '2023-03-10', NULL, 'FARAJA MORDEKAI', 'RACHEL MWAMINI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7667', NULL, 'actif', 4),
(300, NULL, 'ARHUSIMA', 'FARAJA', 'FORTINA', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2025-05-25', NULL, 'MESHACK FARAJA', 'RACHEL CHISHUGI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7668', NULL, 'actif', 4),
(301, NULL, 'KUBUYA', 'MUNIHIRE', 'CHRISTIANNAH', 'F', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2014-11-27', NULL, 'MICHEL MUNIHIRE', 'SARAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7671', NULL, 'actif', 4),
(302, NULL, 'FURAHA', 'MUNIHIRE', 'MICHELINE', 'F', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2017-07-28', NULL, 'MICHEL MUNIHIRE', 'SARAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7672', NULL, 'actif', 4),
(303, NULL, 'FEZA', 'MUNIHIRE', 'CHRISTIVIE', 'F', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2018-12-15', NULL, 'MICHEL MUNIHIRE', 'SARAH', 'NORD-KIVU', 'MAISI', NULL, NULL, NULL, '7673', NULL, 'actif', 4),
(304, NULL, 'AZAFE', 'MUNIHIRE', 'CHRISTILA', 'F', 'MASISI', 'CONGOLAISE', 'NGANDA, NTINGI, 93', '2021-02-04', NULL, 'MICHEL MUNIHIRE', 'SARAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7674', NULL, 'actif', 4),
(305, NULL, 'NABINTU', 'BISHWEKA', 'SOPHIE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 11', '2020-03-06', NULL, 'ASUMANI BISHWEKA', 'SIFA PROSPERINE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7675', NULL, 'actif', 4),
(306, NULL, 'ASUMANI', 'BISHWEKA', 'GRACE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 11', '2021-07-13', NULL, 'ASUMANI BISHWEKA', 'SIFA PROSPERINE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7676', NULL, 'actif', 4),
(307, NULL, 'ASIFIWE', 'KALANGIRO', 'JOSLINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2013-12-18', NULL, 'KALANGIRO EMMANUEL', 'NABINTU MUDERHWA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7677', NULL, 'actif', 4),
(308, NULL, 'NEEMA', 'BUSANE', 'BENITHA', 'F', 'KABUMBINO', 'CONGOLAISE', 'NGANGI, NTINGI, 032', '2009-09-10', NULL, 'GUILLAUMME NYAMUGANDA', 'LWABOSHI NYAKURA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7678', NULL, 'actif', 4),
(309, NULL, 'KAMIKAZE', 'HABYARIMANA', 'QUEEN', 'F', 'NGOMBO', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2021-04-17', NULL, 'LUC KATUMBI', 'MARLENE FURAHA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7679', NULL, 'actif', 4),
(310, NULL, 'BILUGE', 'MUSHAGALUSA', 'ELVINE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 03', '2021-06-20', NULL, 'MUSHAGALUSA GANYWAMULUME', 'CHIBALONZA MACUMU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7682', NULL, 'actif', 4),
(311, NULL, 'UCHIKA', 'MATONDO', 'CHRISTEL', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 240', '2020-08-23', NULL, 'BERTIN MACHEKALO', 'MIKALA RUTH', 'KONGO CENTRAL', NULL, NULL, NULL, NULL, '7688', NULL, 'actif', 4),
(312, NULL, 'BWIRA', 'SALUMU', 'JORDANNAH', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 332', '2021-07-16', NULL, 'JOSPIN SALUMU', 'FURAHA KASHERA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7689', NULL, 'actif', 4),
(313, NULL, 'AWANOTA', 'NDOOLE', 'ASTRID', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2015-05-21', NULL, 'KUBUYA NDOOLE', 'KAHINDO JULIENNE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7691', NULL, 'actif', 4),
(314, NULL, 'LUMOO', 'NDOOLE', 'MERVEILLE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2017-01-05', NULL, 'KUBUYA NDOOLE', 'KAHINDO JULIENNE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7692', NULL, 'actif', 4),
(315, NULL, 'KUBUYA', 'NDOOLE', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2012-03-03', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7694', NULL, 'actif', 4),
(316, NULL, 'MUNYERENKANA', 'BUGALE', 'EUNICE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 43', '2022-09-30', NULL, 'MAOMBI BUGALE CHRISTIAN', 'UWEZO LWABOSHI', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7699', NULL, 'actif', 4),
(317, NULL, 'KANZA', 'ALUNGA', 'KEVINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, MABADILIKO, 823', '2021-04-13', NULL, 'KANZA ALUNGA FELIX', 'PENDANO KABULE AIME', 'EQUATEUR', NULL, NULL, NULL, NULL, '7703', NULL, 'actif', 4),
(318, NULL, 'BAHATI', 'BASHOMBANA', 'CHARLINE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, ZIZI, 63', '2020-10-12', NULL, 'BAHATI ISHARA', 'NOELA POLEPOLE', 'SUD-KIVU', 'KATANA', NULL, NULL, NULL, '7709', NULL, 'actif', 4),
(319, NULL, 'MASIGI', 'SANTUS', 'ADOLPHINE', 'F', 'ITULA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2013-09-10', NULL, 'SANTUS ASUMANI', 'FAIDA BWANAHERI', 'SUD-KIVU', 'UVIRA', NULL, NULL, NULL, '7710', NULL, 'actif', 4),
(320, NULL, 'FEZA', 'BUSHIRI', 'SCOLA', 'F', 'ITULA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2007-11-27', NULL, 'BUSHIRI ASUMANI', 'ADOLPHINE', 'SUD-KIVU', 'UVIRA', NULL, NULL, NULL, '7711', NULL, 'actif', 4),
(321, NULL, 'OLAME', 'BISIMWA', 'QUETSIAH', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, SALONGO, 21', '2013-08-27', NULL, 'BYAMUNGU BISIMWA', 'NYOTA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7715', NULL, 'actif', 4),
(322, NULL, 'BIKULO', 'BAKILONGO', 'MARIE', 'F', 'WALIKALE', 'CONGOLAISE', 'KATOYI, VISOKE, 19', '2010-09-09', NULL, 'ANICET MUNGUIKO', 'SIFA BAGUNDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7717', NULL, 'actif', 4),
(323, NULL, 'SAKINA', 'HANGI', 'GABRIELLA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2022-06-03', NULL, 'GERMAIN HANGI', 'ALINE BIRINGANINE', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7722', NULL, 'actif', 4),
(324, NULL, 'MWIZA', 'NZITA', 'JOSIANE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 19', '2020-07-03', NULL, 'ANICET MUNGUIKO', 'SIFA BAGUNDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7723', NULL, 'actif', 4),
(325, NULL, 'MAPENDO', 'BAHATI', 'CLEMENCE', 'F', 'RUTSHURU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-10-11', NULL, 'FABRICE BAHATI', 'LEA SHUMBUSHO', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '7730', NULL, 'actif', 4),
(326, NULL, 'ANOCIATA', 'BAHATI', 'ARJUN', 'F', 'GOMA', 'CONGOLAISE', NULL, '2013-07-02', NULL, 'FABRICE BAHATI', 'RACHEL SHUMBUSHO', NULL, NULL, NULL, NULL, NULL, '7731', NULL, 'actif', 4),
(327, NULL, 'NABINTU', 'BWEMA', 'SHIMENE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2026-05-01', NULL, 'BWEMA RWESI', 'SHUKURU KANYABASHI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7736', NULL, 'actif', 4),
(328, NULL, 'FAZILA', 'NGABO', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 11', '2014-09-26', NULL, 'ASUMANI BISHWEKA', 'SIFA PROSPERINE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7744', NULL, 'actif', 4),
(329, NULL, 'FARAJA', 'BAHIGA', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KILIMANJARO1, 12', '2012-12-12', NULL, 'KATABAZI BAHIGA', 'MASIKA BAKE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7747', NULL, 'actif', 4),
(330, NULL, 'BISUDJA', 'KAMANDA', 'ESPERANCE', 'F', 'WALIKALE', 'CONGOLAISE', 'NGANGI, TNINGI, 21', '2010-10-08', NULL, 'AMISI KAMANDA', 'ZAWADI MUHABWA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7754', NULL, 'actif', 4),
(331, NULL, 'BARAKA', 'GAHETO', 'JEMIMA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 02', '2008-05-22', NULL, 'MOMBERO GAHETO', 'FURAHA MPONGO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7771', NULL, 'actif', 4),
(332, NULL, 'MUGOLI', 'KAYEMBE', 'JUSTINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 21', '2017-02-20', NULL, 'KAZADI KAYEMBE', 'FAIDA BUHENDWA', 'SUD-KIVU', 'KAVUMU', NULL, NULL, NULL, '7776', NULL, 'actif', 4),
(333, NULL, 'IPUNGILI', 'WASSO', 'DANIELLA', 'F', 'GOMA', 'CONGOLAISE', 'TURUNGA, KAKOTI', '2012-01-13', NULL, 'WASSO MUSOMBWA', 'KAHAMBU KILOLIRWE', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7778', NULL, 'actif', 4),
(334, NULL, 'UWEZO', 'BUNFIKANE', 'VICTORIEUSE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTIGI, 21', '2017-11-01', NULL, 'BUNFIKANE BIZIMNA', 'LANDRINE MULASHI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7780', NULL, 'actif', 4),
(335, NULL, 'MUTULA', 'NDASHENGA', 'BENE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NYANDONDO, 92', '2010-02-28', NULL, 'MUTULA HENRY', 'LUCIE LUCIE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7781', NULL, 'actif', 4),
(336, NULL, 'BINYANGE', 'FAMBA', 'JENNY', 'F', 'GOMA', 'CONGOLAISE', NULL, '2012-02-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7784', NULL, 'actif', 4),
(337, NULL, 'BELWAPAME', 'TEPENGETI', 'ROLLANDE', 'F', 'BIKAVU', 'CONGOLAISE', 'KATOYI, NDOWE, 132', '2012-12-22', NULL, 'MAURICE BONDO', 'NOELLA KIYAYU', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7786', NULL, 'actif', 4),
(338, NULL, 'NABINTU', 'RWAMAGIRA', 'ANOUARITE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KILIMANJARO, 21', '2017-06-22', NULL, 'AMANI RWAMAGIRE', 'FURAHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7789', NULL, 'actif', 4),
(339, NULL, 'SIKILIZA', 'MUHINDO', 'DORCAS', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 32', '2018-09-29', NULL, 'MUHINDO MWENDAPOLE', 'FURAHA MIRUMBI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7794', NULL, 'actif', 4),
(340, NULL, 'SAFI', 'MUTWEDU', 'HORTENCE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2020-12-10', NULL, 'AKSANTI MUTWEDU', 'FAZILI MULUMEODERWWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7795', NULL, 'actif', 4),
(341, NULL, 'KANYERE', 'KABUYAYA', 'LATIFA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 12', '2020-09-10', NULL, 'ARIEL KABUYAYA', 'MWAMINI MURHULA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7798', NULL, 'actif', 4),
(342, NULL, 'MAPENDO', 'BAGULA', 'LANDRINE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 82', '2010-10-11', NULL, 'BAGULA MAKANGARA', 'NSIMIRE KADUSI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7799', NULL, 'actif', 4),
(343, NULL, 'LUKUSA', 'KASENDA', 'MARIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 921', '2016-06-08', NULL, 'TUMBA KASENDA', 'USHINDI BAHATI', 'KASAI', 'LODJA', NULL, NULL, NULL, '7806', NULL, 'actif', 4),
(344, NULL, 'BAHATI', 'KANENO', 'ROSALIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2013-02-24', NULL, 'KANENO BAHATI', 'ELODIE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7808', NULL, 'actif', 4),
(345, NULL, 'LUMOO', 'KANENO', 'ROSINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2008-10-10', NULL, 'KANENO', 'ELODIE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7809', NULL, 'actif', 4),
(346, NULL, 'UTULIVU', 'NZANGI', 'JENOVIC', 'F', 'SAKE', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2019-05-12', NULL, 'NZENGI BOYA', 'FURAHA JANVIER', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7813', NULL, 'actif', 4),
(347, NULL, 'SARAFINA', 'AKILIMALI', 'BRIGITTE', 'F', 'KAMITUGA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-09-21', NULL, 'AKILIMALI KYANZA', 'MWENGE LAFE', 'SUD-KIVU', 'KAMITUGA', NULL, NULL, NULL, '7814', NULL, 'actif', 4),
(348, NULL, 'ESTA', 'BAIMIRENE', 'MAGNIFIQUE', 'F', 'MACHUMBI', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2020-06-20', NULL, 'BARAKA', NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7815', NULL, 'actif', 4),
(349, NULL, 'AKONKWA', 'CHOBOHWA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 73', '2021-09-02', NULL, 'CHOBOHWA KATU', 'MAPENDO JACKELINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7818', NULL, 'actif', 4),
(350, NULL, 'BAHATI', 'BAMPORIKI', 'BERNADETTE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 12', '2018-01-06', NULL, 'BAHATI BAMPORIKI', 'ANNE-MARIE HANGI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7820', NULL, 'actif', 4),
(351, NULL, 'UWASE', 'SEBUTOZI', 'PRISCA', 'F', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 123', '2014-04-12', NULL, 'SEBUTOZI', 'NOELA BOYA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7821', NULL, 'actif', 4),
(352, NULL, 'BINJA', 'BUHENDWA', 'HAPPY', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI', '2016-02-12', NULL, 'JEAN-PIERRE BUHENDWA', 'NABINTU RUBIBI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7822', NULL, 'actif', 4),
(353, NULL, 'ASINGIBWE', 'BUSANYA', 'JOSEPHINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2017-07-06', NULL, 'BUSANYA KABALI', 'SIUZIKE NABURAMBI', 'NORD-KIVU', NULL, NULL, NULL, NULL, '7823', NULL, 'actif', 4),
(354, NULL, 'MASIKA', 'KAOMA', 'REBECCA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2022-03-23', NULL, 'KASEREKA KAOMA', 'KATUNGU VANGISIVAVI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7827', NULL, 'actif', 4),
(355, NULL, 'ASIFIWE', 'MUNYAGALA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2018-04-18', NULL, 'INNOCENT MUNYAGALA', 'CHARLINE NZIGIRE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7828', NULL, 'actif', 4),
(356, NULL, 'KARUBEMBE', 'ZIBANDA', 'KERENE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2014-06-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7833', NULL, 'actif', 4),
(357, NULL, 'ANDEMA', 'MURHULA', 'PATRICIA', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2021-04-24', NULL, 'MURHULA MARC', 'ALPHNSINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7835', NULL, 'actif', 4),
(358, NULL, 'KABIKA', 'MURHABAZI', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', NULL, '2020-11-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7836', NULL, 'actif', 4),
(359, NULL, 'SUZANA', 'LULINDA', 'FRANCINE', 'F', 'KITUMBA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2010-06-16', NULL, 'LULINGA', 'NYOTA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7838', NULL, 'actif', 4),
(360, NULL, 'ASHUZA', 'AKSANTE', 'LEONNIE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 132', '2021-06-25', NULL, 'AKSANTI', 'KATAMBWE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7839', NULL, 'actif', 4),
(361, NULL, 'ASIFIWE', 'BASHIGALE', 'IRENE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, KILIMANJARO1, 231', '2008-04-20', NULL, 'KITUMAINI BASHIGALE', 'FURAHA BURUME', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7841', NULL, 'actif', 4),
(362, NULL, 'MUNIHIRE', 'MAWASO', NULL, 'F', 'SHASHA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2012-07-06', NULL, 'LUTSIRA MAWASO', 'CELESTINE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7850', NULL, 'actif', 4),
(363, NULL, 'KAVIRA', 'MALEKEWA', 'NAOMIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 123', '2010-05-06', NULL, 'OBEDI', 'NYOTA PASCALINE', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7859', NULL, 'actif', 4),
(364, NULL, 'BUSHASHIRE', 'NDOOLE', 'ELIZA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 91', '2018-08-07', NULL, 'MIKA NDUO', 'KAVIRA JULIE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7861', NULL, 'actif', 4),
(365, NULL, 'BATANGA', 'ESONGELO', 'KEVINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, 13', '2007-06-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7862', NULL, 'actif', 4),
(366, NULL, 'LINA', 'ESONGELO', 'KEVIN', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI', '2011-11-28', NULL, 'RUBIN ESONGELO', 'ALPHONSINE', NULL, NULL, NULL, NULL, NULL, '7863', NULL, 'actif', 4),
(367, NULL, 'ANGISHA', 'ZIHALIRWA', 'CHRISTINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 71', '2021-06-17', NULL, 'ZIHALIRWA', 'MALIYETU', 'SUD-KIVU', 'KAVUMU', NULL, NULL, NULL, '7864', NULL, 'actif', 4),
(368, NULL, 'NGALYA', 'MWOGELWA', 'INGRID', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2020-08-23', NULL, 'ALKBER MWOGZELWA', 'RUTH ABAHTI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7869', NULL, 'actif', 4),
(369, NULL, 'MUNYERENKANA', 'BISIMWA', 'JEMIMA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, SALONGO, 831', '2019-11-27', NULL, 'BAYAMUNGU BISIMWA', 'NYOTA MARIE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7870', NULL, 'actif', 4),
(370, NULL, 'WANI', 'BISIMWA', 'QUERENE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2018-10-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7871', NULL, 'actif', 4),
(371, NULL, 'AKILIMALI', 'KAHIGA', 'VICTOIRE', 'F', 'IDJWI', 'CONGOLAISE', 'NGANGI, NTINGI, 921', '2020-09-24', NULL, 'LAVIE KAHIGA', 'FRACINE NANKAFU', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7873', NULL, 'actif', 4),
(372, NULL, 'IRUNGU', 'BAHATI', 'GRACIANNAH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2023-08-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7875', NULL, 'actif', 4),
(373, NULL, 'BAHATI', 'BASHOMBANA', 'CHARLINE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2019-08-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7879', NULL, 'actif', 4),
(374, NULL, 'KALIZA', 'BAGALWA', 'ANTOINETTE', 'F', 'IDJWI', 'CONGOLAISE', 'NGANGI, NTINGI, 23', '2013-04-25', NULL, 'MANIRAGUHA BAGALWA', 'NYABUHORO', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7880', NULL, 'actif', 4),
(375, NULL, 'NSHOBOLE', 'BAGALWA', 'VERONIQUE', 'F', 'IDJWI', 'CONGOLAISE', NULL, '2011-10-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7881', NULL, 'actif', 4),
(376, NULL, 'ASHUZA', 'MUNYIARAKENGWA', 'DORCAS', 'F', 'CIRAMBI', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2013-05-05', NULL, 'MUNYIARAKENGWA', 'PATIENCE', 'SUD-KIVU', 'KAMEHE', NULL, NULL, NULL, '7888', NULL, 'actif', 4),
(377, NULL, 'ANSIMA', 'MURHULA', 'DAVINA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2021-01-29', NULL, 'KAMANZI MURHULA', 'MUHINDO KANYENA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7889', NULL, 'actif', 4),
(378, NULL, 'SIFA', 'HABAMUNGU', 'LATIFA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 29', '2013-12-08', NULL, 'HABAMUNGU FISTON', 'ABIBU NATHALIE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7890', NULL, 'actif', 4),
(379, NULL, 'RIZIKI', 'TAMARI', 'SAKINA', 'F', 'PINGA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2012-10-23', NULL, 'SADIKI CHIHASA', 'CHANCE AMINA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7892', NULL, 'actif', 4),
(380, NULL, 'BABUYE', 'NDOOLE', 'LADY', 'F', 'GOMA', 'CONGOLAISE', NULL, '2018-06-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7899', NULL, 'actif', 4),
(381, NULL, 'MERVERILLE', 'WENGA', 'ESTHER', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 92', '2019-01-11', NULL, 'MASTAKI LEON', 'NADIA CHIRIMWAMI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7901', NULL, 'actif', 4),
(382, NULL, 'CHIKU', 'MULUMEODERHWA', 'JOSLINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2011-04-06', NULL, 'JUSTIN MULUMEODERHWA', 'PASCALINE DOGO', 'NORD-KIVU', NULL, NULL, NULL, NULL, '7902', NULL, 'actif', 4),
(383, NULL, 'CHITO', 'MULUMEODERHWA', 'JOYCE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2011-04-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7903', NULL, 'actif', 4),
(384, NULL, 'BIBENTYO', 'CHIRIMWAMI', 'DORCAS', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, SALAMA, 12', '2013-09-01', NULL, 'CHIRIMWAMI JEAN-PIERRE', 'KITUMAINI JUSTINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7905', NULL, 'actif', 4),
(385, NULL, 'FAZILI', 'BARUME', 'PREFERENCE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 22', '2020-11-27', NULL, 'ISAAC BARUME', 'DESANGES JUVENALE', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '7908', NULL, 'actif', 4),
(386, NULL, 'NSTII', 'BAHATI', 'BENEDICTE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2015-10-10', NULL, 'BAHATI SANVURA', 'MAOMBIMUHINDO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7909', NULL, 'actif', 4),
(387, NULL, 'ISHIMA', 'BANYANGA', 'PROVIDENCE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 291', '2021-02-22', NULL, 'PROSPERE BANYANGA', 'SIFA MASIRI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7922', NULL, 'actif', 4),
(388, NULL, 'BARAKA', 'KANANE', 'JEMIMA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 92', '2015-08-24', NULL, 'SAFARI KANANE', 'SANGOLA MANENO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7930', NULL, 'actif', 4),
(389, NULL, 'CHIZA', 'KIBANJA', 'PASCALINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2016-03-30', NULL, 'EMMANUEL KIBANJA', 'FRANCINE MAENE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7947', NULL, 'actif', 4),
(390, NULL, 'NAFISA', 'USENI', 'USHINDI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2012-06-30', NULL, 'USENI DEGAMMA', 'ZIGASHANE BASHIZI', 'MANIEMA', 'KINDU', NULL, NULL, NULL, '7950', NULL, 'actif', 4),
(391, NULL, 'TENKYA', 'BUSIKILA', 'JEANNE-D\'ARC', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NINGI, 21', '2018-07-25', NULL, 'CHRISTIAN BUSIKILA', 'DORCAS BADERHA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7959', NULL, 'actif', 4),
(392, NULL, 'FURAHA', 'CHIKURU', 'JOYCE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2013-09-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7973', NULL, 'actif', 4),
(393, NULL, 'SIFA', 'IRENGE', 'PLAMEDI', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NGINGI, 31', '2010-08-24', NULL, 'BIENVENU TEGANYI', 'ANOUARITE CHIRIMWMAI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7974', NULL, 'actif', 4),
(394, NULL, 'AKONKWA', 'CHIMANA', 'ABIGAEL', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 31', '2013-12-20', NULL, 'SHAMAVU CHIMANA', 'FURAHA MATERANYA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7976', NULL, 'actif', 4),
(395, NULL, 'SINAOFU', 'BUSHASHIRE', 'ARLENNE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2016-02-05', NULL, 'KAKURU', 'SIFA MIRIMO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7980', NULL, 'actif', 4),
(396, NULL, 'RUTU', 'MAHULU', 'FLORENTINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2018-11-21', NULL, 'KWIZERA BIFUMBO', 'MAOMBI MUDEGE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7982', NULL, 'actif', 4),
(397, NULL, 'MWANGAZA', 'WASO', 'LUMIERE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2010-01-13', NULL, 'THIERRY WASO', 'LOUISE KAHAMBU', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7984', NULL, 'actif', 4),
(398, NULL, 'FATUMA', 'CHEKE', 'BENITE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI', '2014-03-19', NULL, 'CHEKE NORBERT', 'KITUMAINI REGINA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7987', NULL, 'actif', 4),
(399, NULL, 'PUNGWE', 'CHEKANABO', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI', '2016-11-14', NULL, 'PUNGWE SALA', 'NYAZUNGU SIFA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7997', NULL, 'actif', 4),
(400, NULL, 'PUNGWE', 'MABWE', 'GERMANY', 'F', 'GOMA', 'CONGOLAISE', NULL, '2018-10-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7998', NULL, 'actif', 4),
(401, NULL, 'PUNGWE', 'FATUMA', 'PROVIDENCE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2020-03-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7999', NULL, 'actif', 4),
(402, NULL, 'PUNGWE', 'ANNE-MARIE', 'SHEKINAH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2022-04-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8000', NULL, 'actif', 4),
(403, NULL, 'PUNGWE', 'ELYANNE', 'PERVEILLE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2024-12-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8001', NULL, 'actif', 4),
(404, NULL, 'ANSIMA', 'BAHANE', 'GRACE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, TECHNICIENS, 93', '2010-12-07', NULL, 'BAHANE MUNYALI', 'GERMAINE LUHUSA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8002', NULL, 'actif', 4),
(405, NULL, 'FURAHA', 'KITAMBALA', 'JOYCE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOHWA, 237', '2011-06-06', NULL, 'KITAMBALAVALENTIN', 'MASIRIKA LOUISE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8016', NULL, 'actif', 4),
(406, NULL, 'SENDAMA', 'TUMAINI', 'HOPE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2021-03-16', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8021', NULL, 'actif', 4),
(407, NULL, 'DURIDA', 'MULONGE', 'ROSALIE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 21', '2021-08-04', NULL, 'RODRIGUR MULONGE', 'NABINTU RACHEL', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8037', NULL, 'actif', 4),
(408, NULL, 'KAKOMBA', 'EDEN', 'LEKA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2023-05-18', NULL, 'VEDASTE LEKA', 'IBRAHIM ANGELIQUE', 'MANIEMA', 'KINDU', NULL, NULL, NULL, '8038', NULL, 'actif', 4),
(409, NULL, 'FURAHA', 'KIMBUNDA', 'EMMANUELLE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2015-09-27', NULL, 'ABEDI KIMBUNDA', 'AKILIMALI RUPHINE', 'NORD-KIVU', 'WALIKALAE', NULL, NULL, NULL, '8039', NULL, 'actif', 4),
(410, NULL, 'ORISA', 'BILI', 'MUHONGYA', 'F', 'GOMA', 'CONGOLAISE', NULL, '2020-05-22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8041', NULL, 'actif', 4),
(411, NULL, 'ESTHER', 'KARHEKEZI', 'PLAMEDI', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 032', '2009-10-30', NULL, 'KARHEKEZI AUGUSTIN', 'CHIKWANINE KALAMABA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8042', NULL, 'actif', 4),
(412, NULL, 'NABINTU', 'MULUMEODERHWA', 'DESANGES', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2009-10-09', NULL, 'MULUMEOERWHA', 'KABAYA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8043', NULL, 'actif', 4),
(413, NULL, 'LUKOMBO', 'SITA', 'PRISCA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 92', '2020-08-09', NULL, 'SITA ROGER', 'MBOKO SUZANE', 'KONGO CENTRAL', 'SEKEBANZA', NULL, NULL, NULL, '8048', NULL, 'actif', 4),
(414, NULL, 'MERVEILLE', 'WENGA', 'ESTHER', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 31', '2019-01-11', NULL, 'PENE MASTAKI', 'NADIA CHIRMWAMI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8049', NULL, 'actif', 4),
(415, NULL, 'FATUMA', 'KIUSILA', 'GEORGINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 21', '2007-03-14', NULL, 'KIUSILA DIEUDONNE', 'NYANCHIRA KALENGE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8050', NULL, 'actif', 4),
(416, NULL, 'BINDU', 'BWIRA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'HIMBI, DE GOMA, 21', '2017-10-13', NULL, 'BWIRA MUHIMA', 'RIZIKI BANDU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8051', NULL, 'actif', 4),
(417, NULL, 'ELISABETH', 'BUHASHIRE', 'OPPORTUNE', 'F', 'MACHUMBI', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2017-01-17', NULL, 'BUSHASHIRE LUKOO', 'SHAKI FAIDA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8054', NULL, 'actif', 4),
(418, NULL, 'FURAHA', 'BUSHASHIRE', 'EXAUCE', 'F', 'MACHMBI', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2020-11-17', NULL, 'BUSHASHIRE LUKOO', 'SHAKI FAIDA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8055', NULL, 'actif', 4),
(419, NULL, 'NINGINI', 'ASEANE', 'CHRISTEL', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2010-12-30', NULL, 'BADI ASEANE', 'MARTINE GATO', 'HAUT KATANGA', 'WALIKALE', NULL, NULL, NULL, '8059', NULL, 'actif', 4),
(420, NULL, 'ATOSHA', 'BISIMWA', 'CLEMENCE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2010-05-05', NULL, 'BISIMWA HERITIER', 'NSIMIRE MUDUNGE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8061', NULL, 'actif', 4),
(421, NULL, 'SALE', 'SULEMANI', 'QUEEN', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-04-28', NULL, 'BINYANGE SULEMANI', 'LINDA ZAWADI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8063', NULL, 'actif', 4),
(422, NULL, 'BADERHA', 'BUROKO', 'GLORIA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2015-05-07', NULL, 'BUROKO BADERHA', 'RIZIKI TURENGE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8069', NULL, 'actif', 4),
(423, NULL, 'AJABU', 'MUSGISHO', 'GRACIELLA', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-06-16', NULL, 'MUGISHO BASHEKA', 'FRANCINE MATABARO', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8070', NULL, 'actif', 4),
(424, NULL, 'AMENIPA', 'MUNYALI', 'AMINI', 'F', 'BUNYAKIRI', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-10-10', NULL, 'MUNYALIJEAN', 'ESPERANCE BAHATI', 'SUD-KIVU', 'BUNYAKIRI', NULL, NULL, NULL, '8072', NULL, 'actif', 4),
(425, NULL, 'KATUNGU', 'KABUYAYA', 'MICHELINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 81', '2010-03-10', NULL, 'KAMBALE KABUYAYA', 'ESTHER MBAKU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8084', NULL, 'actif', 4),
(426, NULL, 'MARIAMU', 'MASUDI', 'JEANNE', 'F', 'KINDU', 'CONGOLAISE', 'KATOYI, ZIZI, 011', '2008-10-17', NULL, 'MASUDI KASONGO', 'SAFI MBAKU', 'MANIEMA', 'KINDU', NULL, NULL, NULL, '8088', NULL, 'actif', 4),
(427, NULL, 'ISHIMWE', 'GABIKIRE', 'JOSEPH', 'F', 'RUTSHURU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS', '2015-10-15', NULL, 'JOSEPH GABIKIRE', 'SALIMA MABRUKI', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '8094', NULL, 'actif', 4),
(428, NULL, 'ASIFIWE', 'MBONIMPA', 'ELYSEE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2012-12-03', NULL, 'ZUNGU MBONIMPA', 'MARIE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8097', NULL, 'actif', 4),
(429, NULL, 'NTII', 'MUKUBA', 'ARCHANGE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 291', '2022-06-22', NULL, 'MUUBIRI MUKUBA', 'NSIMIRE BACHIREBA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8098', NULL, 'actif', 4),
(430, NULL, 'MWAMINI', 'AMISI', 'GERMAINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANI, NTINGI, 21', '2010-09-07', NULL, 'MUSA MAONEO', 'ESPERANCE KABUHO', 'NORD-KIVU', NULL, NULL, NULL, NULL, '8099', NULL, 'actif', 4),
(431, NULL, 'OMENGOMO', 'MUCHUYUKO', 'PATRICIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KIBWE, 91', '2010-11-19', NULL, 'MUCHUYUKO Autriche', 'MUHOZI MONIQUE', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8102', NULL, 'actif', 4),
(432, NULL, 'NEEMA', 'BAZIGA', 'ESTHER', 'F', 'MINOVA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2013-02-13', NULL, 'KITO BAZIGA', 'ELYSEE BARABAZI', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '8110', NULL, 'actif', 4),
(433, NULL, 'UWEZO', 'POTO', 'EMILIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2007-04-15', NULL, 'MITUPE POTO', 'CHARLOTTE MASHAURI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8118', NULL, 'actif', 4),
(434, NULL, 'KALIMUKA', 'REBECCA', 'JASMINE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-10-10', NULL, 'MUSUMBE KALIMUKA', 'KAHINDO ANNA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8119', NULL, 'actif', 4),
(435, NULL, 'SHEKINAH', 'RIZIKI', 'KUBUYA', 'F', 'GOMA', 'CONGOLAISE', NULL, '2022-03-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8124', NULL, 'actif', 4),
(436, NULL, 'AMANI', 'BATASEMA', 'CHANCELINE', 'F', 'KATANA', 'CONGOLAISE', 'NGANGI, NTINGI, 54', '2013-01-31', NULL, 'OMBENI BATASEMA', 'NZIGIRE ALICE', 'SUD-KIVU', 'KATANA', NULL, NULL, NULL, '8138', NULL, 'actif', 4),
(437, NULL, 'AMANI', 'OMBENI', 'BENEDICTE', 'F', 'KATANA', 'CONGOLAISE', 'NGANGI, NTINGI, 54', '2015-08-07', NULL, 'OMBENI BATASEMA', 'NZIGIRE ALICE', 'SUD-KIVU', 'KATANA', NULL, NULL, NULL, '8139', NULL, 'actif', 4),
(438, NULL, 'NSHOKANO', 'MUGARUKA', 'JETOU', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2013-01-13', NULL, 'FREDY', 'SIFA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8144', NULL, 'actif', 4),
(439, NULL, 'KAHAMBU', 'KISYANGA', 'SHEKINAH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2020-12-12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8150', NULL, 'actif', 4),
(440, NULL, 'MARCELINE', 'SHABANI', 'DOMINGO', 'F', 'GOMA', 'CONGOLAISE', NULL, '2015-04-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8152', NULL, 'actif', 4),
(441, NULL, 'BEKWAPEKO', 'WAKEMA', 'ASINA', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, KAKOTI, 39', '2014-10-23', NULL, NULL, 'FEZA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8158', NULL, 'actif', 4),
(442, NULL, 'ISHARA', 'MURHULA', 'CHRISTELLE', 'F', 'BUKAVU', 'CONGOLAISE', NULL, '2011-09-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8159', NULL, 'actif', 4),
(443, NULL, 'ZAWADI', 'KANYERE', 'JULIENNE', 'F', 'PINGA', 'CONGOLAISE', 'NGANGI, UHURU, 21', '2019-02-08', NULL, NULL, NULL, 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8164', NULL, 'actif', 4),
(444, NULL, 'NABINTU', 'RUGAMBWA', 'MARIE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 219', '2021-02-28', NULL, 'MOISE RUGAMBWA', 'ALICE CHIZA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8166', NULL, 'actif', 4),
(445, NULL, 'ZANI', 'MAROYI', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 29', '2014-07-07', NULL, 'DAVID MAROYI', 'FAIDA CHEBUJONGO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8171', NULL, 'actif', 4),
(446, NULL, 'ATUPENDA', 'HERI', 'IDILLE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2014-10-17', NULL, 'HERI CHIZUNGU', 'NZIGIRE ORNELLA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8176', NULL, 'actif', 4),
(447, NULL, 'MWANGAZA', 'BAHATI', 'LUMIERE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 302', '2021-10-15', NULL, 'BAHATI MUHOZI', 'ASIFIWE ASITU', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8200', NULL, 'actif', 4),
(448, NULL, 'BYANDIKE', 'MUBALAMA', 'ROSALIE', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2010-07-01', NULL, 'MUBALAMA GERALD', 'NABINTU JULIENNE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8203', NULL, 'actif', 4),
(449, NULL, 'ANIFA', 'MANYEBWA', 'PASCALINE', 'F', 'GOMA', 'CONGOLAISE', 'HIMBI, DE GOMA, 212', '2011-04-20', NULL, 'AIJO MUNGAZI', 'JOSEPHINE BAGIRA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8216', NULL, 'actif', 4),
(450, NULL, 'ETUNDA', 'ENOCK', 'DAVINA', 'F', 'GOMA', 'CONGOLAISE', NULL, '2017-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8218', NULL, 'actif', 4),
(451, NULL, 'FATUMA', 'SHAMAMBA', 'LAETITIA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2019-07-16', NULL, 'SHAMAMBA JACQUES', 'MWAMINI MWARABU', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8223', NULL, 'actif', 4),
(452, NULL, 'ANSIMA', 'BASHIMBE', 'ESTHER', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 331', '2007-08-28', NULL, 'BASHIMBE SHAMAVU', 'FATUMA FINGIRIRO', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8224', NULL, 'actif', 4),
(453, NULL, 'HULDA', 'LUMBABO', 'KEVINE', 'F', 'GOMA', 'CONGOLAISE', 'HIMBI, SALAMABILA, 21', '2010-09-27', NULL, 'ALEXIS LUMBABO', 'PIERRETTE FAIDA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8225', NULL, 'actif', 4),
(454, NULL, 'NANKAFU', 'MWITABA', 'CHANCELINE', 'F', 'IDJWI', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2006-01-01', NULL, 'MWITABA', 'CHARLOTTE', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '8232', NULL, 'actif', 4),
(455, NULL, 'REHEMA', 'MUGANZA', 'CECILE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 32', '2021-05-22', NULL, 'TUUNGANE MUGANZA', 'TUOMBE PRISCA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8238', NULL, 'actif', 4),
(456, NULL, 'MAYAZA', 'KAMUNGU', 'FURAHA', 'F', 'MATEBO', 'CONGOLAISE', 'NGANGI, NTINGI, 311', '2007-01-02', NULL, 'KAMUNGU', 'CHEUSI MANYAMACHO', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '8241', NULL, 'actif', 4),
(457, NULL, 'KAHINDO', 'NZAMBA', 'JOSEPHINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, VITSHUMBI, 003', '2008-11-28', NULL, 'JOSUE NZAMBA', 'CECILE BAKULU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8242', NULL, 'actif', 4),
(458, NULL, 'BUSANGA', 'BONANE', 'SOUZANE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 31', '2012-12-14', NULL, 'BASUBI BONANE', 'ORTHENCE KASOPO', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8243', NULL, 'actif', 4),
(459, NULL, 'RIZIKI', 'BAHATI', 'AGNES', 'F', 'SHABUNDA', 'CONGOLAISE', 'KATOTI, MUHAVURA, 21', '2004-11-08', NULL, 'BAHATI BAUDOIN', 'JEANNETTE MUKENGE', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '8245', NULL, 'actif', 4),
(460, NULL, 'NSIMIRE', 'GANYWAMULUME', 'IMMACULEE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 21', '2014-07-02', NULL, 'NDUME GANYWAMULUME', 'CLARISSE BASILWANGO', 'SUD-KIVU', NULL, NULL, NULL, NULL, '8265', NULL, 'actif', 4),
(461, NULL, 'MWEMA', 'MINANI', 'GISELLE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 092', '2019-03-05', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8272', NULL, 'actif', 4),
(462, NULL, 'MALENGA', 'SWEDI', 'ADELE', 'F', 'BUKAVU', 'CONGOLAISE', 'KATOYI, BUKONDE, 493', '2009-07-19', NULL, NULL, NULL, 'SUD-KIVU', 'FIZI', NULL, NULL, NULL, '8273', NULL, 'actif', 4),
(463, NULL, 'OMBENI', 'KABENE', 'ORNELLA', 'F', 'WALIKALE', 'CONGOLAISE', 'NDOSHO, KILIMANJARO, 021', '2009-12-12', NULL, 'OMBENI KABENE', 'ALUTA MUSAFIRI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8281', NULL, 'actif', 4),
(464, NULL, 'MWISHA', 'SHEMATSI', 'DIVINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 02', '2012-11-03', NULL, 'FRANCK SHEMATSI', 'FURAHA BYADUNIA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8284', NULL, 'actif', 4),
(465, NULL, 'KUBUYA', 'SHEMATI', 'DANIELLA', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 021', '2018-07-07', NULL, 'FRANK SHEMATSI', 'FURAHA BYADUNIA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8285', NULL, 'actif', 4),
(466, NULL, 'NSIMIRE', 'BALOLA', 'ESTHER', 'F', 'KABARE', 'CONGOLAISE', 'KATOYI, BUKONDE, 021', '2009-02-08', NULL, NULL, NULL, 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8286', NULL, 'actif', 4),
(467, NULL, 'MUYANGO', 'NKENSE', 'MERITE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 9311', '2009-02-06', NULL, 'MUYANGO', 'BIHANGO', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8287', NULL, 'actif', 4),
(468, NULL, 'BUSANGA', 'LONGANGE', 'FLORENCE', 'F', 'GOMA', 'CONGOLAISE', NULL, '2020-03-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8295', NULL, 'actif', 4),
(469, NULL, 'TUMUSIFU', 'FIKIRI', 'JOYCE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKONDE, 93', '2011-07-12', NULL, 'JOHN FIKIRI', 'REGINE MAVAMBU', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8298', NULL, 'actif', 4),
(470, NULL, 'MUSANABERA', 'SIBOMANA', 'DORCAS', 'F', 'MASISI', 'CONGOLAISE', NULL, '2010-09-07', NULL, 'SIBOMANA JACQUES', 'IRABIMANA IMMACULEE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8311', NULL, 'actif', 4),
(471, NULL, 'ANIFA', 'LUPANGI', 'GEORGETTE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2020-10-20', NULL, 'GEORGES LUPANGI', 'VANESSA MWAMULEZI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8312', NULL, 'actif', 4),
(472, NULL, 'ADIDJA', 'LUPANGI', 'MARIANNAH', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2023-02-23', NULL, 'GEORGES LUPANGI', 'VANESSA MWAMULEZI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8313', NULL, 'actif', 4),
(473, NULL, 'STEPHANIE', 'MWAMULEZI', 'JEMIMA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2021-08-22', NULL, 'DIEUME MWAMULEZI', 'NANCY ANGIRA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8314', NULL, 'actif', 4),
(474, NULL, 'OLINDE', 'AMANI', 'DAVINAH', 'F', 'GOMA', 'CONGOLAISE', NULL, '2013-09-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8316', NULL, 'actif', 4),
(475, NULL, 'NTAWAMO', 'KAMALEBO', 'MARY', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 291', '2009-03-09', NULL, 'NTAWAMO KAMALEBO', 'SARHA NGALULA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8318', NULL, 'actif', 4),
(476, NULL, 'TUMUSIFU', 'FIKIRI', 'JOYCE', 'F', '12/07/2011', 'CONGOLAISE', 'KATOYI, BUKONDE, 921', '2011-07-12', NULL, 'JOHN FIKIRI', 'REGINA MAVANGA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8331', NULL, 'actif', 4),
(477, NULL, 'BINALINTYO', 'BADOSE', 'QUETSIAH', 'F', 'RUTSHURU', 'CONGOLAISE', NULL, '2020-10-20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8333', NULL, 'actif', 4),
(478, NULL, 'KABUO', 'MUHEKWA', 'BERNICE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 30', '2021-08-05', NULL, 'KASEREKA MUHEKWA', 'NZIGIRE SHOMBO', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '8334', NULL, 'actif', 4),
(479, NULL, 'VANAZOBE', 'MUHEKWA', 'BRIGITTE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 30', '2019-08-05', NULL, 'KASEREKA MUHEKWA', 'NZIIRE SHOMBO', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '8335', NULL, 'actif', 4),
(480, NULL, 'ATOSHA', 'MACHARA', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'NDOSHO, MATOVU, 012', '2011-09-06', NULL, 'MINANI MACHARA', 'ATOSHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8336', NULL, 'actif', 4),
(481, NULL, 'NYENE', 'MUBWEBWE', 'PASCALINE', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 012', '2014-04-19', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8354', NULL, 'actif', 4),
(482, NULL, 'ANDEMA', 'BIRIMWIRAGI', 'JOVIALE', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 021', '2021-02-03', NULL, 'BIRIMWIRAGI JOSUE', 'HESHIMA LWABOSHI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8357', NULL, 'actif', 4),
(483, NULL, 'BINJA', 'CHIZUNGU', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, DES GEOMETRES, 92', '2014-11-25', NULL, 'GERARD MACHUMU', 'ELISABETH MWAMINI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8358', NULL, 'actif', 4);
INSERT INTO `eleves` (`id`, `matricule`, `nom`, `postnom`, `prenom`, `genre`, `lieu_naissance`, `nationalite`, `adresse`, `date_naissance`, `parent_id`, `nom_pere`, `nom_mere`, `province_origine`, `territoire`, `secteur`, `groupement`, `village`, `num_permanent`, `photo`, `statut_eleve`, `ecole_id`) VALUES
(484, NULL, 'AMENIPA', 'BAVURHE', 'RAEL', 'F', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 921', '2008-06-05', NULL, 'OMBENI BAVURHE', 'SYLVINE RUZIGAMIZA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8362', NULL, 'actif', 4),
(485, NULL, 'DORIKA', 'BANDU', 'SOFIA', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2015-09-07', NULL, 'GUILLAUME KASKOTI', 'NDOOLE BINDU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8364', NULL, 'actif', 4),
(486, NULL, 'BANDU', 'NKUBA', 'ABIGAIL', 'F', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 931', '2012-09-09', NULL, 'GUILLAUME KASKOTI', 'NDOOLE BINDU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8365', NULL, 'actif', 4),
(487, NULL, 'AHANA', 'MULIHANO', 'PLAMEDI', 'F', 'GOMA', 'CONGOLAISE', 'KATOYI, PINGA, 31', '2012-11-12', NULL, 'FAKAGE MULIHANO', 'KAHINDO CHIBUGA', 'NORD-KIVU', 'KALEHE', NULL, NULL, NULL, '8366', NULL, 'actif', 4),
(488, NULL, 'BWIRA', 'BWENGE', 'ESTHER', 'F', 'GOMA', 'CONGOLAISE', 'KASIKA, DIKUTA, 01', '2013-12-06', NULL, 'JACQUES BWENGE', 'KAHINDO NDUURA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '8367', NULL, 'actif', 4),
(489, NULL, 'MURHABAZI', 'MUGISHO', 'ASHIME', 'M', 'CHIBARHAMA', 'CONGOLAISE', 'KATOYI, ZIZI', '2020-04-15', NULL, 'HABINAMWISHO', 'OLINABANJI ORELIE', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '7484', NULL, 'actif', 4),
(490, NULL, 'AHANA', 'BANTU', 'FLORYBERT', 'M', 'KALEHE', 'CONGOLAISE', 'KATOYI, USHIRIKA, 283', '2021-06-20', NULL, 'BANTU TAHAGARHWA', 'KOKO NYAMUGANDA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7488', NULL, 'actif', 4),
(491, NULL, 'IRAGI', 'KABUMBA', 'GRATIEN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, USHIRIKA, -', '2021-03-09', NULL, 'KABUMBA', 'NAMUNGANGA', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '7489', NULL, 'actif', 4),
(492, NULL, 'BALUME', 'BUNONGO', 'IBRAHIM', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI III, UHURU, 21', '2012-12-13', NULL, 'SAFARI BUNONGO', 'NEEMA BUHENDWA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7491', NULL, 'actif', 4),
(493, NULL, 'IRUNGU', 'MASAKU', 'ESPERANT', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2022-01-23', NULL, 'IRUNGU MASAKU PHILIPPE', 'LUKOGO MASASI PASCALINE', 'HAUT KATANGA', NULL, NULL, NULL, NULL, '7494', NULL, 'actif', 4),
(494, NULL, 'IRUNGU', 'MASAKU', 'VICTOIRE', 'M', 'MAMBASA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2020-10-25', NULL, 'IRUNGU MASAKU PHILIPPE', 'LUKOGO MASASI PASCALINE', 'HAUT KATANGA', NULL, NULL, NULL, NULL, '7495', NULL, 'actif', 4),
(495, NULL, 'ALAMBO', 'LONDO', 'JORDAN', 'M', 'KATANA', 'CONGOLAISE', NULL, '2021-08-25', NULL, 'LONDO GABY', 'NEEMA BISHWEKA NELLY', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7497', NULL, 'actif', 4),
(496, NULL, 'LUKUBA', 'KALYOTO', 'PRITHAME', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 021', '2022-08-21', NULL, 'HABARI INNOCENT', 'TUMAINI RUTH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7498', NULL, 'actif', 4),
(497, NULL, 'TUNFIKANE', 'BIAKA', 'EXAUCE', 'M', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 923', '2020-02-12', NULL, 'ISAYA BIAKA HERITIER', 'FRACINE MUHAMBIKWA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7501', NULL, 'actif', 4),
(498, NULL, 'AKONKWA', 'BALEMBA', 'LAURIENT', 'M', 'GOMA', 'CONGOLAISE', NULL, '2013-05-08', NULL, 'BALEMBA JUSTIN', 'BULAMBO CLAUDINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7503', NULL, 'actif', 4),
(499, NULL, 'MUSA', 'KASHIRA', 'ENOCK', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2006-12-31', NULL, 'KASHIRA MUSAFIRI', 'NTUMBA MUSHIYA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7505', NULL, 'actif', 4),
(500, NULL, 'NGABOYEKA', 'BISIMWA', 'NELSON', 'M', 'MBIZA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2019-06-06', NULL, 'JOHN BISIMWA', 'BULONZA MWEZE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7506', NULL, 'actif', 4),
(501, NULL, 'USHINDI', 'BISIMWA', 'KEVIN', 'M', 'MBIZA', 'CONGOLAISE', 'NGANNGI, NTINGI, 93', '2020-08-01', NULL, 'JOHN BISIMWA', 'BULONZA MWEZE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7507', NULL, 'actif', 4),
(502, NULL, 'BATUMIKE', 'BISIMWA', 'MOISE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 932', '2018-03-06', NULL, 'BISIMWA BATUMIKE', 'FRANCINE BISENGI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7508', NULL, 'actif', 4),
(503, NULL, 'BYAKIFUNGA', 'PILIPILI', 'ARMAND-CHRIST', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 832', '2023-03-13', NULL, 'BYAKIFUNGA PILIPILI ARMED', 'CARINE MUSIMBI QUEEN', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7509', NULL, 'actif', 4),
(504, NULL, 'NAMIGOGO', 'MUSHAMUKA', 'JONATHAN', 'M', 'KISANGANI', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2020-12-09', NULL, 'MUSHAMUKA NAMIGOGO', 'BEKANDA KOSI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7513', NULL, 'actif', 4),
(505, NULL, 'BILUGE', 'BAGAYA', 'TRESOR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 032', '2012-04-04', NULL, 'BAGAYA', 'FURAHA CHISHIBAJI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7515', NULL, 'actif', 4),
(506, NULL, 'BIBENTYO', 'BAGAYA', 'JOEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 031', '2012-04-13', NULL, 'BAGAYA', 'FURAHA CHISHIBANJI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7516', NULL, 'actif', 4),
(507, NULL, 'DAUDI', 'NGANIRWA', 'CHERUBIN', 'M', 'KITSHANGA', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2015-08-07', NULL, 'NGANIRWA', 'FURAHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7517', NULL, 'actif', 4),
(508, NULL, 'ISHARA', 'NGANIRWA', 'BARAKA', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 74', '2014-07-19', NULL, 'NGANIRWA', 'FURAHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7518', NULL, 'actif', 4),
(509, NULL, 'ANDEMA', 'HABAMUNGU', 'KEVIN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 93', '2022-10-02', NULL, 'HABAMUNGU SERGE', 'BUHORO LUCIENNE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7523', NULL, 'actif', 4),
(510, NULL, 'BARAKA', 'LUGANO', 'JOSIAS', 'M', 'GOMA', 'CONGOLAISE', 'NDOSHO, RULENGA, 93', '2015-05-25', NULL, 'CHIKURU LUGANO', 'IRENE LWAGERA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7530', NULL, 'actif', 4),
(511, NULL, 'MURHABAZI', 'BAHATI', 'ARISTIDE', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2010-06-29', NULL, 'BAHATI MWEZE', 'NYAMUTO ODACCINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7535', NULL, 'actif', 4),
(512, NULL, 'UWEZO', 'MUGISHO', 'JOSUE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NNTINGI, 32', '2014-03-31', NULL, 'CHIHUGUYU KEAN-MARIE', 'MAPENDO JUSTINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7537', NULL, 'actif', 4),
(513, NULL, 'ASHUZA', 'MURHULA', 'JULIEN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 301', '2020-07-27', NULL, 'MURHULA BAHATI', 'NTAKABONO MIGABO', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7538', NULL, 'actif', 4),
(514, NULL, 'MWEMA', 'LUENDO', 'GAD', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, MUHAVURA, 23', '2013-01-17', NULL, 'SADIKI JOHN', 'MICHELINE KANINGU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7544', NULL, 'actif', 4),
(515, NULL, 'AKONKWA', 'MUNGANGA', 'OSCAR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2022-06-15', NULL, 'EMMANUEL MUNGANG', 'NEEMA MORISHO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7549', NULL, 'actif', 4),
(516, NULL, 'MUGISHO', 'MUNGANGA', 'ORLIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2016-03-29', NULL, 'EMMANUEL MUNGANGA', 'NEEMA MORISHO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7550', NULL, 'actif', 4),
(517, NULL, 'NDAANE', 'BAUMA', 'DAVID', 'M', 'MASISI', 'CONGOLAISE', 'NGANGI, UHURU, 31', '2011-10-10', NULL, 'BAUMA BALINGENE TIMOTHEE', 'BWIRA BAHATI EUGENIE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7552', NULL, 'actif', 4),
(518, NULL, 'AMANI', 'KAYUMBU', 'GEOFFREY', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2021-01-29', NULL, 'BENJAMIN KAYUMBU', 'LEA LUKOMERE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7553', NULL, 'actif', 4),
(519, NULL, 'BARAKA', 'BAHATI', 'JOSEPH', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 213', '2021-07-30', NULL, 'BAHATI NYAKANA', 'NABINTU RUTAKANGWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7555', NULL, 'actif', 4),
(520, NULL, 'KOMBI', 'MUSONI', 'GEORGES', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 12', '2021-06-05', NULL, 'MUSONI KAMAVU ALBERT', 'MATHE NZIAVAKE PASCALINE', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7561', NULL, 'actif', 4),
(521, NULL, 'MASEYI', 'MUSONI', 'HERITIER-LEON', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 12', '2019-11-10', NULL, 'MUSONI KAMAVU ALBERT', 'MATHE NZIYAVAKE PASCALINE', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7563', NULL, 'actif', 4),
(522, NULL, 'AKUZWE', 'MIGABO', 'VOLONTE', 'M', 'MINOVA', 'CONGOLAISE', 'NGANGI, NTINGI, 311', '2013-04-27', NULL, 'BAZAMANZA WAMIGABO', 'KAHINDO EMELINE', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '7565', NULL, 'actif', 4),
(523, NULL, 'DAVID', 'SHABANI', 'ANDRE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 63', '2021-12-27', NULL, 'CHIMANUKA SHABANI', 'NZIGIRE MAGAYANE', 'SUD-KIVU', 'MASINGO', NULL, NULL, NULL, '7566', NULL, 'actif', 4),
(524, NULL, 'LWANZO', 'MAHAMBA', 'JOHNNY', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 92', '2021-10-29', NULL, 'MAHAMBA JAPHET', 'VANESSA BOMBATA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7580', NULL, 'actif', 4),
(525, NULL, 'HABAMUNGU', 'MUGISHO', 'JORDAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2009-02-03', NULL, 'MUGISHO RUVUNANGIZA', 'ELIZA BAZUNGU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7581', NULL, 'actif', 4),
(526, NULL, 'NDEKO', 'KANIKO', 'GLOIRE', 'M', 'GOMA', 'CONGOLAISE', 'KIZIBA I, CATHOLIQUE, 93', '2008-12-12', NULL, 'BANDU KANIKO', 'SIFA KAMBUMBU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7582', NULL, 'actif', 4),
(527, NULL, 'KIRIZA', 'MUSHAGALUSA', 'ISRAEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 73', '2021-08-21', NULL, 'DAVID MUSHAGALUSA', 'ESTHER SHUKURU', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7585', NULL, 'actif', 4),
(528, NULL, 'MICHEL', 'NAMEGABE', 'MICHAEL', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 83', '2008-02-20', NULL, 'NAMEGABE KULIMUSHI', 'JOSLYNE BADERHA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7602', NULL, 'actif', 4),
(529, NULL, 'MULIMWENTUGA', 'BUJIRIRI', 'EDO', 'M', 'MBOBERO', 'CONGOLAISE', NULL, '2008-08-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7607', NULL, 'actif', 4),
(530, NULL, 'MUNGUAHANA', 'KASI', 'KELLY', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTI?GI, 23', '2022-09-07', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7608', NULL, 'actif', 4),
(531, NULL, 'AKSANTI', 'CHITO', 'LIEUVAIN', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, KISIBANGI, 93', '2021-08-13', NULL, 'CHITO RUNGAZI', 'MAPENDO CHIZA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7612', NULL, 'actif', 4),
(532, NULL, 'KOKO', 'MAPENDO', 'DAVID', 'M', 'MINOVA', 'CONGOLAISE', 'HIMBI, WALIKALE, 169', '2011-05-27', NULL, 'MAPENDO LWIRUNGU', 'KITUMAINI NYAMUSHAGAZA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7613', NULL, 'actif', 4),
(533, NULL, 'BAHATI', 'SEREYA', 'IBRAHIM', 'M', 'SAKE', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2008-03-29', NULL, 'SEREYA', 'VUMULIYA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7618', NULL, 'actif', 4),
(534, NULL, 'ELISHA', 'BALUME', 'VAINQUEUR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2000-10-28', NULL, 'FURAHA MASOMO', 'ASIN LUKOO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7620', NULL, 'actif', 4),
(535, NULL, 'KOKOLULEMA', 'MALEKERA', 'LEON', 'M', 'KINDU', 'CONGOLAISE', 'NGANGI, NTINGI, 13', '2018-04-23', NULL, 'CHUBAKA MALEKERA', 'SEZUMURIMO NSIKU LUCIE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7624', NULL, 'actif', 4),
(536, NULL, 'MUKOBELWA', 'WANZAMBI', 'JUNIOR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2012-11-15', NULL, 'MUKOBELWA PATRICK', 'FRANCINE MASAKA', 'SUD-KIVU', 'KAMITUGA', NULL, NULL, NULL, '7628', NULL, 'actif', 4),
(537, NULL, 'MUKOBELWA', 'WANZAMBI', 'GIRESS', 'M', 'GOMA', 'CONGOLAISE', NULL, '2015-02-19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7629', NULL, 'actif', 4),
(538, NULL, 'AGANZE', 'MUPENZI', 'MOÏSE', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2012-12-15', NULL, 'MUPENZI', 'ZAWADI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7634', NULL, 'actif', 4),
(539, NULL, 'KULIMUSHI', 'BIGABO', 'PATIENT', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, WANDEKA, 93', '2017-11-19', NULL, 'MEDI KYANZA', 'JOYCE FURAHA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7640', NULL, 'actif', 4),
(540, NULL, 'BARAKA', 'BISONGA', 'ENOCK', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2019-06-10', NULL, 'CLEMENT BISONGA', 'MARIE NONDO', 'MANIEMA', 'KASONGO', NULL, NULL, NULL, '7643', NULL, 'actif', 4),
(541, NULL, 'AKSANTI', 'WATELANINWA', 'SAMUEL', 'M', 'KAVUMU', 'CONGOLAISE', 'KATOYI, BWITO, 006', '2014-02-16', NULL, 'WATELANINWA MUKUNGILWA', 'MWINJA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7647', NULL, 'actif', 4),
(542, NULL, 'MAHUKANYA', 'SHABANI', 'CHRITOPHE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 92', '2016-07-17', NULL, 'SHABANI', 'BULAMBO', 'SUD-KIVU', 'KINDU', NULL, NULL, NULL, '7649', NULL, 'actif', 4),
(543, NULL, 'MAHUKANYA', 'SHABANI', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 92', '2018-02-19', NULL, 'SHABANI', 'BULAMBO', 'SANGURU', 'KINDU', NULL, NULL, NULL, '7650', NULL, 'actif', 4),
(544, NULL, 'MUGONDOZI', 'BULAMBO', 'ELKANA', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 92', '2015-01-04', NULL, 'SHABANI', 'BULAMBO', 'SANGURU', 'KINDU', NULL, NULL, NULL, '7651', NULL, 'actif', 4),
(545, NULL, 'MUHINDO', 'BOSOLE', 'ALEXIS', 'M', 'KATANA', 'CONGOLAISE', 'KATOYI, BIKUKWE, 92', '2008-10-25', NULL, 'BUSOLE DIDAS', 'FURAHA MWAKASHOFU', 'SUD-KIVU', 'KATANA', NULL, NULL, NULL, '7652', NULL, 'actif', 4),
(546, NULL, 'FARAJA', 'BATUMIKE', 'MARLENE', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2021-04-09', NULL, 'BATUMIKE PATRICK', 'SIFA BUHALIZA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7655', NULL, 'actif', 4),
(547, NULL, 'AMISI', 'ACHIBONEKA', 'CLARK', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2020-05-15', NULL, 'ACHIBONEKA ELIE', 'MWAMINI ALLIANCE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7657', NULL, 'actif', 4),
(548, NULL, 'KIYANA', 'MUNIHIRE', 'CHRISPIN', 'M', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2012-11-06', NULL, 'MICHEL MUNIHIRE', 'SARAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7669', NULL, 'actif', 4),
(549, NULL, 'BALUME', 'MUNIHIRE', 'INNOCENT', 'M', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2010-09-30', NULL, 'MICHEL MUNIHIRE', 'SARAH', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7670', NULL, 'actif', 4),
(550, NULL, 'ALADIN', 'UZARAMA', 'PROMESSE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 36', '2020-10-14', NULL, 'BAHATI UZARAMA', 'ARIANE ISORA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7680', NULL, 'actif', 4),
(551, NULL, 'KASEREKA', 'ISORA', 'SALOMON', 'M', 'KIRUMBA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 36', '2019-08-08', NULL, 'MWISSA ISORA', 'KAVIRA LOUISE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7681', NULL, 'actif', 4),
(552, NULL, 'MURHULA', 'MASTAKI', 'JEAN-LUC', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 93', '2020-05-20', NULL, 'MASTAKI JEAN-PIERRE', 'NSIMIRE BASHIGE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7683', NULL, 'actif', 4),
(553, NULL, 'MATUNGULU', 'BUSI', 'ANDRE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2012-08-24', NULL, 'MARC MATUNGULU', 'MPASA MUTUMBI', 'MANIEMA', 'PANGI', NULL, NULL, NULL, '7684', NULL, 'actif', 4),
(554, NULL, 'KYAKULONDA', 'ILUNGA', 'MIRADI', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 92', '2018-11-26', NULL, 'JEAN-CLAUDE ILUNGA', 'ODREY MASIKA', 'KASAI', NULL, NULL, NULL, NULL, '7685', NULL, 'actif', 4),
(555, NULL, 'AMANI', 'CHIKURU', 'GEDEON', 'M', 'KABAMBA', 'CONGOLAISE', 'NGANGI, NTINGI, 192', '2015-02-15', NULL, 'CHIKURU BIREGO', 'BORA MITIMA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7686', NULL, 'actif', 4),
(556, NULL, 'AKONKWA', 'CHIKURU', 'JORDAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 192', '2017-04-22', NULL, 'CHIKURU BIREGO', 'BORA MITIMA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7687', NULL, 'actif', 4),
(557, NULL, 'MUHINDO', 'NDOOLE', 'AIMEDO', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2013-11-22', NULL, 'KUBUYA NDOOLE', 'KAHINDO JULIENNE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7690', NULL, 'actif', 4),
(558, NULL, 'USHINDI', 'NDOOLE', 'ALBER', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 36', '2019-04-11', NULL, 'KUBUYA NDOOLE', 'KAHINDO JULIENNE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7693', NULL, 'actif', 4),
(559, NULL, 'WASSO', 'WABENGA', 'JEREMIE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KILIMANJARO, 12', '2026-08-06', NULL, 'ELIAS WASSO', 'SIFA MIRINDI', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '7697', NULL, 'actif', 4),
(560, NULL, 'KANZA', 'BALONGA', 'KEVIN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, MABADILIKO, 823', '2021-04-13', NULL, 'KANZA ALUNGA FELIX', 'PENDANO KABULE AIME', 'EQUATEUR', NULL, NULL, NULL, NULL, '7704', NULL, 'actif', 4),
(561, NULL, 'KAKULE', 'KINDUHI', 'CHRISTIAN', 'M', 'GOMA', 'CONGOLAISE', 'KASIKA, BIKINDWE, 33', '2010-07-11', NULL, 'MUHINDO NZOVOLI', 'ANOUARITE BUHOLO', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7705', NULL, 'actif', 4),
(562, NULL, 'MUMBERE', 'ZABULUNA', 'CHRISTIAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 23', '2017-12-31', NULL, 'KAMBALE TRESOR', 'FURAHA ISSA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7706', NULL, 'actif', 4),
(563, NULL, 'MUGISHO', 'BABWINE', 'BARAKA', 'M', 'GOMA', 'CONGOLAISE', 'HIMBI, DE LA PAIX, 12', '2011-08-28', NULL, 'MANGE BABWINE', 'BADESIRE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7716', NULL, 'actif', 4),
(564, NULL, 'NZITA', 'MUNGUIKO', 'JOSIAS', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 19', '2017-04-20', NULL, 'ANICET MUNGUIKO', 'SIFA BAGUNDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7724', NULL, 'actif', 4),
(565, NULL, 'YATOSHA', 'NZITA', 'CHRISTIVI', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 19', '2017-07-29', NULL, 'ANICET MUNGUIKO', 'SIFA BAGUNDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7725', NULL, 'actif', 4),
(566, NULL, 'MIHATI', 'NZITA', 'JOSAPHAT', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 19', '2022-10-02', NULL, 'ANICET MUNGUIKO', 'SIFA BAGUNDA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7726', NULL, 'actif', 4),
(567, NULL, 'BIRIMWIRAGI', 'BAGEREKA', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 73', '2020-04-04', NULL, 'BULANGALIRE BAGEREKA', 'ISHARA NABUSIKE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7729', NULL, 'actif', 4),
(568, NULL, 'SAMUEL', 'MUZANDWA', 'KATHY', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2006-03-26', NULL, 'GUILLAUMME MUZANDWA', 'KATUGU SIKIRYAMUVA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7734', NULL, 'actif', 4),
(569, NULL, 'BUSHU', 'MULIRO', 'DANIEL', 'M', 'WALIKALE', 'CONGOLAISE', 'KATOTI, ZIZI, 21', '2012-10-09', NULL, 'BUSHU DENIS', 'LUBUNGO THERESE', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7737', NULL, 'actif', 4),
(570, NULL, 'MUTIYA', 'BUSHU', 'JUSTIN', 'M', 'WALIKALE', 'CONGOLAISE', 'KATOYI, ZIZI, 12', '2010-09-06', NULL, 'BUSHU DENIS', 'LUBUNGO THERESE', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7738', NULL, 'actif', 4),
(571, NULL, 'AMANI', 'BYAMUNGU', 'JOSPIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2008-01-23', NULL, 'GEROME BYAMUNGU', 'SOLANGE ZAWADI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7746', NULL, 'actif', 4),
(572, NULL, 'BARIKIWA', 'KAHAJIRE', 'CLEMENT', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2017-07-06', NULL, 'MASUMBUKO KAHAJIRE', 'RECHEL KYALONDAWA', 'SUD-KIVU', 'MUDAKA', NULL, NULL, NULL, '7748', NULL, 'actif', 4),
(573, NULL, 'SIVAHAVWA', 'PALUKU', 'VAINQUEUR', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2020-07-16', NULL, 'KASEREKA AMI', 'MUOMBAMUNGU SEBYERA', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '7753', NULL, 'actif', 4),
(574, NULL, 'MASHUKANO', 'FAIDA', 'CHRINOVIC', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 12', '2012-04-22', NULL, 'MASHUKANO BARONYI', 'FEZA BAHATI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7759', NULL, 'actif', 4),
(575, NULL, 'MUHINDO', 'KASKOTI', 'BENEDICTION', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2020-08-16', NULL, 'GUILLAUME KASKOTI', 'NDOOLE BINDU', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7763', NULL, 'actif', 4),
(576, NULL, 'MASU', 'BASHIZI', 'GRACE', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 83', '2009-01-01', NULL, 'BASHIZI MASU', 'FURAHA BUHENDWA', 'SUD-KIVU', 'WALUNGU', NULL, NULL, NULL, '7766', NULL, 'actif', 4),
(577, NULL, 'KALUMUNA', 'RAMAZANI', 'BENJAMIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2019-05-01', NULL, 'MUTALE RAMAZANI', 'ELYSEE ZAGABE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7768', NULL, 'actif', 4),
(578, NULL, 'MUSHAGALUSA', 'BANDERIRE', 'PROMESSE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 099', '2020-09-26', NULL, 'MURHULA BANDERIRE', 'NZIGIRE ZIHINDULA FLORENCE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7777', NULL, 'actif', 4),
(579, NULL, 'BUTINDA', 'KANYANGE', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2016-06-18', NULL, 'JUMA KANYANGE', 'RACHEL MAENE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7779', NULL, 'actif', 4),
(580, NULL, 'BINYANGE', 'KYATENDA', 'PHILEMON', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2018-11-13', NULL, 'SELESTIN BINYANGE', 'LINDA ZAWADI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7782', NULL, 'actif', 4),
(581, NULL, 'BINYANGE', 'KYATENDA', 'SAMUEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2016-12-12', NULL, NULL, NULL, 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7783', NULL, 'actif', 4),
(582, NULL, 'MWISHA', 'BWIRA', 'BIENVENU', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-11-28', NULL, 'BWIRA BUUMA', 'KAHINDO NAMUGENZI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7792', NULL, 'actif', 4),
(583, NULL, 'BARAKA', 'MUHINDO', 'BENJAMIN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 129', '2017-01-29', NULL, 'MUHINDO MWENDAPOLE', 'FURAHA MIRUMBI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7793', NULL, 'actif', 4),
(584, NULL, 'MBURA', 'MUSHUNGANYA', 'LEVIS', 'M', 'KIBUA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2012-04-05', NULL, 'MBURA GEORGES', 'NKUO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7797', NULL, 'actif', 4),
(585, NULL, 'IRAGI', 'BUHENDWA', 'SALOMON', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KILIMANJARO 1, 92', '2012-11-09', NULL, 'BUHENDWA JACQUES', 'CHIKWANINE MULUMEODERHWA', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '7800', NULL, 'actif', 4),
(586, NULL, 'RUBIPI', 'CHIRAHONGERWA', 'STEPHAN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KILIMANJARO 1, 92', '2007-11-25', NULL, 'SIMEON AMANI', 'LUANDA MAFILLE', 'NORD-KIVU', 'IDJWI', NULL, NULL, NULL, '7801', NULL, 'actif', 4),
(587, NULL, 'KIBISHINGO', 'WILONDJA', 'QUERENE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 23', '2010-08-30', NULL, 'KIBISHINGO WILONJA', 'MALENGE MWENYEMALI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7807', NULL, 'actif', 4),
(588, NULL, 'MUMBERE', 'KABUYAYA', 'ISRAEL', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 82', '2019-01-20', NULL, 'EVA KABUYAYA', 'MERVEILLE SHABANI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7811', NULL, 'actif', 4),
(589, NULL, 'URITI', 'NZANGI', 'JUSCAR', 'M', 'SAKE', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2021-06-22', NULL, 'NZANGI BOYA', 'FURAHA JANVIER', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7812', NULL, 'actif', 4),
(590, NULL, 'USENI', 'RACHIDI', 'GABRIEL', 'M', 'WALIKALE', 'CONGOLAISE', 'NGANGI, NTINGI, 301', '2023-02-20', NULL, 'RACHIDI USENI', 'ANIFA LUKUSA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '7819', NULL, 'actif', 4),
(591, NULL, 'RWAMANGA', 'BASHIGE', 'ARTHUR', 'M', 'GOMA', 'CONGOLAISE', NULL, '2019-07-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7832', NULL, 'actif', 4),
(592, NULL, 'AMANI', 'AKSANTI', 'LEON', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 132', '2002-04-20', NULL, 'AKSANTI', 'KATAMBWE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7840', NULL, 'actif', 4),
(593, NULL, 'HERI', 'MUZALIWA', 'HEUREUX', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2013-11-27', NULL, 'MUHINDO DAVID', 'NABINTU FIKIRI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7846', NULL, 'actif', 4),
(594, NULL, 'CHANCE', 'HANGI', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', 'KATINDO, 21', '2014-01-28', NULL, 'PATRICK MUHIRWA', 'AMISA HANGI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7852', NULL, 'actif', 4),
(595, NULL, 'MUBALAMA', 'MUGANUZI', 'SALOMON', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 92', '2014-01-14', NULL, 'MUGANUZI NKOYI', 'FURAHA JOYCE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7853', NULL, 'actif', 4),
(596, NULL, 'KALUME', 'MORISHO', 'FREDERICK', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 121', '2020-04-04', NULL, 'MALENDERA', 'BORA BAHATI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7854', NULL, 'actif', 4),
(597, NULL, 'BYAMUNGU', 'MINGELE', 'CHRITEVI', 'M', 'GOMA', 'CONGOLAISE', 'HIMBI, DU LAC, 004', '2023-11-03', NULL, 'BYAMUNGU MINGELE', 'ADELA MASTAKI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7855', NULL, 'actif', 4),
(598, NULL, 'OLENGA', 'ONGO', 'ISAAC', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 132', '2021-06-16', NULL, 'ALAIN ONGO', 'JUSTINE MUNGUAKONKWA', 'KONGO CENTRAL', NULL, NULL, NULL, NULL, '7865', NULL, 'actif', 4),
(599, NULL, 'MURHULA', 'BISIMWA', 'KEVIN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2015-03-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7872', NULL, 'actif', 4),
(600, NULL, 'LUMOO', 'BINDU', 'EXAUCEE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 9821', '2011-10-24', NULL, 'MAPASA BINDU', 'EMILIE CHIBALONZA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7884', NULL, 'actif', 4),
(601, NULL, 'BAHATI', 'KARESI', 'MERDI', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 12', '2019-09-07', NULL, 'USHINDI KARESI', 'EVODIE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7893', NULL, 'actif', 4),
(602, NULL, 'CHUBAKA', 'MULIRI', 'DIDIER', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, MUHAVURA, 92', '2012-01-21', NULL, 'MULIRI BAGANDA', 'CHANCE SHANVU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7896', NULL, 'actif', 4),
(603, NULL, 'KIMENGE', 'NDOOLE', 'CALEB', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 0231', '2012-03-04', NULL, 'MUFANO NDOOLE', 'JUDITH MWETI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7897', NULL, 'actif', 4),
(604, NULL, 'MUFANO', 'NDOOLE', 'THEOPHILE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2015-10-08', NULL, 'MUFANO NDOOLE', NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7898', NULL, 'actif', 4),
(605, NULL, 'BWIRA', 'NDOOLE', 'VAINQUEUR', 'M', 'MASISI', 'CONGOLAISE', NULL, '2017-08-17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7900', NULL, 'actif', 4),
(606, NULL, 'MUHANZI', 'MULUMEODERHWA', 'JEAN-CLAUDE', 'M', 'GOMA', 'CONGOLAISE', NULL, '2021-03-20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7904', NULL, 'actif', 4),
(607, NULL, 'TITO', 'NZAMBA', 'MIGABO', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 219', '2013-09-07', NULL, 'YOSHUWA NZAMBA', 'CECILE BAKULU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7906', NULL, 'actif', 4),
(608, NULL, 'BAHATI', 'BARUME', 'SOCRATE', 'M', 'MINOVA', 'CONGOLAISE', 'NGANGI, NTINGI, 22', '2018-06-10', NULL, 'ISAAC BARUME', 'DESANGES JUVENALE', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '7907', NULL, 'actif', 4),
(609, NULL, 'BAUMA', 'BAHATI', 'EBENEZER', 'M', 'GOMA', 'CONGOLAISE', NULL, '2018-04-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7910', NULL, 'actif', 4),
(610, NULL, 'MUNIHIRE', 'BAHATI', 'ELIEZER', 'M', 'GOMA', 'CONGOLAISE', NULL, '2020-07-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7911', NULL, 'actif', 4),
(611, NULL, 'AGANZE', 'AMANI', 'JORDAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 231', '2019-01-12', NULL, 'AMANI HAMULI', 'FEZA BAHATI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7917', NULL, 'actif', 4),
(612, NULL, 'HABAMUNGU', 'MUTARAHAZA', 'DIEUMERCI', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2011-02-13', NULL, 'MUGISHO', 'ZAWADI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7923', NULL, 'actif', 4),
(613, NULL, 'ASIFIWE', 'KWITONDA', NULL, 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2017-05-22', NULL, 'KWITONDA', 'CATHERINE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7924', NULL, 'actif', 4),
(614, NULL, 'MASHUKANO', 'KWITONDA', NULL, 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2016-12-14', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7925', NULL, 'actif', 4),
(615, NULL, 'SALAMA', 'KAMANZI', 'CLEMENT', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 82', '2014-06-16', NULL, 'CLAUDE KAMANZI', 'CLAUDINE KAISHUSHSA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7926', NULL, 'actif', 4),
(616, NULL, 'AGANZE', 'NTAKOBAJIRA', NULL, 'M', 'GOMA', 'CONGOLAISE', NULL, '2011-11-25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7929', NULL, 'actif', 4),
(617, NULL, 'ALAMBO', 'LONDO', 'JORDAN', 'M', 'KATANA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2021-08-25', NULL, 'GABRIEL LONDO', 'NEEMA BISHWEKA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7936', NULL, 'actif', 4),
(618, NULL, 'LUNANGA', 'MWENDELWA', 'BENJAMIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2017-09-15', NULL, 'ISAAC LUNANGA', 'LYDIE MAHAMBA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '7937', NULL, 'actif', 4),
(619, NULL, 'MURHULA', 'MIRINDI', 'BLAISE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2021-01-10', NULL, 'JANVIER MISEDU', 'ESTHER MUHINDO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7938', NULL, 'actif', 4),
(620, NULL, 'AGANZE', 'KANDANDA', 'XAVIER', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-12-25', NULL, 'KANDANDA', 'BORAUZIMA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7939', NULL, 'actif', 4),
(621, NULL, 'OMBENI', 'HABAMUNGU', 'GHISLAIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2012-11-09', NULL, 'INNOCENT KANYENYEZI', 'SIKA KAHINDO', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '7943', NULL, 'actif', 4),
(622, NULL, 'KITSA', 'NDOOLE', 'VICTOIRE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-03-21', NULL, 'MUFANO NDOOLE', 'RIZIKI MWETI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7944', NULL, 'actif', 4),
(623, NULL, 'MAGAYWA', 'ZIHINDULA', 'JUNIOR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2011-11-28', NULL, 'MAGAYWA DIEUDONEE', 'BIHANGO KYAHI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7946', NULL, 'actif', 4),
(624, NULL, 'MIRIMO', 'MITONDEKE', 'HORIZON', 'M', 'NYABYONDO', 'CONGOLAISE', 'NGANGI, NTINGI, 91', '2010-01-01', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7948', NULL, 'actif', 4),
(625, NULL, 'KITUMAINI', 'NYAMUGABIRA', 'ESPOIR', 'M', 'GOMA', 'CONGOLAISE', NULL, '2008-05-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7951', NULL, 'actif', 4),
(626, NULL, 'MWAMBA', 'MASUNGANE', 'CLODIAS', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 31', '2018-04-21', NULL, 'MWAMBA MASUNGANE', 'MARCELINE BINDU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7955', NULL, 'actif', 4),
(627, NULL, 'MWAMBA', 'MASUNGANE', 'CLOVIS', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI', '2020-07-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7956', NULL, 'actif', 4),
(628, NULL, 'BALAGIZI', 'MUNGANGA', 'DAVID', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2021-05-05', NULL, 'AMANI BALAGIZI', 'SAFI BAGALWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7958', NULL, 'actif', 4),
(629, NULL, 'FATAKI', 'BUSIKILA', 'DYKLAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2020-03-11', NULL, 'CHRISTIAN BUSIKILA', 'DORCAS BADERHA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7960', NULL, 'actif', 4),
(630, NULL, 'IMANI', 'MUSHAGALUSA', 'EUGENE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KASINDI, 32', '2011-02-07', NULL, 'PASCAL MUSHAGALUSA', 'SOLANGE BANYWESIZE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7975', NULL, 'actif', 4),
(631, NULL, 'KALINDA', 'MAHULU', 'JOSAPHAT', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2020-09-18', NULL, 'KWIZERA BIFUMBO', 'MAOMBI MUDEGE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '7983', NULL, 'actif', 4),
(632, NULL, 'MUNGUIKO', 'SHAMAMBA', 'MESHACK', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOHWA, 331', '2010-06-24', NULL, 'MUNGUIKO SHAMAMBA', 'AMINA FAZILI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '7991', NULL, 'actif', 4),
(633, NULL, 'MUNGUIKO', 'SHAMAMBA', 'SHADRACK', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOHWA, 331', '2012-12-11', NULL, 'MUNGUIKO SHAMAMBA', 'AMINA FAZILI', 'SANGURU', 'KALEHE', NULL, NULL, NULL, '7992', NULL, 'actif', 4),
(634, NULL, 'BARAKA', 'MUHIGIRWA', 'ANTONNY', 'M', 'MINOVA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 31', '2010-04-04', NULL, 'WILLY MUHIIRWA', 'MAPENZI MASHIMANG', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '8003', NULL, 'actif', 4),
(635, NULL, 'MUSOMBWA', 'MATABARO', 'VAINQUEUR', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, LEMBI, 21', '2012-07-21', NULL, 'MATABARO FARAJA', 'CARINE MUSOMBWA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8004', NULL, 'actif', 4),
(636, NULL, 'MUSAFIRI', 'NDEKO', 'MOISE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-06-21', NULL, 'NDEKO JANVIER', 'LUSAGILA BRIGITTE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8008', NULL, 'actif', 4),
(637, NULL, 'FARAJA', 'BATUNDI', 'ALFRED', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 32', '2010-11-25', NULL, 'BATUNDI JOSPIN', 'HANGI BWIRA WIVINE', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8014', NULL, 'actif', 4),
(638, NULL, 'ASANI', 'MUBAWA', 'KALAMO', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 143', '2021-08-30', NULL, 'KIKANDI MUBAWA ABDOUL', 'ZAINABO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8020', NULL, 'actif', 4),
(639, NULL, 'IRAGI', 'MUSHARAMINA', 'BENJAMIN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, VISOKE, 39', '2016-06-15', NULL, 'FELICIEN MUSHARAMINA', 'NOELLA KULONDWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8022', NULL, 'actif', 4),
(640, NULL, 'SHENGOWA', 'MUHIMA', 'MOISE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKOWA, 43', '2020-07-29', NULL, NULL, NULL, 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8028', NULL, 'actif', 4),
(641, NULL, 'AJIBU', 'MUSAFARI', 'CHRISTOPHE', 'M', 'CHOFI', 'CONGOLAISE', 'KATOYI, KISIBANGI, 32', '2010-06-08', NULL, 'KABENE MUSAFIRI', 'ZAWADI BARAIGA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8031', NULL, 'actif', 4),
(642, NULL, 'NGUGHO', 'SADIKI', 'CHRISTIAN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2022-08-15', NULL, 'JEAN-CLAUDE SADIKI', 'SUSETTE KALUSHA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8033', NULL, 'actif', 4),
(643, NULL, 'LUANDA', 'KANIKI', 'BLESSING', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2022-02-24', NULL, 'AMANI KANIKI', 'ORNELLA RIBAKARI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8044', NULL, 'actif', 4),
(644, NULL, 'AMANI', 'BONANE', 'MUSA', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 321', '2018-04-10', NULL, 'OLIVIER BONANE', 'ASHA MALEKERA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8045', NULL, 'actif', 4),
(645, NULL, 'USAO', 'IYETEBINGI', 'ORISIS', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 321', '2021-06-21', NULL, 'YOSHWA USAWA', 'ADOLPHINE MPALA', 'MANIEMA', 'KASONGO', NULL, NULL, NULL, '8047', NULL, 'actif', 4),
(646, NULL, 'ACHIZA', 'BISIMWA', 'MOISE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2014-07-10', NULL, 'JEAN-CLAUDE BISIMWA', 'FITINA BAYONGA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8053', NULL, 'actif', 4),
(647, NULL, 'TUKO', 'KINDUGU', 'ISSA', 'M', 'MASISI', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2020-06-29', NULL, 'KINDUGU KITSA', 'FAIDA BANDU', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8057', NULL, 'actif', 4),
(648, NULL, 'USAO', 'LUNDULA', 'CLOVIS', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 31', '2016-08-02', NULL, 'YOSHUWA USAO', 'ADOLPHINE MPALA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8060', NULL, 'actif', 4),
(649, NULL, 'MUNYIRAGI', 'SIKUMINGI', 'BARAKA', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 11', '2015-04-12', NULL, 'BISIMWA', 'FURAHA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8064', NULL, 'actif', 4),
(650, NULL, 'MULEYI', 'VICTOIRE', 'DOUBLANCE', 'M', 'KITSHANGA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2009-06-04', NULL, 'MWISHA BAROKI', 'KANYERE MAPENDO', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8065', NULL, 'actif', 4),
(651, NULL, 'AKILIMALI', 'BUBANJI', 'BENJAMIN', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, BUKOWA, 21', '2010-09-14', NULL, 'BUBANJI SAFARI', 'KALINDA MADELEINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8073', NULL, 'actif', 4),
(652, NULL, 'MAISHA', 'KWANGAKISI', 'JOHN', 'M', 'SHABUNDA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-02-27', NULL, 'KWANGASI ROGER', 'KINYAMA CELESTINE', 'SUD-KIVU', 'SHABUNDA', NULL, NULL, NULL, '8075', NULL, 'actif', 4),
(653, NULL, 'MAOMBI', 'KABUYAYA', 'EBENEZER', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 81', '2013-05-14', NULL, 'KAMBALE KABUYAYA', 'ESTHER MBAKU', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8085', NULL, 'actif', 4),
(654, NULL, 'MBAKU', 'LONGOLONGO', 'BERNARD', 'M', 'KINDU', 'CONGOLAISE', NULL, '2019-07-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8087', NULL, 'actif', 4),
(655, NULL, 'MULINDA', 'SHENYUNGU', 'DAVID', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2012-05-12', NULL, 'MULINDA HONORE', 'MAOMBI LUANDA', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '8093', NULL, 'actif', 4),
(656, NULL, 'SHIRAMBERE', 'GABIKIRE', 'SOSTEN', 'M', 'RUTSHURU', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS', '2010-11-23', NULL, 'J0SEPH GABIKIRE', 'SALIMA MABRUKI', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '8096', NULL, 'actif', 4),
(657, NULL, 'IMOWA', 'KUBUYA', 'IBRAHIM', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 231', '2022-09-02', NULL, 'INNOCENT KUBUYA', 'JASMINE BUUMA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8100', NULL, 'actif', 4),
(658, NULL, 'CHANGANO', 'MIHIGO', 'JOSUE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2010-05-15', NULL, 'CHANGANO MAROYI', 'KAHINDO CHUMA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8101', NULL, 'actif', 4),
(659, NULL, 'MUSHAGALUSA', 'KULIMUSHI', 'NEHEMIE', 'M', 'BUKAVU', 'CONGOLAISE', 'MUGUNGA, MAPENDO, 13', '2012-04-03', NULL, 'KAMUNTU LEONARD', 'NSIMIRE GHISLAINE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8103', NULL, 'actif', 4),
(660, NULL, 'KAMBALE', 'MUASU', 'CHRISTOPHE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2007-10-09', NULL, 'MUHINDO MUASU', 'MAOMBI SHABANI', 'NORD-KIVU', 'LUBERO', NULL, NULL, NULL, '8105', NULL, 'actif', 4),
(661, NULL, 'MUKUNDA', 'MIZUMBI', 'PREMICE', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KILIMANJARO, 21', '2021-05-29', NULL, 'PATRICK MIZUMBI', 'JEMIMA AMULI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8109', NULL, 'actif', 4),
(662, NULL, 'BARAKA', 'BAAZIGA', 'ENOCK', 'M', 'GOMA', 'CONGOLAISE', NULL, '2022-01-13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8115', NULL, 'actif', 4),
(663, NULL, 'MUMBERE', 'KUBUYA', 'CHERUBIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2018-09-09', NULL, 'KUBUYA NOE', 'CHANCE RIZIKI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8122', NULL, 'actif', 4),
(664, NULL, 'CHARMANT', 'KUBUYA', 'GABRIEL', 'M', 'GOMA', 'CONGOLAISE', NULL, '2021-11-21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8123', NULL, 'actif', 4),
(665, NULL, 'AMANI', 'BAHATI', 'KEVIN', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 91', '2015-09-15', NULL, NULL, NULL, 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8134', NULL, 'actif', 4),
(666, NULL, 'AGANZE', 'SHANGALUME', 'JOSPIN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 31', '2012-01-01', NULL, 'SHANGALUME JEAN', 'SCOLA ZIHALIRWA', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '8137', NULL, 'actif', 4),
(667, NULL, 'ABONGYA', 'LOOKO', 'BENEDICTION', 'M', 'NGOMIANI', 'CONGOLAISE', 'KATOYI, VITSHUMBI, 21', '2012-08-15', NULL, 'ABONGYA', 'NAKASUNYU', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8168', NULL, 'actif', 4),
(668, NULL, 'WALUMBUKA', 'LOOKO', 'BERTRAND', 'M', 'NGOMIANO', 'CONGOLAISE', 'KATOYI, VITSHUMBI, 14', '2014-01-19', NULL, 'ABONGYA BYULA', 'NAKASUNYU', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8169', NULL, 'actif', 4),
(669, NULL, 'CHIBALONZA', 'BUGAMBWA', 'ESTHER', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2018-03-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8170', NULL, 'actif', 4),
(670, NULL, 'NDAY', 'WAKOIBWANABO', 'ETHAN', 'M', 'KAMINA', 'CONGOLAISE', 'KATOYI, ZIZI, 21', '2022-09-21', NULL, 'MASUDI KIMAMU', 'MUKAKU NDAY', 'HAUT LOMAMI', 'KASONGWANYIMBO', NULL, NULL, NULL, '8172', NULL, 'actif', 4),
(671, NULL, 'AKSANTI', 'CHIKURU', 'JEREMIE', 'M', 'KABAMBA', 'CONGOLAISE', 'NGANGI, BIHIRWA, 103', '2013-08-08', NULL, 'CHIKURU BIREGO', 'BORA MITIMA', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8174', NULL, 'actif', 4),
(672, NULL, 'MWELI', 'MUVINGI', 'YVAN', 'M', 'RUTSHURU', 'CONGOLAISE', 'NGANGI, NTINGI, 39', '2010-09-26', NULL, 'MWALI CHRISTIAN', 'KAVIRA JEANNETTE', 'NORD-KIVU', 'RUTSHURU', NULL, NULL, NULL, '8181', NULL, 'actif', 4),
(673, NULL, 'LUMOO', 'KANYANGE', 'ISMAEL', 'M', 'SAKE', 'CONGOLAISE', 'NGANGI, NTINGI, 102', '2008-08-08', NULL, 'ISMAEL KANYANGE', 'KANDU MUKEMBANYI', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8183', NULL, 'actif', 4),
(674, NULL, 'MUGWIZA', 'MATABARO', 'HERITIER', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 31', '2010-01-02', NULL, 'MUGWIZA MATABARO', 'MAOMBI SAIDATI', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8185', NULL, 'actif', 4),
(675, NULL, 'BARAKA', 'BIRINGINGWA', 'MAURICE', 'M', 'MBIZA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2017-03-18', NULL, 'BIRINGINGWA BALOLEBWAMI', 'SHUKURU MACHUMU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8186', NULL, 'actif', 4),
(676, NULL, 'IRAGI', 'BIRINGINGWA', 'KEVIN', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2019-01-14', NULL, 'BIRINGINGWA BALOLEBWAMI', 'SHUKURU MACHUMU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8187', NULL, 'actif', 4),
(677, NULL, 'BWENGE', 'HANGI', 'BIENVENU', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, UHURU, 021', '2013-12-13', NULL, 'BITASIMWA HANGI', 'NZIGIRE MWAMBUSA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8191', NULL, 'actif', 4),
(678, NULL, 'MWISHA', 'HANGI', 'GRADY', 'M', 'GOMA', 'CONGOLAISE', 'NGANI, UHURU, 021', '2016-02-27', NULL, 'BITASIMWA HANGI', 'NZIGIRE MWAMBUSA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8192', NULL, 'actif', 4),
(679, NULL, 'BWIRA', 'MIRINDI', 'OSTERE', 'M', 'MINOVA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 21', '2010-10-10', NULL, 'THEOPHILE ZABIKA', 'SAFI BWIRA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8196', NULL, 'actif', 4),
(680, NULL, 'AMANI', 'BAUMA', 'ADILI', 'M', 'MINOVA', 'CONGOLAISE', 'KATOYI, DANQUIER MUITI, 32', '2009-04-09', NULL, 'BAUMA JOSEPH', 'VICTORINE NAKARANGA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8201', NULL, 'actif', 4),
(681, NULL, 'MUGISHO', 'CHIRUZA', 'JOSPIN', 'M', 'MULENGEZI', 'CONGOLAISE', 'NGANGI, NTINGI, 32', '2011-08-30', NULL, 'CHIRUZA MURHAMBWA', NULL, 'SUD-KIVU', 'MUDAKA', NULL, NULL, NULL, '8202', NULL, 'actif', 4),
(682, NULL, 'AMANI', 'BADERHEKUGUMA', 'PASCAL', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, KISIBANGI, 31', '2014-04-25', NULL, 'ADOLPHE BADERHEKUGUMA', 'NSIMIRE KIRERU', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8207', NULL, 'actif', 4),
(683, NULL, 'MUHESHIMIWA', 'ENOCK', 'VAINQUEUR', 'M', 'GOMA', 'CONGOLAISE', NULL, '2020-05-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8221', NULL, 'actif', 4),
(684, NULL, 'RAMAZANI', 'AMISI', 'KATOLE', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 31', '2005-06-06', NULL, 'AMISI KATOLE', 'ZUNA CHIGABI', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8228', NULL, 'actif', 4),
(685, NULL, 'CHIKURU', 'BUTIKANGA', 'MIKA', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 311', '2009-09-05', NULL, 'JEAN-MARIE BUTIKANGA', 'FAIDA DORCAS', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8231', NULL, 'actif', 4),
(686, NULL, 'USHINDI', 'NGOSHA', 'PATRICE', 'M', 'KALEMIE', 'CONGOLAISE', 'NGANGI, NTINGI, 21', '2014-06-09', NULL, 'RACHIDI', 'NICOLE OMBA', 'LUALABA', 'KALEMIE', NULL, NULL, NULL, '8237', NULL, 'actif', 4),
(687, NULL, 'USHINDI', 'MUGANZA', 'VAIQUEUER', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, KISIBANGI, 21', '2018-09-10', NULL, 'TUUNGANE MUGANZA', 'TUOMBE PRISCA', 'SUD-KIVU', 'MWENGA', NULL, NULL, NULL, '8239', NULL, 'actif', 4),
(688, NULL, 'BITATINDWA', 'BALUME', 'JEAN-CLAUDE', 'M', 'WALIKALE', 'CONGOLAISE', 'KATOYI, KILIMANJARO, 210', '2010-08-16', NULL, 'BAENI BALINDA', 'FRANCINE SIBAZIKE', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8251', NULL, 'actif', 4),
(689, NULL, 'KITENGE', 'MISEYI', 'JEAN', 'M', 'BUKAVU', 'CONGOLAISE', NULL, '2014-05-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8253', NULL, 'actif', 4),
(690, NULL, 'SHAMAMBA', 'MITSORI', 'JOSEPH', 'M', 'GOMA', 'CONGOLAISE', 'NGANI, NTINGI, 210', '2011-02-12', NULL, 'SHAMAMBA FOCAS', 'MANGAZA KIBIRA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8254', NULL, 'actif', 4),
(691, NULL, 'BURONGU', 'KIHINONGO', 'MICHEL', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 192', '2014-10-28', NULL, 'INNOCENT', 'MAOMBI TSONGO', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8255', NULL, 'actif', 4),
(692, NULL, 'EMANGA', 'SWEDI', 'CLEOPHAS', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, BUKONDE, 291', '2013-02-17', NULL, 'EMANGA SWEDI', 'WABIWA SALOME', 'SUD-KIVU', 'FIZI', NULL, NULL, NULL, '8278', NULL, 'actif', 4),
(693, NULL, 'EMANGA', 'SWEDI', 'EXAUCE', 'M', 'GOMA', 'CONGOLAISE', NULL, '2010-03-03', NULL, 'EMANGA SWEDI', 'WABIWA', NULL, NULL, NULL, NULL, NULL, '8279', NULL, 'actif', 4),
(694, NULL, 'MUHINDO', 'SHEMATI', 'DIVIN', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, NDOWE, 012', '2014-04-30', NULL, 'FRANCK SHEMATSI', 'FURAHA BYADUNIA', 'NORD-KIVU', 'MASISI', NULL, NULL, NULL, '8283', NULL, 'actif', 4),
(695, NULL, 'MUYANGO', 'NKENSE', 'ALEX', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 021', '2011-06-22', NULL, 'MUYANGO', 'BIHANGO', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8288', NULL, 'actif', 4),
(696, NULL, 'AMANI', 'FIKIRI', 'ADOLPHE', 'M', 'MINOVA', 'CONGOLAISE', 'KATOYI, DES TECHNICIENS, 93', '2015-12-18', NULL, 'CLAUDE SHAMAMBA', 'SIFA SHEKE', 'SUD-KIVU', 'MINOVA', NULL, NULL, NULL, '8290', NULL, 'actif', 4),
(697, NULL, 'BONGOMBE', 'LONGANGE', 'BENJAMIN', 'M', 'GOMA', 'CONGOLAISE', NULL, '2014-06-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '8294', NULL, 'actif', 4),
(698, NULL, 'AMANI', 'MUNGANGA', 'NATHANAEL', 'M', 'GOMA', 'CONGOLAISE', 'NDOSHO, KITO, 95', '2011-05-11', NULL, 'MUNGANGA BALUNGWE', 'FURAHA BIBISHE', 'SUD-KIVU', NULL, NULL, NULL, NULL, '8296', NULL, 'actif', 4),
(699, NULL, 'MURHULA', 'BARAGURA', 'CEDRICK', 'M', 'IDJWI', 'CONGOLAISE', 'KATOYI, BISHUSHA, 93', '2009-08-08', NULL, 'NIPO BARAGUHA', 'ESPERANCE CHIBALONZA', 'SUD-KIVU', 'IDJWI', NULL, NULL, NULL, '8304', NULL, 'actif', 4),
(700, NULL, 'AMISI', 'SINDANI', 'DAVID', 'M', 'GOMA', 'CONGOLAISE', 'KATOYI, BIKUKWE, 31', '2013-10-14', NULL, 'SINDANI MAOMBI', 'FELICITE MUMBERE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8309', NULL, 'actif', 4),
(701, NULL, 'WANI', 'MAPENDO', 'LEBON', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 012', '2010-10-29', NULL, 'MAPENDO MATARA', 'JEANINE BATUMIKE', 'SUD-KIVU', 'KABARE', NULL, NULL, NULL, '8321', NULL, 'actif', 4),
(702, NULL, 'BAHOMBA', 'NSUKUMBA', 'BONHEUR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, USHIRIKA, 093', '2011-04-17', NULL, 'BULONZA NYABUKERE', 'SAFARI MUHEKWA', 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8341', NULL, 'actif', 4),
(703, NULL, 'TAIFA', 'WAKABALA', 'ABOUBAKAR', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 032', '2007-07-27', NULL, NULL, NULL, 'SUD-KIVU', 'FIZI', NULL, NULL, NULL, '8343', NULL, 'actif', 4),
(704, NULL, 'OMBENI', 'KABENE', 'JACQUES', 'M', 'WALIKALE', 'CONGOLAISE', 'NGANGI, NTINGI, 831', '2010-05-26', NULL, NULL, NULL, 'NORD-KIVU', 'WALIKALE', NULL, NULL, NULL, '8346', NULL, 'actif', 4),
(705, NULL, 'BAHASANI', 'MUNDELE', 'JEAN-CLAUDE', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 021', '2010-07-20', NULL, 'MUDENLE ZEKOKO', 'TAMITUNDA GISELLE', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8350', NULL, 'actif', 4),
(706, NULL, 'MALASI', 'NKANGO', 'CLEMENT', 'M', 'GOMA', 'CONGOLAISE', 'NGANGI, NTINGI, 210', '2011-02-17', NULL, 'MALASI BUSHUANDA', 'MUHNIHIRE NDOOLE', 'KWANGO', 'KENGE', NULL, NULL, NULL, '8351', NULL, 'actif', 4),
(707, NULL, 'BAHATI', 'ABUSANI', 'SEBASTIEN', 'M', 'BUKAVU', 'CONGOLAISE', 'KATOYI, MUHAVURA, 02', '2020-06-11', NULL, 'BAHATI GERARD', 'ROSETTE ZIHALIRWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8352', NULL, 'actif', 4),
(708, NULL, 'AMPIRE', 'BIRIMWIRAGI', 'SAMUEL', 'M', 'BUKAVU', 'CONGOLAISE', 'NGANGI, NTINGI, 021', '2017-11-23', NULL, 'BIRIMWIRAGI JOSUE', 'HESHIMA LWABOSHI', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8356', NULL, 'actif', 4),
(709, NULL, 'AMANI', 'MUHIGIRWA', 'MOISE', 'M', 'GOMA', 'CONGOLAISE', 'NGANNGI, NTINGI, 012', '2012-02-21', NULL, 'MOISE GERVAIS', 'OLIVE WABIWA', 'SUD-KIVU', 'KALEHE', NULL, NULL, NULL, '8359', NULL, 'actif', 4),
(710, 'MWEN98801', 'MWENYEMALI', 'KALEGAMA', 'JANVIER', 'M', 'BUKAVU', 'CONGOLAISE', 'MURARA', '2022-01-05', NULL, 'JOH KIZO', 'BIBISHE KALULU', 'Kwango', 'Popokabaka', NULL, NULL, NULL, '', NULL, 'actif', 4);

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

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `attribution_cours_id`, `periode_id`, `type_evaluation`, `date_evaluation`, `ponderation_max`) VALUES
(5, 5, 3, 'Devoir', '2026-10-20', 10);

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
  `audience_roles` text DEFAULT NULL,
  `organisateur_id` int(11) DEFAULT NULL,
  `auteur_user_id` int(11) DEFAULT NULL,
  `annee_scolaire_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evenements`
--

INSERT INTO `evenements` (`id`, `ecole_id`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`, `categorie_id`, `audience_roles`, `organisateur_id`, `auteur_user_id`, `annee_scolaire_id`) VALUES
(1, 1, 'Conseil des Directeurs', 'Planification de la clôture des notes du premier semestre.', '2026-06-25 10:00:00', '2026-06-25 13:00:00', 'Bureau du Préfet', 1, NULL, 3, NULL, 1),
(2, 4, 'Récouvrement Forcé', 'le recouvrement des élèves', '2026-10-31 23:34:00', '2026-11-08 23:34:00', 'toute l\'école', 2, NULL, NULL, 165, 2),
(3, 4, 'Récouvrement Forcé', 'LA REUNION DES AGENTS', '2026-10-21 23:48:00', '2026-10-21 23:51:00', 'toute l\'école', 1, '[\"DA_école\",\"DD_école\",\"DE_école\",\"DP_école\",\"agent_ecole\",\"comptable_école\",\"ecole_admin\",\"parent_ecole\",\"promoteur_école\",\"préfet_école\",\"sec_école\"]', NULL, 165, 2),
(4, 4, 'proclamation', 'la proclamation de la première période', '2026-11-04 10:00:00', '2026-11-04 11:45:00', 'à l\'école', 4, '[\"DA_école\",\"DD_école\",\"DE_école\",\"DP_école\",\"agent_ecole\",\"comptable_école\",\"ecole_admin\",\"eleve_ecole\",\"enseignant_école\",\"parent_ecole\",\"promoteur_école\",\"préfet_école\",\"sec_école\"]', NULL, 165, 2);

-- --------------------------------------------------------

--
-- Table structure for table `exonerations`
--

CREATE TABLE `exonerations` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `frais_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(5) NOT NULL DEFAULT 'USD',
  `motif` varchar(500) NOT NULL,
  `statut` enum('En_attente','Approuvee','Refusee') NOT NULL DEFAULT 'En_attente',
  `commentaire` varchar(500) DEFAULT NULL,
  `demandeur_id` int(11) NOT NULL,
  `validateur_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decided_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exonerations`
--

INSERT INTO `exonerations` (`id`, `ecole_id`, `eleve_id`, `frais_id`, `montant`, `devise`, `motif`, `statut`, `commentaire`, `demandeur_id`, `validateur_id`, `created_at`, `decided_at`) VALUES
(1, 4, 325, 6, 70.00, 'USD', 'ELLE AETE LA PREMIERE DE L\'INSTITUT', 'Approuvee', NULL, 165, 170, '2026-10-20 21:23:16', '2026-10-22 10:32:55');

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
  `scope_id` int(11) DEFAULT NULL,
  `ecole_id` int(11) DEFAULT NULL,
  `encodage` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `frais_scolaires`
--

INSERT INTO `frais_scolaires` (`id`, `classe_id`, `type_frais`, `montant_total`, `annee_scolaire_id`, `devise`, `scope`, `scope_id`, `ecole_id`, `encodage`) VALUES
(1, NULL, 'Frais de l\'Etat Maternel', 24000.00, 2, 'CDF', 'section', 1, 4, 'FRAIS-2026-71383'),
(2, NULL, 'Frais technique Nutrition', 45.00, 2, 'USD', 'option', 7, 4, NULL),
(3, 4, 'Frais de l\'Etat', 30000.00, 1, 'CDF', 'class', NULL, 4, NULL),
(4, NULL, 'FRAIS DE CONSTRUCTION', 1.00, 2, 'USD', 'school', NULL, 4, NULL),
(5, NULL, 'Frais de l\'Etat Primaire', 24000.00, 2, 'CDF', 'section', 2, 4, 'FRAIS-2026-7131'),
(6, NULL, 'Frais Scolaires -secondaire', 135.00, 2, 'USD', 'section', 3, 4, NULL),
(7, NULL, 'Frais techniques Sociale', 30.00, 2, 'USD', 'option', 5, 4, NULL),
(8, NULL, 'Frais Scolaires Primaire', 70.00, 2, 'USD', 'section', 2, 4, NULL),
(9, NULL, 'Frais Scolaires Maternelle', 130.00, 2, 'USD', 'section', 1, 4, NULL),
(11, NULL, 'Frais Tricot', 15.00, 2, 'USD', 'school', NULL, 4, 'ART-SCOL-EXISTING-TRICOT'),
(12, 8, 'Frais de Contrôle des Dossiers', 15.00, 2, 'USD', 'class', 8, 4, NULL),
(14, NULL, 'Frais de l\'Etat Secondaire', 30000.00, 2, 'CDF', 'section', 3, 4, NULL),
(15, NULL, 'Cahier de Communication', 1.00, 2, 'USD', 'school', NULL, 4, 'ART-SCOL-7C35EA85A703'),
(16, NULL, 'Logo', 1.00, 2, 'USD', 'school', NULL, 4, 'ART-SCOL-E787B9AD5A10'),
(17, NULL, 'Cahier d\'écriture', 2.00, 2, 'USD', 'school', NULL, 4, 'ART-SCOL-B4FF62E868D3');

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
-- Table structure for table `horaires_cours`
--

CREATE TABLE `horaires_cours` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `attribution_cours_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `jour_id` tinyint(4) NOT NULL,
  `periode` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `horaires_cours`
--

INSERT INTO `horaires_cours` (`id`, `ecole_id`, `annee_scolaire_id`, `attribution_cours_id`, `classe_id`, `cours_id`, `agent_id`, `jour_id`, `periode`) VALUES
(11, 4, 2, 32, 10, 11, 8, 1, 1),
(12, 4, 2, 53, 10, 30, 8, 2, 1),
(13, 4, 2, 32, 10, 11, 8, 1, 2),
(14, 4, 2, 32, 10, 11, 8, 4, 2),
(15, 4, 2, 44, 10, 10, 19, 4, 1),
(16, 4, 2, 45, 18, 10, 19, 4, 1),
(17, 4, 2, 46, 46, 10, 19, 4, 1),
(18, 4, 2, 47, 59, 10, 19, 4, 1),
(19, 4, 2, 52, 25, 27, 8, 5, 2),
(20, 4, 2, 52, 25, 27, 8, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `indices_enseignants`
--

CREATE TABLE `indices_enseignants` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `indice` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `indices_enseignants`
--

INSERT INTO `indices_enseignants` (`id`, `ecole_id`, `annee_scolaire_id`, `agent_id`, `indice`) VALUES
(1, 4, 2, 7, 1),
(2, 4, 2, 8, 2),
(3, 4, 2, 9, 3),
(4, 4, 2, 17, 4),
(5, 4, 2, 19, 5);

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
(5, 6, 1, 1, '2026-07-04 23:30:18', NULL, NULL, 'En cours'),
(6, 10, 7, 2, '2026-08-26 20:46:28', NULL, NULL, 'En cours'),
(7, 11, 10, 2, '2026-10-16 19:01:54', NULL, NULL, 'En cours'),
(8, 12, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(9, 13, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(10, 14, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(11, 15, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(12, 16, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(13, 17, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(14, 18, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(15, 19, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(16, 20, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(17, 21, 22, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(18, 22, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(19, 23, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(20, 24, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(21, 25, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(22, 26, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(23, 27, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(24, 28, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(25, 29, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(26, 30, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(27, 31, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(28, 32, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(29, 33, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(30, 34, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(31, 35, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(32, 36, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(33, 37, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(34, 38, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(35, 39, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(36, 40, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(37, 41, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(38, 42, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(39, 43, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(40, 44, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(41, 45, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(42, 46, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(43, 47, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(44, 48, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(45, 49, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(46, 50, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(47, 51, 32, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(48, 52, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(49, 53, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(50, 54, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(51, 55, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(52, 56, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(53, 57, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(54, 58, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(55, 59, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(56, 60, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(57, 61, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(58, 62, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(59, 63, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(60, 64, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(61, 65, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(62, 66, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(63, 67, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(64, 68, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(65, 69, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(66, 70, 36, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(67, 71, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(68, 72, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(69, 73, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(70, 74, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(71, 75, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(72, 76, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(73, 77, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(74, 78, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(75, 79, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(76, 80, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(77, 81, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(78, 82, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(79, 83, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(80, 84, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(81, 85, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(82, 86, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(83, 87, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(84, 88, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(85, 89, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(86, 90, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(87, 91, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(88, 92, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(89, 93, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(90, 94, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(91, 95, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(92, 96, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(93, 97, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(94, 98, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(95, 99, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(96, 100, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(97, 101, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(98, 102, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(99, 103, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(100, 104, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(101, 105, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(102, 106, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(103, 107, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(104, 108, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(105, 109, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(106, 110, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(107, 111, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(108, 112, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(109, 113, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(110, 114, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(111, 115, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(112, 116, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(113, 117, 40, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(114, 118, 22, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(115, 119, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(116, 120, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(117, 121, 32, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(118, 122, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(119, 123, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(120, 124, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(121, 125, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(122, 126, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(123, 127, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(124, 128, 42, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(125, 129, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(126, 130, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(127, 131, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(128, 132, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(129, 133, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(130, 134, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(131, 135, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(132, 136, 40, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(133, 137, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(134, 138, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(135, 139, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(136, 140, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(137, 141, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(138, 142, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(139, 143, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(140, 144, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(141, 145, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(142, 146, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(143, 147, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(144, 148, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(145, 149, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(146, 150, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(147, 151, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(148, 152, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(149, 153, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(150, 154, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(151, 155, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(152, 156, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(153, 157, 44, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(154, 158, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(155, 159, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(156, 160, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(157, 161, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(158, 162, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(159, 163, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(160, 164, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(161, 165, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(162, 166, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(163, 167, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(164, 168, 45, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(165, 169, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(166, 170, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(167, 171, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(168, 172, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(169, 173, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(170, 174, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(171, 175, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(172, 176, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(173, 177, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(174, 178, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(175, 179, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(176, 180, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(177, 181, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(178, 182, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(179, 183, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(180, 184, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(181, 185, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(182, 186, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(183, 187, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(184, 188, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(185, 189, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(186, 190, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(187, 191, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(188, 192, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(189, 193, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(190, 194, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(191, 195, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(192, 196, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(193, 197, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(194, 198, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(195, 199, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(196, 200, 36, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(197, 201, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(198, 202, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(199, 203, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(200, 204, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(201, 205, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(202, 206, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(203, 207, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(204, 208, 47, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(205, 209, 48, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(206, 210, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(207, 211, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(208, 212, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(209, 213, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(210, 214, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(211, 215, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(212, 216, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(213, 217, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(214, 218, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(215, 219, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(216, 220, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(217, 221, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(218, 222, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(219, 223, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(220, 224, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(221, 225, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(222, 226, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(223, 227, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(224, 228, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(225, 229, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(226, 230, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(227, 231, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(228, 232, 32, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(229, 233, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(230, 234, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(231, 235, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(232, 236, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(233, 237, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(234, 238, 44, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(235, 239, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(236, 240, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(237, 241, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(238, 242, 45, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(239, 243, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(240, 244, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(241, 245, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(242, 246, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(243, 247, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(244, 248, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(245, 249, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(246, 250, 42, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(247, 251, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(248, 252, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(249, 253, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(250, 254, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(251, 255, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(252, 256, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(253, 257, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(254, 258, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(255, 259, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(256, 260, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(257, 261, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(258, 262, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(259, 263, 49, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(260, 264, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(261, 265, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(262, 266, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(263, 267, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(264, 268, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(265, 269, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(266, 270, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(267, 271, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(268, 272, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(269, 273, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(270, 274, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(271, 275, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(272, 276, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(273, 277, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(274, 278, 50, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(275, 279, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(276, 280, 47, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(277, 281, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(278, 282, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(279, 283, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(280, 284, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(281, 285, 50, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(282, 286, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(283, 287, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(284, 288, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(285, 289, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(286, 290, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(287, 291, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(288, 292, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(289, 293, 51, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(290, 294, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(291, 295, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(292, 296, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(293, 297, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(294, 298, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(295, 299, 52, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(296, 300, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(297, 301, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(298, 302, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(299, 303, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(300, 304, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(301, 305, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(302, 306, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(303, 307, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(304, 308, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(305, 309, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(306, 310, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(307, 311, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(308, 312, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(309, 313, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(310, 314, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(311, 315, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(312, 316, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(313, 317, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(314, 318, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(315, 319, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(316, 320, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(317, 321, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(318, 322, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(319, 323, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(320, 324, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(321, 325, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(322, 326, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(323, 327, 49, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(324, 328, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(325, 329, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(326, 330, 53, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(327, 331, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(328, 332, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(329, 333, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(330, 334, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(331, 335, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(332, 336, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(333, 337, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(334, 338, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(335, 339, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(336, 340, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(337, 341, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(338, 342, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(339, 343, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(340, 344, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(341, 345, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(342, 346, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(343, 347, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(344, 348, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(345, 349, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(346, 350, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(347, 351, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(348, 352, 54, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(349, 353, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(350, 354, 55, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(351, 355, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(352, 356, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(353, 357, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(354, 358, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(355, 359, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(356, 360, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(357, 361, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(358, 362, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(359, 363, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(360, 364, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(361, 365, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(362, 366, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(363, 367, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(364, 368, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(365, 369, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(366, 370, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(367, 371, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(368, 372, 55, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(369, 373, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(370, 374, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(371, 375, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(372, 376, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(373, 377, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(374, 378, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(375, 379, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(376, 380, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(377, 381, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(378, 382, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(379, 383, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(380, 384, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(381, 385, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(382, 386, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(383, 387, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(384, 388, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(385, 389, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(386, 390, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(387, 391, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(388, 392, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(389, 393, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(390, 394, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(391, 395, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(392, 396, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(393, 397, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(394, 398, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(395, 399, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(396, 400, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(397, 401, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(398, 402, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(399, 403, 50, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(400, 404, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(401, 405, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(402, 406, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(403, 407, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(404, 408, 50, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(405, 409, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(406, 410, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(407, 411, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(408, 412, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(409, 413, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(410, 414, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(411, 415, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(412, 416, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(413, 417, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(414, 418, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(415, 419, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(416, 420, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(417, 421, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(418, 422, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(419, 423, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(420, 424, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(421, 425, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(422, 426, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(423, 427, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(424, 428, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(425, 429, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(426, 430, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(427, 431, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(428, 432, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(429, 433, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(430, 434, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(431, 435, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(432, 436, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(433, 437, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(434, 438, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(435, 439, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(436, 440, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(437, 441, 32, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(438, 442, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(439, 443, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(440, 444, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(441, 445, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(442, 446, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(443, 447, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(444, 448, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(445, 449, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(446, 450, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(447, 451, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(448, 452, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(449, 453, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(450, 454, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(451, 455, 56, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(452, 456, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(453, 457, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(454, 458, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(455, 459, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(456, 460, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(457, 461, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(458, 462, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(459, 463, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(460, 464, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(461, 465, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(462, 466, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(463, 467, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(464, 468, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(465, 469, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(466, 470, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(467, 471, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(468, 472, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(469, 473, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(470, 474, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(471, 475, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(472, 476, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(473, 477, 56, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(474, 478, 56, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(475, 479, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(476, 480, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(477, 481, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(478, 482, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(479, 483, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(480, 484, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(481, 485, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(482, 486, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(483, 487, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(484, 488, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(485, 489, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(486, 490, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(487, 491, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(488, 492, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(489, 493, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(490, 494, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(491, 495, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(492, 496, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(493, 497, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(494, 498, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(495, 499, 57, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(496, 500, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(497, 501, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(498, 502, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(499, 503, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(500, 504, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(501, 505, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(502, 506, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(503, 507, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(504, 508, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(505, 509, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(506, 510, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(507, 511, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(508, 512, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(509, 513, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(510, 514, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(511, 515, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(512, 516, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(513, 517, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(514, 518, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(515, 519, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(516, 520, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(517, 521, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(518, 522, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(519, 523, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(520, 524, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(521, 525, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(522, 526, 58, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(523, 527, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(524, 528, 42, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(525, 529, 37, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(526, 530, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(527, 531, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(528, 532, 40, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(529, 533, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(530, 534, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(531, 535, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(532, 536, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(533, 537, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(534, 538, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(535, 539, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(536, 540, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(537, 541, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(538, 542, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(539, 543, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(540, 544, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(541, 545, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(542, 546, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(543, 547, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(544, 548, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(545, 549, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(546, 550, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(547, 551, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(548, 552, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(549, 553, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(550, 554, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(551, 555, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(552, 556, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(553, 557, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(554, 558, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(555, 559, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(556, 560, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(557, 561, 47, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(558, 562, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(559, 563, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(560, 564, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(561, 565, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(562, 566, 52, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(563, 567, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(564, 568, 34, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(565, 569, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(566, 570, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(567, 571, 44, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(568, 572, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(569, 573, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(570, 574, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(571, 575, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(572, 576, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(573, 577, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(574, 578, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(575, 579, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(576, 580, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(577, 581, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(578, 582, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(579, 583, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(580, 584, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(581, 585, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(582, 586, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(583, 587, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(584, 588, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(585, 589, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(586, 590, 50, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(587, 591, 33, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(588, 592, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(589, 593, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(590, 594, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(591, 595, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(592, 596, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(593, 597, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(594, 598, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(595, 599, 16, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(596, 600, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(597, 601, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(598, 602, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(599, 603, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(600, 604, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(601, 605, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(602, 606, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(603, 607, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(604, 608, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(605, 609, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(606, 610, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(607, 611, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(608, 612, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(609, 613, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(610, 614, 38, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(611, 615, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(612, 616, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(613, 617, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(614, 618, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(615, 619, 56, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(616, 620, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(617, 621, 35, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(618, 622, 47, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(619, 623, 59, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(620, 624, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(621, 625, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(622, 626, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(623, 627, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(624, 628, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(625, 629, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(626, 630, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(627, 631, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(628, 632, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(629, 633, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(630, 634, 30, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(631, 635, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(632, 636, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(633, 637, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(634, 638, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(635, 639, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(636, 640, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(637, 641, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(638, 642, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(639, 643, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(640, 644, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(641, 645, 39, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(642, 646, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(643, 647, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(644, 648, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(645, 649, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(646, 650, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(647, 651, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(648, 652, 47, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(649, 653, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(650, 654, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(651, 655, 40, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(652, 656, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(653, 657, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(654, 658, 25, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(655, 659, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(656, 660, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(657, 661, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(658, 662, 60, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(659, 663, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(660, 664, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(661, 665, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(662, 666, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(663, 667, 59, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(664, 668, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(665, 669, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(666, 670, 43, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(667, 671, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(668, 672, 10, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(669, 673, 59, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(670, 674, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(671, 675, 24, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(672, 676, 21, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(673, 677, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(674, 678, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(675, 679, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(676, 680, 19, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(677, 681, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(678, 682, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(679, 683, 31, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(680, 684, 17, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(681, 685, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(682, 686, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(683, 687, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(684, 688, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(685, 689, 23, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(686, 690, 46, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(687, 691, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(688, 692, 28, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(689, 693, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(690, 694, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(691, 695, 47, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(692, 696, 20, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(693, 697, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(694, 698, 40, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(695, 699, 61, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(696, 700, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(697, 701, 40, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(698, 702, 18, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(699, 703, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(700, 704, 7, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(701, 705, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(702, 706, 41, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(703, 707, 26, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(704, 708, 29, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(705, 709, 27, 2, '2026-10-20 17:42:18', NULL, NULL, 'En cours'),
(706, 710, 26, 2, '2026-10-21 21:31:40', NULL, NULL, 'En cours');

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
-- Table structure for table `jours_libres_enseignants`
--

CREATE TABLE `jours_libres_enseignants` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `jour_id` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jours_libres_enseignants`
--

INSERT INTO `jours_libres_enseignants` (`id`, `ecole_id`, `annee_scolaire_id`, `agent_id`, `jour_id`) VALUES
(1, 4, 2, 8, 6);

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
-- Table structure for table `livraisons_objets_scolaires`
--

CREATE TABLE `livraisons_objets_scolaires` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `frais_id` int(11) NOT NULL,
  `livre_par_user_id` int(11) DEFAULT NULL,
  `date_livraison` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages_utilisateurs`
--

CREATE TABLE `messages_utilisateurs` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type` varchar(40) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages_utilisateurs`
--

INSERT INTO `messages_utilisateurs` (`id`, `utilisateur_id`, `type`, `reference_id`, `title`, `body`, `is_read`, `created_at`) VALUES
(1, 165, 'derogation_expiree', 1, 'Dérogation arrivée à échéance', 'La dérogation accordée à AGANZE NTAKOBAJIRA a pris fin le 25/10/2026.', 1, '2026-10-29 09:27:04'),
(2, 174, 'derogation_expiree', 1, 'Dérogation arrivée à échéance', 'La dérogation accordée à AGANZE NTAKOBAJIRA a pris fin le 25/10/2026.', 0, '2026-10-29 09:27:04'),
(140, 113, 'communique', 3, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 0, '2026-10-29 14:24:23'),
(141, 156, 'communique', 3, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 0, '2026-10-29 14:24:23'),
(142, 163, 'communique', 3, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 0, '2026-10-29 14:24:23'),
(143, 165, 'communique', 3, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 1, '2026-10-29 14:24:23'),
(144, 166, 'communique', 3, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 0, '2026-10-29 14:24:23'),
(145, 174, 'communique', 3, 'Récouvrement Forcé', 'Chers parents Bonjour, \r\nil est porté à votre connaissance que nous procédons au recouvrement des frais techniques à partir de ce lundi.\r\nfranche collaboration', 0, '2026-10-29 14:24:23'),
(163, 113, 'communique', 4, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 0, '2026-10-29 14:39:35'),
(164, 156, 'communique', 4, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 0, '2026-10-29 14:39:35'),
(165, 163, 'communique', 4, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 0, '2026-10-29 14:39:35'),
(166, 165, 'communique', 4, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 1, '2026-10-29 14:39:35'),
(167, 166, 'communique', 4, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 0, '2026-10-29 14:39:35'),
(168, 174, 'communique', 4, 'Récouvrement Forcé', 'il est possible de passer à l\'ouverture des notifications', 0, '2026-10-29 14:39:35'),
(172, 113, 'communique', 5, 'Récouvrement Forcé', 'oui', 0, '2026-10-29 14:41:06'),
(173, 156, 'communique', 5, 'Récouvrement Forcé', 'oui', 0, '2026-10-29 14:41:06'),
(174, 163, 'communique', 5, 'Récouvrement Forcé', 'oui', 0, '2026-10-29 14:41:06'),
(175, 165, 'communique', 5, 'Récouvrement Forcé', 'oui', 0, '2026-10-29 14:41:06'),
(176, 166, 'communique', 5, 'Récouvrement Forcé', 'oui', 0, '2026-10-29 14:41:06'),
(177, 174, 'communique', 5, 'Récouvrement Forcé', 'oui', 0, '2026-10-29 14:41:06'),
(187, 163, 'paiement', 1463, 'Paiement reçu pour NKUBA KASKOTI JOHN', 'Un paiement de 0,80 USD a été enregistré pour NKUBA KASKOTI JOHN. Frais : Logo - 1.00 USD.', 1, '2026-10-29 15:09:27');

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

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `evaluation_id`, `eleve_id`, `note_obtenue`) VALUES
(5, 5, 10, 6.0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications_evenements`
--

CREATE TABLE `notifications_evenements` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `evenement_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications_evenements`
--

INSERT INTO `notifications_evenements` (`id`, `utilisateur_id`, `evenement_id`, `is_read`, `created_at`) VALUES
(1, 165, 3, 1, '2026-10-21 21:50:09'),
(13, 170, 3, 0, '2026-10-21 21:55:56'),
(185, 169, 3, 0, '2026-10-22 14:05:49');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) DEFAULT NULL,
  `nom_option` varchar(100) NOT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `ecole_id`, `nom_option`, `section_id`) VALUES
(2, 1, 'Commerciale et Gestion', 3),
(4, 1, 'Vétérinaire', 3),
(5, 4, 'Technique Sociale', 3),
(6, 4, 'Education de base', 3),
(7, 4, 'Nutrition Générale', NULL),
(8, 1, 'Mécanique Générale', NULL),
(9, 1, 'Construction', NULL),
(10, 1, 'Eléctricité Générale', NULL),
(11, 1, 'Vétérinaire', NULL),
(12, 1, 'Vétérinaire', NULL),
(13, 4, 'Commerciale et Gestion', 3),
(14, 4, 'Vétérinaire', 3),
(15, 3, 'Mécanique Générale', NULL),
(16, 4, 'Mécanique Générale', 3),
(17, 3, 'Construction', NULL),
(18, 4, 'Construction', 3),
(19, 3, 'Eléctricité Générale', NULL),
(20, 4, 'Eléctricité Générale', 3),
(21, 3, 'Vétérinaire', NULL),
(23, 3, 'Vétérinaire', NULL),
(41, NULL, 'Education de base', 2);

-- --------------------------------------------------------

--
-- Table structure for table `paiements_autorisations`
--

CREATE TABLE `paiements_autorisations` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `frais_id` int(11) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `granted_by_user_id` int(11) DEFAULT NULL,
  `granted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paiements_autorisations`
--

INSERT INTO `paiements_autorisations` (`id`, `ecole_id`, `role`, `frais_id`, `actif`, `granted_by_user_id`, `granted_at`) VALUES
(4, 4, 'sec_école', 4, 1, 165, '2026-10-15 07:03:12'),
(5, 4, 'préfet_école', 4, 1, 165, '2026-10-15 07:03:14'),
(6, 4, 'DE_école', 4, 1, 165, '2026-10-15 07:03:15'),
(7, 4, 'DD_école', 4, 1, 165, '2026-10-15 07:03:16'),
(8, 4, 'DP_école', 4, 1, 165, '2026-10-15 07:03:17'),
(9, 4, 'DA_école', 4, 1, 165, '2026-10-15 07:03:20'),
(10, 4, 'DE_école', 2, 1, 165, '2026-10-15 07:03:32'),
(11, 4, 'DE_école', 7, 1, 165, '2026-10-15 07:03:38'),
(12, 4, 'sec_école', 11, 1, 165, '2026-10-15 07:03:45');

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
(5, 9, 2, 23.00, '2026-07-05 19:43:21'),
(6, 10, 14, 1.00, '2026-08-26 23:09:19'),
(7, 10, 14, 100.00, '2026-08-26 23:21:24'),
(8, 10, 6, 24.00, '2026-08-30 07:10:37'),
(9, 10, 11, 10.00, '2026-09-21 17:12:22'),
(10, 10, 6, 10.00, '2026-10-16 10:28:20'),
(11, 10, 11, 5.00, '2026-10-16 12:24:58'),
(12, 11, 6, 14.50, '2026-10-16 19:34:58'),
(13, 11, 11, 10.00, '2026-10-16 19:45:40'),
(14, 10, 14, 9.00, '2026-10-16 19:47:06'),
(15, 10, 6, 10.00, '2026-10-16 19:56:31'),
(16, 11, 6, 12.00, '2026-10-16 19:57:40'),
(17, 9, 11, 4.00, '2026-10-16 20:06:34'),
(18, 9, 11, 7.00, '2026-10-16 20:16:21'),
(19, 9, 11, 1.00, '2026-10-18 10:23:23'),
(20, 9, 2, 1.00, '2026-10-18 10:32:12'),
(21, 9, 11, 1.00, '2026-10-18 10:35:17'),
(22, 9, 2, 1.00, '2026-10-18 11:55:20'),
(23, 10, 6, 1.00, '2026-10-18 12:15:11'),
(24, 9, 2, 2.00, '2026-10-18 12:18:16'),
(25, 616, 6, 10.00, '2026-10-20 19:07:22'),
(26, 425, 6, 15.00, '2026-10-20 21:29:23'),
(27, 425, 15, 1.00, '2026-10-29 12:19:53'),
(28, 10, 16, 0.80, '2026-10-29 14:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `parametres_horaires`
--

CREATE TABLE `parametres_horaires` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `duree_heure_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 50,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parametres_horaires`
--

INSERT INTO `parametres_horaires` (`id`, `ecole_id`, `section_id`, `duree_heure_minutes`, `updated_at`) VALUES
(1, 4, 3, 50, '2026-10-21 23:26:54');

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
(5, 4, 'BAHATI', '0823941089', 'codekizo8@gmail.com', '$2y$10$/U/I7FQ.2kF4qjElU.X2L.PLmS4u.6Rm33b7eoEEZDKWKnmh43MTG'),
(6, 4, 'AGIZO KASKOTI', '0977342942', 'johkizo19@gmail.com', '$2y$10$6JPYhkWbOm43kWFMDtjZ3.V9CBbGOYDK6ey5GRBSKUY0ybQ1MGQ0i'),
(9, 4, 'NICOLE KAPUTU', '0902283246', 'csemmanuel@gmail.com', '$2y$10$ytw6oikNumqEYXhPTl1TH.z4zQFc4.NFf8mCIVj5axtspbijL9dKW');

-- --------------------------------------------------------

--
-- Table structure for table `parent_child_requests`
--

CREATE TABLE `parent_child_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `remote_address` varchar(45) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(2, '2ème Période', 1, 0),
(3, 'Période de test', 2, 0);

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

--
-- Dumping data for table `plans_abonnement`
--

INSERT INTO `plans_abonnement` (`id`, `nom_plan`, `duree_mois`, `prix`, `description`) VALUES
(5, 'Plan Mensuel', 1, 50.00, 'Plan de durée d\'un mois d\'utilisation'),
(6, 'Plan Trimestriel', 3, 80.00, 'Plan de durée de Trois mois d\'utilisation'),
(7, 'Plan Annuel', 10, 1000.00, NULL);

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
(2, 2, '2026-06-16', 'Retard'),
(3, 10, '2026-10-20', 'Absent Non Justifié');

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
-- Table structure for table `programmes_cours`
--

CREATE TABLE `programmes_cours` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `nature_cours` enum('commun','option') NOT NULL,
  `volume_horaire_hebdo` tinyint(3) UNSIGNED NOT NULL,
  `agent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programmes_cours`
--

INSERT INTO `programmes_cours` (`id`, `ecole_id`, `annee_scolaire_id`, `section_id`, `cours_id`, `nature_cours`, `volume_horaire_hebdo`, `agent_id`) VALUES
(6, 4, 2, 3, 10, 'commun', 2, 19),
(7, 4, 2, 3, 11, 'option', 4, 8),
(18, 4, 2, 3, 22, 'commun', 2, 17),
(21, 4, 2, 3, 25, 'option', 2, 19),
(22, 4, 2, 3, 26, 'option', 2, 8),
(23, 4, 2, 3, 27, 'option', 2, 8),
(24, 4, 2, 3, 28, 'option', 6, 8),
(25, 4, 2, 3, 29, 'option', 4, 8),
(26, 4, 2, 3, 30, 'option', 6, 8);

-- --------------------------------------------------------

--
-- Table structure for table `programmes_cours_classes`
--

CREATE TABLE `programmes_cours_classes` (
  `programme_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programmes_cours_classes`
--

INSERT INTO `programmes_cours_classes` (`programme_id`, `classe_id`) VALUES
(6, 10),
(6, 18),
(6, 46),
(6, 59),
(7, 10),
(18, 10),
(18, 18),
(18, 46),
(18, 59),
(21, 40),
(22, 10),
(23, 25),
(24, 10),
(25, 10),
(26, 10);

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
-- Table structure for table `school_messages`
--

CREATE TABLE `school_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `thread_id` bigint(20) UNSIGNED NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_messages`
--

INSERT INTO `school_messages` (`id`, `thread_id`, `sender_user_id`, `recipient_user_id`, `body`, `created_at`, `read_at`) VALUES
(1, 1, 163, 165, 'Cher compte veuillez me permettre que l\'enfant entre aujourd\'hui', '2026-10-29 15:22:47', '2026-10-29 15:23:15'),
(2, 1, 165, 163, 'cher parent nous sommes deja en retard pour le paiement des frais de l\'état', '2026-10-29 15:23:43', '2026-10-29 15:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `school_message_threads`
--

CREATE TABLE `school_message_threads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `parent_user_id` int(11) NOT NULL,
  `staff_user_id` int(11) NOT NULL,
  `subject` varchar(160) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_message_threads`
--

INSERT INTO `school_message_threads` (`id`, `school_id`, `student_id`, `parent_user_id`, `staff_user_id`, `subject`, `created_at`, `updated_at`) VALUES
(1, 4, 10, 163, 165, 'Demande de dérogation', '2026-10-29 15:22:47', '2026-10-29 15:23:43');

-- --------------------------------------------------------

--
-- Table structure for table `school_message_typing`
--

CREATE TABLE `school_message_typing` (
  `thread_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `typing_until` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `seuils_recouvrement`
--

CREATE TABLE `seuils_recouvrement` (
  `id` int(11) NOT NULL,
  `ecole_id` int(11) NOT NULL,
  `devise` varchar(5) NOT NULL,
  `montant` decimal(12,2) NOT NULL DEFAULT 0.00,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seuils_recouvrement`
--

INSERT INTO `seuils_recouvrement` (`id`, `ecole_id`, `devise`, `montant`, `updated_by`, `updated_at`) VALUES
(1, 4, 'USD', 20.00, 165, '2026-10-22 12:09:57'),
(2, 4, 'CDF', 20000.00, 165, '2026-10-20 20:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `super_admin_activities`
--

CREATE TABLE `super_admin_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `target_type` varchar(30) NOT NULL,
  `target_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` varchar(1000) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admin_activities`
--

INSERT INTO `super_admin_activities` (`id`, `actor_user_id`, `action`, `target_type`, `target_id`, `title`, `details`, `created_at`) VALUES
(3, 169, 'school_admin_access_changed', 'user', 164, 'JOHN NKUBA', 'Accès modifiés : Eleve école → Agent ; statut Actif → Actif ; école Complexe Scolaire Emmanuel 1 → Complexe Scolaire Emmanuel 1.', '2026-10-29 12:38:23'),
(4, 143, 'school_notification_access_changed', 'school', 4, 'Complexe Scolaire Emmanuel 1', 'Accès aux notifications mis à jour : SMS désactivé, WhatsApp activé.', '2026-10-29 12:43:36'),
(5, 143, 'school_access_changed', 'school', 4, 'Complexe Scolaire Emmanuel 1', 'Accès de l’école modifié : Actif → Suspendu.', '2026-10-29 14:06:55'),
(6, 143, 'school_access_changed', 'school', 4, 'Complexe Scolaire Emmanuel 1', 'Accès de l’école modifié : Suspendu → Actif.', '2026-10-29 14:07:33'),
(7, 169, 'school_admin_access_changed', 'user', 163, 'AGIZO KASKOTI', 'Accès modifiés : Parent école → Parent ; statut Actif → Actif ; école Complexe Scolaire Emmanuel 1 → Complexe Scolaire Emmanuel 1.', '2026-10-29 15:03:53');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(150) NOT NULL,
  `identifiant` varchar(100) NOT NULL COMMENT 'Email ou Téléphone pour la connexion',
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('super_admin','ecole_admin','prefet_école','DE_école','DD_école','DP_école','DA_école','comptable_école','sec_école','promoteur_école','enseignant_école','agent_école','eleve_école','parent_école') NOT NULL,
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
(3, 'KIZOMBO LWALABA Joseph', 'marc.balume@outlook.com', '$2y$10$Q1qdbvmfw...', 'prefet_école', 4, 3, 'Actif', '2026-06-27 22:08:32', NULL, 3),
(100, 'Parent Test', 'parent.test@example.com', '<HASH_PARENT>', 'parent_école', 1, 1, 'Actif', '2026-06-27 23:06:48', NULL, NULL),
(102, 'Élève Test', 'eleve1@example.com', '<HASH_ELEVE>', 'eleve_école', 1, 1, 'Actif', '2026-06-27 23:06:48', NULL, NULL),
(106, 'École Test', 'ecole1', '$2y$10$.GPiu8iKdwNgPSfgxQD7vun17cpOX8p8qVAEOCSIIyny0lkWfrd5O', 'prefet_école', 4, NULL, 'Actif', '2026-07-01 22:48:00', NULL, 3),
(107, 'Admin Test', 'admin1', '$2y$10$OFUC2olSBwR2BbWp4ydOn.wgskYJvtrf6IqdfCUmu6206VWNUAbG.', 'super_admin', NULL, NULL, 'Actif', '2026-07-01 22:48:00', NULL, NULL),
(112, 'Marie Kavira', 'mariekav@gmail.com', '$2y$10$83gZl3Nan4NZHUQtbFCkBurbmqkX6CF9aL0tTPqdir3dxQOz.QDeW', 'parent_école', 1, 2, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(113, 'JOH KIZO', 'adminkizo@gmail.com', '$2y$10$SjUUuKlXcwUVehJJAr3xe.QepJGjg9AyGv9NlN3G.moFa.54e91qC', 'parent_école', 4, 4, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(114, 'Arsène Paluku', 'eleve2@school.local', '$2y$10$y7iecyfnenW334rEdFCKsuOusJpGSdhLM5qKhyiAx8Mwk6mFG2sxC', 'eleve_école', 1, 2, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(115, 'Clarisse Masika', 'JSDJSK21', '$2y$10$OTIXV7Uk9MlweqkfBpH0a.tTaSEaQ.v4IgLGdPQTK0Mp1Dxj3Iyo2', 'eleve_école', 1, 3, 'Actif', '2026-07-03 00:36:07', NULL, NULL),
(143, 'Super Admin Test', 'super_admin@test.local', '$2y$10$7ZBugOkKdrFPui8AMPjXHet4sNu8WZs1gH7ojNuf16..67ZbPMnfu', 'super_admin', NULL, NULL, 'Actif', '2026-07-27 18:57:27', NULL, NULL),
(144, 'Jules MWENYEMALI', 'comptable_test1@example.com', '$2y$10$mcM3Te467D/OdT8nohvnNuyNHzuzKO08h0L3JmmHVN/M9/6tl5XmW', 'prefet_école', 4, NULL, 'Actif', '2026-07-27 18:57:27', NULL, 3),
(156, 'BAHATI', 'codekizo8@gmail.com', '$2y$10$EvotduSqp8rJDZZv6S9DuuhoaPcusUkzjlQ2cfVMUor7R9dkqxOFy', 'parent_école', 4, 5, 'Actif', '2026-07-27 19:06:47', NULL, NULL),
(157, 'ANGE AMINA', 'AMINA-20260703085920-252854', '$2y$10$rsTaXJtDmSUJoDyudA0laOvavI.UOYAdOgmnknQ6ytmrKXg69U8ge', 'eleve_école', 1, 4, 'Actif', '2026-07-27 19:06:47', NULL, NULL),
(158, 'PRUNELLE AGISHA', 'AGISHA-20260703122431-213539', '$2y$10$F/GN/a500kZXK8LkJiPLRu.8.ishja3hMeyjpXj6mjdYNc2akUaXm', 'eleve_école', 1, 5, 'Actif', '2026-07-27 19:06:47', NULL, NULL),
(159, 'angle Bahati', 'B-20260705013018-a99f18', '$2y$10$2W0x0DihtVQVSX7Msg6epOkhXQZuEOIS6mK5FDE0QrVQD1okucSce', 'eleve_école', 1, 6, 'Actif', '2026-07-27 19:06:47', NULL, NULL),
(162, 'MIRIAM ANGELA', 'ANGELA-20260705123651-584c73', '$2y$10$SpICOwv4w4OuBBQgDhL.pe8jwwDDjGtNLuoN/RB/hDggB7Bbr.p26', 'eleve_école', 4, 9, 'Actif', '2026-07-27 19:06:47', NULL, NULL),
(163, 'AGIZO KASKOTI', 'johkizo19@gmail.com', '$2y$10$SWL50DeUSoHk9FA62ExUwOEJBJqEGUQAjtLqrNbMap0EQfRkGXKDW', 'parent_école', 4, 6, 'Actif', '2026-08-26 20:46:28', NULL, NULL),
(164, 'JOHN NKUBA', 'NKUB00245', '$2y$10$sWjIiXyFQZ4dbLFY7415mO2R.K6J3X3wVsAlZIvfxFULEkq222pZi', 'agent_école', 4, 10, 'Actif', '2026-08-26 20:46:28', NULL, NULL),
(165, 'JOSUE MAKUTANO', 'comptable_emmanuel1', '$2y$10$zYm..DbJa3U27GoUTx9gl.q28H5Uf3iuNlH.b2N9z6oCW2Ni6hh4K', 'comptable_école', 4, 3, 'Actif', '2026-08-30 09:01:24', NULL, NULL),
(166, 'NICOLE KAPUTU', 'csemmanuel@gmail.com', '$2y$10$nG0x3XTBKgLBjyqxwdtk8u6QRkR.sGO8Rh3iklorEW2ucHneJ.2lu', 'parent_école', 4, 9, 'Actif', '2026-10-16 19:01:54', NULL, NULL),
(167, 'JOHN KASEREKA', 'KASE93856', '$2y$10$/6UVZH1PWglj1RgvcQh2buMxWVdZGPdPZhBuMBxeVyESIqmW3hzv2', 'eleve_école', 4, 11, 'Actif', '2026-10-16 19:01:54', NULL, NULL),
(168, 'Joseph KIZOMBO', 'sec_ecole@test.local', '$2y$10$x5vXsluhgyeRwnUIRp4I5OqghaEdp9.5cX0fiW1CTIkJqMmohRh2G', 'sec_école', 4, NULL, 'Actif', '2026-10-16 19:13:47', NULL, NULL),
(169, 'Admin École Test', 'admin.test@kc-sma.local', '$2y$10$xG6si5Q3t/CkGWoDp5DEveXJF5n6BasuHgBWPK5LglBp36sWz0KOW', 'ecole_admin', 4, NULL, 'Actif', '2026-10-20 13:13:22', NULL, NULL),
(170, 'Directeur des études Test', 'de.test@kc-sma.local', '$2y$10$Hqs/LnwJl3WiozmAQNVvreRrvwWBJkYRSDQFkuA5Kck6XY/OSycO2', 'DE_école', 4, NULL, 'Actif', '2026-10-20 13:13:22', NULL, NULL),
(171, 'Directeur département Test', 'dd.test@kc-sma.local', '$2y$10$4d6SSVfEciBfCDgpaw9J4.8iMKBAsViCEz65QO9kWOmAOMZgwXYva', 'DD_école', 4, NULL, 'Actif', '2026-10-20 13:13:22', NULL, NULL),
(172, 'Directeur principal Test', 'dp.test@kc-sma.local', '$2y$10$GrREhO1n3wf72ggQpWywreJwzJhODkv7Y/J4TVpLzMgAkFM3.ay86', 'DP_école', 4, NULL, 'Actif', '2026-10-20 13:13:22', NULL, NULL),
(173, 'Directeur adjoint Test', 'da.test@kc-sma.local', '$2y$10$ZSUcWEGxBamAfE.vQ/YPz.a2xPFjRkuXquWj7KUBIJwHH1FydIaGa', 'DA_école', 4, NULL, 'Actif', '2026-10-20 13:13:22', NULL, NULL),
(174, 'Comptable Test', 'comptable.test@kc-sma.local', '$2y$10$njJwXh3pyymtQa9bTzvnleYwKKNAQah4IPkkzKUMqJld6KwMzkFG6', 'comptable_école', 4, NULL, 'Actif', '2026-10-20 13:13:23', NULL, NULL),
(175, 'Promoteur Test', 'promoteur.test@kc-sma.local', '$2y$10$2Ravc4YE4htEfy2xp0Qwaue/h7UdOi2xI2yNtJVqjbcFW4YdMlaty', 'promoteur_école', 4, NULL, 'Actif', '2026-10-20 13:13:23', NULL, NULL),
(176, 'Enseignant Test', 'enseignant.test@kc-sma.local', '$2y$10$tqvIxYR7Idkcv/S9PG7EIO0RGqiNG2mWL9tBO114/cJcepdBJ1a22', 'enseignant_école', 4, 7, 'Actif', '2026-10-20 13:13:23', NULL, NULL),
(177, 'JANVIER MWENYEMALI', 'MWEN98801', '$2y$10$/hpR9ec7Vrr9fC7j1DixzuUBlkScCALPzSqUfLPvOFcC7iaKpTdt2', 'eleve_école', 4, 710, 'Actif', '2026-10-21 21:31:40', NULL, NULL),
(178, 'Test Enseignant 01 Aline', 'enseignant.secondaire.test01@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 8, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(179, 'Test Enseignant 02 B?atrice', 'enseignant.secondaire.test02@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 9, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(180, 'Test Enseignant 03 C?dric', 'enseignant.secondaire.test03@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 10, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(181, 'Test Enseignant 04 Diane', 'enseignant.secondaire.test04@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 11, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(182, 'Test Enseignant 05 ?ric', 'enseignant.secondaire.test05@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 12, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(183, 'Test Enseignant 06 F?licit?', 'enseignant.secondaire.test06@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 13, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(184, 'Test Enseignant 07 G?rard', 'enseignant.secondaire.test07@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 14, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(185, 'Test Enseignant 08 H?l?ne', 'enseignant.secondaire.test08@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 15, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(186, 'Test Enseignant 09 Ir?ne', 'enseignant.secondaire.test09@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 16, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(187, 'Test Enseignant 10 Jean', 'enseignant.secondaire.test10@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 17, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(188, 'Test Enseignant 11 K?vin', 'enseignant.secondaire.test11@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 18, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(189, 'Test Enseignant 12 Laure', 'enseignant.secondaire.test12@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 19, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(190, 'Test Enseignant 13 Michel', 'enseignant.secondaire.test13@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 20, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(191, 'Test Enseignant 14 Nadine', 'enseignant.secondaire.test14@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 21, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(192, 'Test Enseignant 15 Olivier', 'enseignant.secondaire.test15@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 22, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(193, 'Test Enseignant 16 Pauline', 'enseignant.secondaire.test16@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 23, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(194, 'Test Enseignant 17 Quentin', 'enseignant.secondaire.test17@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 24, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(195, 'Test Enseignant 18 Rachel', 'enseignant.secondaire.test18@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 25, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(196, 'Test Enseignant 19 Samuel', 'enseignant.secondaire.test19@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 26, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(197, 'Test Enseignant 20 Th?r?se', 'enseignant.secondaire.test20@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 27, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(198, 'Test Enseignant 21 Urbain', 'enseignant.secondaire.test21@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 28, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(199, 'Test Enseignant 22 V?ronique', 'enseignant.secondaire.test22@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 29, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(200, 'Test Enseignant 23 William', 'enseignant.secondaire.test23@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 30, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(201, 'Test Enseignant 24 Yvette', 'enseignant.secondaire.test24@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 31, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(202, 'Test Enseignant 25 Zo?', 'enseignant.secondaire.test25@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 32, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(203, 'Test Enseignant 26 Armand', 'enseignant.secondaire.test26@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 33, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(204, 'Test Enseignant 27 Brigitte', 'enseignant.secondaire.test27@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 34, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(205, 'Test Enseignant 28 Christian', 'enseignant.secondaire.test28@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 35, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(206, 'Test Enseignant 29 Delphine', 'enseignant.secondaire.test29@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 36, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(207, 'Test Enseignant 30 Emmanuel', 'enseignant.secondaire.test30@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 37, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(208, 'Test Enseignant 31 Flore', 'enseignant.secondaire.test31@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 38, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(209, 'Test Enseignant 32 Gr?goire', 'enseignant.secondaire.test32@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 39, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(210, 'Test Enseignant 33 Hortense', 'enseignant.secondaire.test33@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 40, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(211, 'Test Enseignant 34 Isma?l', 'enseignant.secondaire.test34@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 41, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(212, 'Test Enseignant 35 Josu?', 'enseignant.secondaire.test35@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 42, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(213, 'Test Enseignant 36 L?onie', 'enseignant.secondaire.test36@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 43, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(214, 'Test Enseignant 37 Marcel', 'enseignant.secondaire.test37@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 44, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(215, 'Test Enseignant 38 No?lle', 'enseignant.secondaire.test38@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 45, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(216, 'Test Enseignant 39 Patrice', 'enseignant.secondaire.test39@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 46, 'Actif', '2026-10-21 23:35:45', NULL, 3),
(217, 'Test Enseignant 40 Ruth', 'enseignant.secondaire.test40@kc-sma.local', '$2y$10$Ak4ctH.Yov69QUc9own8M.qoybQBaeM0tZrN397crgQcTCJrhqvmy', 'enseignant_école', 4, 47, 'Actif', '2026-10-21 23:35:45', NULL, 3);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_liaison_utilisateurs_complete`
-- (See below for the actual view)
--
CREATE TABLE `v_liaison_utilisateurs_complete` (
`utilisateur_id` int(11)
,`compte_nom_affiche` varchar(150)
,`compte_identifiant` varchar(100)
,`compte_role` enum('super_admin','ecole_admin','prefet_école','DE_école','DD_école','DP_école','DA_école','comptable_école','sec_école','promoteur_école','enseignant_école','agent_école','eleve_école','parent_école')
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
-- Indexes for table `administration_autorisations`
--
ALTER TABLE `administration_autorisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_administration_autorisations_ecole_role_module` (`ecole_id`,`role`,`module`),
  ADD KEY `idx_administration_autorisations_ecole_role` (`ecole_id`,`role`);

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
-- Indexes for table `communiques`
--
ALTER TABLE `communiques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_communiques_ecole_created` (`ecole_id`,`created_at`);

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
-- Indexes for table `demandes_abonnement_ecoles`
--
ALTER TABLE `demandes_abonnement_ecoles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demandes_abonnement_ecole` (`ecole_id`,`created_at`),
  ADD KEY `idx_demandes_abonnement_statut` (`statut`,`created_at`);

--
-- Indexes for table `derogations`
--
ALTER TABLE `derogations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_derogations_ecole` (`ecole_id`),
  ADD KEY `idx_derogations_eleve` (`eleve_id`),
  ADD KEY `idx_derogations_statut_date` (`statut`,`date_fin`),
  ADD KEY `fk_derogations_demandeur` (`demandeur_id`),
  ADD KEY `fk_derogations_validateur` (`validateur_id`);

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
-- Indexes for table `ecole_derogation_recipients`
--
ALTER TABLE `ecole_derogation_recipients`
  ADD PRIMARY KEY (`ecole_id`,`utilisateur_id`),
  ADD KEY `idx_derogation_recipients_user` (`utilisateur_id`);

--
-- Indexes for table `ecole_derogation_recipient_settings`
--
ALTER TABLE `ecole_derogation_recipient_settings`
  ADD PRIMARY KEY (`ecole_id`),
  ADD KEY `idx_derogation_recipient_settings_updated_by` (`updated_by_user_id`);

--
-- Indexes for table `ecole_notification_access`
--
ALTER TABLE `ecole_notification_access`
  ADD PRIMARY KEY (`ecole_id`),
  ADD KEY `idx_ecole_notification_access_updated_by` (`updated_by_user_id`);

--
-- Indexes for table `ecole_notification_preferences`
--
ALTER TABLE `ecole_notification_preferences`
  ADD PRIMARY KEY (`ecole_id`),
  ADD KEY `idx_ecole_notification_preferences_updated_by` (`updated_by_user_id`);

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
  ADD KEY `agent_saisie_id` (`agent_saisie_id`),
  ADD KEY `idx_ecritures_utilisateur_saisie` (`utilisateur_saisie_id`);

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
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`),
  ADD KEY `idx_evenements_auteur_user` (`auteur_user_id`);

--
-- Indexes for table `exonerations`
--
ALTER TABLE `exonerations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exonerations_ecole` (`ecole_id`),
  ADD KEY `idx_exonerations_eleve` (`eleve_id`),
  ADD KEY `idx_exonerations_statut` (`statut`),
  ADD KEY `fk_exonerations_frais` (`frais_id`),
  ADD KEY `fk_exonerations_demandeur` (`demandeur_id`),
  ADD KEY `fk_exonerations_validateur` (`validateur_id`);

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
  ADD UNIQUE KEY `uq_frais_ecole_encodage` (`ecole_id`,`encodage`),
  ADD KEY `classe_id` (`classe_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`),
  ADD KEY `scope_id` (`scope_id`),
  ADD KEY `idx_frais_ecole` (`ecole_id`),
  ADD KEY `idx_frais_ecole_encodage` (`ecole_id`,`encodage`);

--
-- Indexes for table `historique_etablissements`
--
ALTER TABLE `historique_etablissements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`);

--
-- Indexes for table `horaires_cours`
--
ALTER TABLE `horaires_cours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_timetable_class_period` (`ecole_id`,`annee_scolaire_id`,`classe_id`,`jour_id`,`periode`),
  ADD KEY `idx_timetable_class` (`ecole_id`,`annee_scolaire_id`,`classe_id`,`jour_id`),
  ADD KEY `idx_timetable_teacher` (`ecole_id`,`annee_scolaire_id`,`agent_id`,`jour_id`),
  ADD KEY `idx_timetable_assignment` (`attribution_cours_id`),
  ADD KEY `idx_timetable_teacher_period` (`ecole_id`,`annee_scolaire_id`,`agent_id`,`jour_id`);

--
-- Indexes for table `indices_enseignants`
--
ALTER TABLE `indices_enseignants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teacher_index_agent` (`ecole_id`,`annee_scolaire_id`,`agent_id`),
  ADD UNIQUE KEY `uq_teacher_index_number` (`ecole_id`,`annee_scolaire_id`,`indice`);

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
-- Indexes for table `jours_libres_enseignants`
--
ALTER TABLE `jours_libres_enseignants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teacher_weekly_day_off` (`ecole_id`,`annee_scolaire_id`,`agent_id`),
  ADD KEY `idx_teacher_day_off` (`ecole_id`,`annee_scolaire_id`,`jour_id`);

--
-- Indexes for table `jours_semaine`
--
ALTER TABLE `jours_semaine`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `livraisons_objets_scolaires`
--
ALTER TABLE `livraisons_objets_scolaires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_objet_livraison_eleve_frais` (`ecole_id`,`annee_scolaire_id`,`eleve_id`,`frais_id`),
  ADD KEY `idx_objet_livraison_date` (`ecole_id`,`annee_scolaire_id`,`date_livraison`);

--
-- Indexes for table `messages_utilisateurs`
--
ALTER TABLE `messages_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_message_user_reference` (`utilisateur_id`,`type`,`reference_id`),
  ADD KEY `idx_message_user_unread` (`utilisateur_id`,`is_read`,`created_at`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_note_eleve_evaluation` (`evaluation_id`,`eleve_id`),
  ADD KEY `eleve_id` (`eleve_id`);

--
-- Indexes for table `notifications_evenements`
--
ALTER TABLE `notifications_evenements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_notification_evenement_utilisateur` (`utilisateur_id`,`evenement_id`),
  ADD KEY `idx_notification_evenement_utilisateur_date` (`utilisateur_id`,`created_at`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_options_sections` (`section_id`),
  ADD KEY `idx_options_ecole_section` (`ecole_id`,`section_id`);

--
-- Indexes for table `paiements_autorisations`
--
ALTER TABLE `paiements_autorisations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_paiements_autorisations_ecole_role_frais` (`ecole_id`,`role`,`frais_id`),
  ADD KEY `idx_paiements_autorisations_ecole_role` (`ecole_id`,`role`),
  ADD KEY `idx_paiements_autorisations_frais` (`frais_id`);

--
-- Indexes for table `paiements_eleves`
--
ALTER TABLE `paiements_eleves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eleve_id` (`eleve_id`),
  ADD KEY `frais_id` (`frais_id`);

--
-- Indexes for table `parametres_horaires`
--
ALTER TABLE `parametres_horaires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_timetable_duration_section` (`ecole_id`,`section_id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_parent_telephone_ecole` (`ecole_id`,`telephone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `parent_child_requests`
--
ALTER TABLE `parent_child_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_child_request_parent` (`parent_id`,`status`,`created_at`),
  ADD KEY `idx_parent_child_request_student` (`student_id`,`status`),
  ADD KEY `idx_parent_child_request_school` (`school_id`,`status`);

--
-- Indexes for table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_participant_ev` (`evenement_id`,`agent_id`),
  ADD KEY `fk_part_final_agent` (`agent_id`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_reset_request_ip` (`remote_address`,`requested_at`),
  ADD KEY `idx_password_reset_request_user` (`user_id`,`requested_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_password_reset_token_hash` (`token_hash`),
  ADD UNIQUE KEY `uq_password_reset_user` (`user_id`),
  ADD KEY `idx_password_reset_expiration` (`expires_at`);

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
-- Indexes for table `programmes_cours`
--
ALTER TABLE `programmes_cours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_program_section` (`ecole_id`,`annee_scolaire_id`,`section_id`,`cours_id`),
  ADD KEY `idx_course_program_teacher` (`ecole_id`,`annee_scolaire_id`,`agent_id`);

--
-- Indexes for table `programmes_cours_classes`
--
ALTER TABLE `programmes_cours_classes`
  ADD PRIMARY KEY (`programme_id`,`classe_id`),
  ADD KEY `idx_course_program_class` (`classe_id`);

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
-- Indexes for table `school_messages`
--
ALTER TABLE `school_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_school_messages_recipient_unread` (`recipient_user_id`,`read_at`,`id`),
  ADD KEY `idx_school_messages_thread` (`thread_id`,`id`);

--
-- Indexes for table `school_message_threads`
--
ALTER TABLE `school_message_threads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_message_thread` (`school_id`,`student_id`,`parent_user_id`,`staff_user_id`),
  ADD KEY `idx_school_message_parent` (`parent_user_id`,`updated_at`),
  ADD KEY `idx_school_message_staff` (`staff_user_id`,`updated_at`);

--
-- Indexes for table `school_message_typing`
--
ALTER TABLE `school_message_typing`
  ADD PRIMARY KEY (`thread_id`,`user_id`),
  ADD KEY `idx_school_message_typing_expiry` (`typing_until`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seuils_recouvrement`
--
ALTER TABLE `seuils_recouvrement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seuil_recouvrement_ecole_devise` (`ecole_id`,`devise`),
  ADD KEY `fk_seuil_recouvrement_user` (`updated_by`);

--
-- Indexes for table `super_admin_activities`
--
ALTER TABLE `super_admin_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_super_admin_activities_created_at` (`created_at`),
  ADD KEY `idx_super_admin_activities_target` (`target_type`,`target_id`),
  ADD KEY `idx_super_admin_activities_actor` (`actor_user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `administration_autorisations`
--
ALTER TABLE `administration_autorisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `annees_scolaires`
--
ALTER TABLE `annees_scolaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attribution_cours`
--
ALTER TABLE `attribution_cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `caisses_banques`
--
ALTER TABLE `caisses_banques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories_evenements`
--
ALTER TABLE `categories_evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `child_requests`
--
ALTER TABLE `child_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `communiques`
--
ALTER TABLE `communiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comptes_eleves`
--
ALTER TABLE `comptes_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=707;

--
-- AUTO_INCREMENT for table `cours`
--
ALTER TABLE `cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `demandes_abonnement_ecoles`
--
ALTER TABLE `demandes_abonnement_ecoles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `derogations`
--
ALTER TABLE `derogations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dettes_eleves`
--
ALTER TABLE `dettes_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1437;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1464;

--
-- AUTO_INCREMENT for table `eleves`
--
ALTER TABLE `eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=711;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exonerations`
--
ALTER TABLE `exonerations`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `historique_etablissements`
--
ALTER TABLE `historique_etablissements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `horaires_cours`
--
ALTER TABLE `horaires_cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `indices_enseignants`
--
ALTER TABLE `indices_enseignants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=707;

--
-- AUTO_INCREMENT for table `journees_pedagogiques`
--
ALTER TABLE `journees_pedagogiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jours_libres_enseignants`
--
ALTER TABLE `jours_libres_enseignants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jours_semaine`
--
ALTER TABLE `jours_semaine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `livraisons_objets_scolaires`
--
ALTER TABLE `livraisons_objets_scolaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages_utilisateurs`
--
ALTER TABLE `messages_utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications_evenements`
--
ALTER TABLE `notifications_evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1827;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `paiements_autorisations`
--
ALTER TABLE `paiements_autorisations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `paiements_eleves`
--
ALTER TABLE `paiements_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `parametres_horaires`
--
ALTER TABLE `parametres_horaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `parent_child_requests`
--
ALTER TABLE `parent_child_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `participants_evenement`
--
ALTER TABLE `participants_evenement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `periodes`
--
ALTER TABLE `periodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `plans_abonnement`
--
ALTER TABLE `plans_abonnement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `presences_agents`
--
ALTER TABLE `presences_agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `presences_eleves`
--
ALTER TABLE `presences_eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `primes_avantages`
--
ALTER TABLE `primes_avantages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `programmes_cours`
--
ALTER TABLE `programmes_cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
-- AUTO_INCREMENT for table `school_messages`
--
ALTER TABLE `school_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `school_message_threads`
--
ALTER TABLE `school_message_threads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seuils_recouvrement`
--
ALTER TABLE `seuils_recouvrement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `super_admin_activities`
--
ALTER TABLE `super_admin_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

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
-- Constraints for table `derogations`
--
ALTER TABLE `derogations`
  ADD CONSTRAINT `fk_derogations_demandeur` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `fk_derogations_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_derogations_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_derogations_validateur` FOREIGN KEY (`validateur_id`) REFERENCES `utilisateurs` (`id`);

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
  ADD CONSTRAINT `fk_ecr_frais` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ecritures_utilisateur_saisie` FOREIGN KEY (`utilisateur_saisie_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
-- Constraints for table `exonerations`
--
ALTER TABLE `exonerations`
  ADD CONSTRAINT `fk_exonerations_demandeur` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `fk_exonerations_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exonerations_eleve` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exonerations_frais` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exonerations_validateur` FOREIGN KEY (`validateur_id`) REFERENCES `utilisateurs` (`id`);

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
  ADD CONSTRAINT `fk_frais_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
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
-- Constraints for table `seuils_recouvrement`
--
ALTER TABLE `seuils_recouvrement`
  ADD CONSTRAINT `fk_seuil_recouvrement_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seuil_recouvrement_user` FOREIGN KEY (`updated_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `fk_utilisateurs_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
