ALTER TABLE `utilisateurs`
  ADD COLUMN IF NOT EXISTS `avatar` VARCHAR(255) DEFAULT NULL;

-- add admin_ecole_id without AFTER since `identifiant` may not exist in this schema
ALTER TABLE `ecoles`
  ADD COLUMN IF NOT EXISTS `admin_ecole_id` INT DEFAULT NULL;
