CREATE TABLE IF NOT EXISTS seuils_recouvrement (
    id INT NOT NULL AUTO_INCREMENT,
    ecole_id INT NOT NULL,
    devise VARCHAR(5) NOT NULL,
    montant DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_seuil_recouvrement_ecole_devise (ecole_id, devise),
    CONSTRAINT fk_seuil_recouvrement_ecole FOREIGN KEY (ecole_id) REFERENCES ecoles (id) ON DELETE CASCADE,
    CONSTRAINT fk_seuil_recouvrement_user FOREIGN KEY (updated_by) REFERENCES utilisateurs (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;