CREATE TABLE IF NOT EXISTS parcours_scolaires (
    id INT NOT NULL AUTO_INCREMENT,
    inscription_id INT NOT NULL,
    ecole_provenance VARCHAR(150) NOT NULL,
    annee_document VARCHAR(9) NOT NULL,
    mention ENUM('reussite', 'echec') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_parcours_inscription (inscription_id),
    CONSTRAINT fk_parcours_inscription
        FOREIGN KEY (inscription_id) REFERENCES inscriptions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bulletins_scolaires (
    id INT NOT NULL AUTO_INCREMENT,
    parcours_scolaire_id INT NOT NULL,
    fichier_stockage VARCHAR(255) NOT NULL,
    nom_original VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_bulletins_parcours (parcours_scolaire_id),
    CONSTRAINT fk_bulletins_parcours
        FOREIGN KEY (parcours_scolaire_id) REFERENCES parcours_scolaires (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
