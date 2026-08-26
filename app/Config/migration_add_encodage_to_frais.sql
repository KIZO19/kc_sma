-- Migration: add encodage column to frais_scolaires
ALTER TABLE `frais_scolaires`
  ADD COLUMN `encodage` VARCHAR(100) NULL AFTER `ecole_id`;

-- Make encodage unique per school by adding a composite unique index on (ecole_id, encodage)
ALTER TABLE `frais_scolaires`
  ADD INDEX `idx_frais_ecole_encodage` (`ecole_id`, `encodage`),
  ADD UNIQUE INDEX `uq_frais_ecole_encodage` (`ecole_id`, `encodage`);

-- Optional: add a foreign key constraint to ensure ecole_id refers to ecoles(id)
-- ALTER TABLE `frais_scolaires` ADD CONSTRAINT `fk_frais_ecole` FOREIGN KEY (`ecole_id`) REFERENCES `ecoles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
