ALTER TABLE ecritures_comptables_eleves
    MODIFY agent_saisie_id INT(11) NULL,
    ADD COLUMN utilisateur_saisie_id INT(11) NULL AFTER agent_saisie_id,
    ADD KEY idx_ecritures_utilisateur_saisie (utilisateur_saisie_id),
    ADD CONSTRAINT fk_ecritures_utilisateur_saisie
        FOREIGN KEY (utilisateur_saisie_id) REFERENCES utilisateurs (id)
        ON DELETE SET NULL ON UPDATE CASCADE;
