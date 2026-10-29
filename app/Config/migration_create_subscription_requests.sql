CREATE TABLE IF NOT EXISTS demandes_abonnement_ecoles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ecole_id INT NOT NULL,
    demandeur_id INT NOT NULL,
    plan_id INT NOT NULL,
    type_demande ENUM('nouvel_abonnement', 'renouvellement') NOT NULL,
    message VARCHAR(1000) NULL,
    statut ENUM('En_attente', 'Approuvee', 'Refusee') NOT NULL DEFAULT 'En_attente',
    traite_par INT NULL,
    traite_le DATETIME NULL,
    reponse VARCHAR(1000) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_demandes_abonnement_ecole (ecole_id, created_at),
    KEY idx_demandes_abonnement_statut (statut, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
