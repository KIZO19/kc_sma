ALTER TABLE parcours_scolaires
    DROP INDEX uq_parcours_inscription,
    ADD INDEX idx_parcours_inscription (inscription_id);
