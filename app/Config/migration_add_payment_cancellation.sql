ALTER TABLE ecritures_comptables_eleves
    ADD COLUMN annulee_at DATETIME NULL DEFAULT NULL,
    ADD COLUMN annulee_par INT(11) NULL DEFAULT NULL,
    ADD COLUMN motif_annulation VARCHAR(500) NULL DEFAULT NULL,
    ADD KEY idx_ecritures_annulee_at (annulee_at),
    ADD KEY idx_ecritures_annulee_par (annulee_par),
    ADD CONSTRAINT fk_ecritures_annulee_par
        FOREIGN KEY (annulee_par) REFERENCES utilisateurs (id)
        ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE paiements_eleves
    ADD COLUMN ecriture_comptable_id INT(11) NULL DEFAULT NULL,
    ADD UNIQUE KEY uq_paiements_ecriture_comptable (ecriture_comptable_id);
