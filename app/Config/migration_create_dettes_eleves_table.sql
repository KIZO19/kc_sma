DELIMITER $$
DROP PROCEDURE IF EXISTS create_dettes_eleves_table$$
CREATE PROCEDURE create_dettes_eleves_table()
BEGIN
  DECLARE cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO cnt FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dettes_eleves' AND COLUMN_NAME = 'id';
  IF cnt = 0 THEN
    SET @sql = 'CREATE TABLE `dettes_eleves` (';
    SET @sql = CONCAT(@sql, '`id` int(11) NOT NULL AUTO_INCREMENT, ');
    SET @sql = CONCAT(@sql, '`eleve_id` int(11) NOT NULL, ');
    SET @sql = CONCAT(@sql, '`frais_id` int(11) NOT NULL, ');
    SET @sql = CONCAT(@sql, '`annee_scolaire_id` int(11) NOT NULL, ');
    SET @sql = CONCAT(@sql, '`montant_initial` decimal(10,2) NOT NULL DEFAULT 0.00, ');
    SET @sql = CONCAT(@sql, '`montant_restant` decimal(10,2) NOT NULL DEFAULT 0.00, ');
    SET @sql = CONCAT(@sql, '`devise` varchar(5) NOT NULL DEFAULT \'USD\', ');
    SET @sql = CONCAT(@sql, '`date_creation` timestamp NOT NULL DEFAULT current_timestamp(), ');
    SET @sql = CONCAT(@sql, 'PRIMARY KEY (`id`), ');
    SET @sql = CONCAT(@sql, 'KEY `eleve_id` (`eleve_id`), ');
    SET @sql = CONCAT(@sql, 'KEY `frais_id` (`frais_id`), ');
    SET @sql = CONCAT(@sql, 'KEY `annee_scolaire_id` (`annee_scolaire_id`), ');
    SET @sql = CONCAT(@sql, 'KEY `montant_restant` (`montant_restant`) ');
    SET @sql = CONCAT(@sql, ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    SET @sql = 'ALTER TABLE `dettes_eleves` ADD CONSTRAINT `dettes_eleves_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE';
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    SET @sql = 'ALTER TABLE `dettes_eleves` ADD CONSTRAINT `dettes_eleves_ibfk_2` FOREIGN KEY (`frais_id`) REFERENCES `frais_scolaires` (`id`) ON DELETE CASCADE ON UPDATE CASCADE';
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    SET @sql = 'ALTER TABLE `dettes_eleves` ADD CONSTRAINT `dettes_eleves_ibfk_3` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE ON UPDATE CASCADE';
    PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

CALL create_dettes_eleves_table();
DROP PROCEDURE IF EXISTS create_dettes_eleves_table;
