DELIMITER $$
DROP PROCEDURE IF EXISTS add_missing_frais_devise_column$$
CREATE PROCEDURE add_missing_frais_devise_column()
BEGIN
  DECLARE cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'frais_scolaires' AND COLUMN_NAME = 'devise';
  IF cnt = 0 THEN
    SET @sql = 'ALTER TABLE `frais_scolaires` ADD COLUMN `devise` VARCHAR(5) NOT NULL DEFAULT \'USD\';';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

CALL add_missing_frais_devise_column();
DROP PROCEDURE IF EXISTS add_missing_frais_devise_column;
