-- Idempotent migration: add any missing columns to `ecoles` and `utilisateurs`.
-- This script creates and calls a small procedure that builds ALTER statements
-- only for columns that are not yet present.

DELIMITER $$
DROP PROCEDURE IF EXISTS add_missing_ecole_columns$$
CREATE PROCEDURE add_missing_ecole_columns()
BEGIN
  DECLARE alter_sql TEXT DEFAULT '';
  DECLARE cnt INT DEFAULT 0;

  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ecoles' AND COLUMN_NAME = 'identifiant';
  IF cnt = 0 THEN SET alter_sql = CONCAT(alter_sql, ' ADD COLUMN `identifiant` VARCHAR(100) DEFAULT NULL,'); END IF;

  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ecoles' AND COLUMN_NAME = 'matricule';
  IF cnt = 0 THEN SET alter_sql = CONCAT(alter_sql, ' ADD COLUMN `matricule` VARCHAR(50) DEFAULT NULL,'); END IF;

  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ecoles' AND COLUMN_NAME = 'logo_url';
  IF cnt = 0 THEN SET alter_sql = CONCAT(alter_sql, ' ADD COLUMN `logo_url` VARCHAR(255) DEFAULT NULL,'); END IF;

  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ecoles' AND COLUMN_NAME = 'admin_ecole_id';
  IF cnt = 0 THEN SET alter_sql = CONCAT(alter_sql, ' ADD COLUMN `admin_ecole_id` INT DEFAULT NULL,'); END IF;

  IF CHAR_LENGTH(alter_sql) > 0 THEN
    SET @s = CONCAT('ALTER TABLE `ecoles`', LEFT(alter_sql, CHAR_LENGTH(alter_sql) - 1), ';');
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

CALL add_missing_ecole_columns();
DROP PROCEDURE IF EXISTS add_missing_ecole_columns;

-- Add avatar column to utilisateurs if missing (separate safe block)
DELIMITER $$
DROP PROCEDURE IF EXISTS add_missing_utilisateurs_columns$$
CREATE PROCEDURE add_missing_utilisateurs_columns()
BEGIN
  DECLARE cnt2 INT DEFAULT 0;
  SELECT COUNT(*) INTO cnt2 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = 'avatar';
  IF cnt2 = 0 THEN
    SET @u = 'ALTER TABLE `utilisateurs` ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL;';
    PREPARE st2 FROM @u;
    EXECUTE st2;
    DEALLOCATE PREPARE st2;
  END IF;

  SELECT COUNT(*) INTO cnt2 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = 'section_id';
  IF cnt2 = 0 THEN
    SET @u = 'ALTER TABLE `utilisateurs` ADD COLUMN `section_id` INT DEFAULT NULL COMMENT \'Section affectée à l\'utilisateur pour ses responsabilités\'';
    PREPARE st2 FROM @u;
    EXECUTE st2;
    DEALLOCATE PREPARE st2;
  END IF;
END$$
DELIMITER ;

CALL add_missing_utilisateurs_columns();
DROP PROCEDURE IF EXISTS add_missing_utilisateurs_columns;

-- Note: foreign key constraint for `ecoles.admin_ecole_id` -> `utilisateurs.id`
-- can be added separately once both columns exist and you confirm referential integrity is desired.
