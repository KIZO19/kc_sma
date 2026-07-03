DELIMITER $$
DROP PROCEDURE IF EXISTS add_missing_frais_scope_columns$$
CREATE PROCEDURE add_missing_frais_scope_columns()
BEGIN
  DECLARE cnt INT DEFAULT 0;
  -- Make classe_id nullable if needed
  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'frais_scolaires' AND COLUMN_NAME = 'scope';
  IF cnt = 0 THEN
    SET @sql = 'ALTER TABLE `frais_scolaires` MODIFY `classe_id` INT DEFAULT NULL;';
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    SET @sql = 'ALTER TABLE `frais_scolaires` ADD COLUMN `scope` VARCHAR(20) NOT NULL DEFAULT \'class\',';
    SET @sql = CONCAT(@sql, ' ADD COLUMN `scope_id` INT DEFAULT NULL;');
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    SET @sql = 'ALTER TABLE `frais_scolaires` ADD KEY `scope_id` (`scope_id`);';
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

CALL add_missing_frais_scope_columns();
DROP PROCEDURE IF EXISTS add_missing_frais_scope_columns;
