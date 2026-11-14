CREATE TABLE IF NOT EXISTS ecole_sortie_approbation_roles (
    ecole_id INT NOT NULL,
    role VARCHAR(50) NOT NULL,
    configured_by_user_id INT DEFAULT NULL,
    configured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (ecole_id, role),
    CONSTRAINT fk_sortie_approbation_ecole
        FOREIGN KEY (ecole_id) REFERENCES ecoles (id) ON DELETE CASCADE,
    CONSTRAINT fk_sortie_approbation_user
        FOREIGN KEY (configured_by_user_id) REFERENCES utilisateurs (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sorties_caisse (
    id INT NOT NULL AUTO_INCREMENT,
    ecole_id INT NOT NULL,
    caisse_banque_id INT NOT NULL,
    paie_agent_id INT DEFAULT NULL,
    montant DECIMAL(12,2) NOT NULL,
    devise VARCHAR(5) NOT NULL,
    motif VARCHAR(255) NOT NULL,
    statut ENUM('En attente', 'Approuvée', 'Refusée') NOT NULL DEFAULT 'En attente',
    created_by_user_id INT NOT NULL,
    decided_by_user_id INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decided_at DATETIME DEFAULT NULL,
    decision_comment VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_sortie_caisse_paie_agent (paie_agent_id, statut),
    KEY idx_sortie_caisse_ecole_statut (ecole_id, statut, created_at),
    CONSTRAINT fk_sortie_caisse_ecole
        FOREIGN KEY (ecole_id) REFERENCES ecoles (id) ON DELETE RESTRICT,
    CONSTRAINT fk_sortie_caisse_account
        FOREIGN KEY (caisse_banque_id) REFERENCES caisses_banques (id) ON DELETE RESTRICT,
    CONSTRAINT fk_sortie_caisse_paie
        FOREIGN KEY (paie_agent_id) REFERENCES paies_agents (id) ON DELETE RESTRICT,
    CONSTRAINT fk_sortie_caisse_creator
        FOREIGN KEY (created_by_user_id) REFERENCES utilisateurs (id) ON DELETE RESTRICT,
    CONSTRAINT fk_sortie_caisse_decider
        FOREIGN KEY (decided_by_user_id) REFERENCES utilisateurs (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
