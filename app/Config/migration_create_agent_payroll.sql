CREATE TABLE IF NOT EXISTS agents_paie_preferences (
    agent_id INT NOT NULL,
    devise_salaire VARCHAR(5) NULL,
    updated_by_user_id INT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (agent_id),
    CONSTRAINT fk_agent_paie_preference_agent
        FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE CASCADE,
    CONSTRAINT fk_agent_paie_preference_user
        FOREIGN KEY (updated_by_user_id) REFERENCES utilisateurs (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS caisses_banques_devises (
    caisse_banque_id INT NOT NULL,
    devise VARCHAR(5) NOT NULL DEFAULT 'USD',
    updated_by_user_id INT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (caisse_banque_id),
    CONSTRAINT fk_caisse_banque_devise_account
        FOREIGN KEY (caisse_banque_id) REFERENCES caisses_banques (id) ON DELETE CASCADE,
    CONSTRAINT fk_caisse_banque_devise_user
        FOREIGN KEY (updated_by_user_id) REFERENCES utilisateurs (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO caisses_banques_devises (caisse_banque_id, devise)
SELECT id, 'USD'
FROM caisses_banques;

CREATE TABLE IF NOT EXISTS paies_agents (
    id INT NOT NULL AUTO_INCREMENT,
    agent_id INT NOT NULL,
    ecole_id INT NOT NULL,
    mois TINYINT UNSIGNED NOT NULL,
    annee SMALLINT UNSIGNED NOT NULL,
    devise VARCHAR(5) NOT NULL,
    salaire_base_historique DECIMAL(12,2) NOT NULL,
    total_primes DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_retenues DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    net_a_payer DECIMAL(12,2) NOT NULL,
    statut_paiement ENUM('En attente', 'Payé') NOT NULL DEFAULT 'En attente',
    caisse_banque_id INT DEFAULT NULL,
    created_by_user_id INT DEFAULT NULL,
    paid_by_user_id INT DEFAULT NULL,
    date_edition TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_paiement_effectif DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_paie_agent_mois_annee (agent_id, mois, annee),
    KEY idx_paie_ecole_periode (ecole_id, annee, mois),
    CONSTRAINT fk_paie_agent_agent
        FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE RESTRICT,
    CONSTRAINT fk_paie_agent_ecole
        FOREIGN KEY (ecole_id) REFERENCES ecoles (id) ON DELETE RESTRICT,
    CONSTRAINT fk_paie_agent_account
        FOREIGN KEY (caisse_banque_id) REFERENCES caisses_banques (id) ON DELETE SET NULL,
    CONSTRAINT fk_paie_agent_user
        FOREIGN KEY (created_by_user_id) REFERENCES utilisateurs (id) ON DELETE SET NULL,
    CONSTRAINT fk_paie_agent_paid_by_user
        FOREIGN KEY (paid_by_user_id) REFERENCES utilisateurs (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS paies_agents_lignes (
    id INT NOT NULL AUTO_INCREMENT,
    paie_agent_id INT NOT NULL,
    type_ligne ENUM('prime', 'retenue') NOT NULL,
    libelle VARCHAR(150) NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_paie_agent_lignes (paie_agent_id),
    CONSTRAINT fk_paie_agent_ligne_slip
        FOREIGN KEY (paie_agent_id) REFERENCES paies_agents (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
