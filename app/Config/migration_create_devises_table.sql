DROP PROCEDURE IF EXISTS create_devises_table$$
CREATE PROCEDURE create_devises_table()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'devises'
    ) THEN
        CREATE TABLE `devises` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `code` varchar(5) NOT NULL,
          `libelle` varchar(100) NOT NULL,
          `taux` decimal(14,6) NOT NULL DEFAULT '1.000000',
          `actif` tinyint(1) NOT NULL DEFAULT 1,
          `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    END IF;
END$$

CALL create_devises_table();
DROP PROCEDURE IF EXISTS create_devises_table;

INSERT IGNORE INTO devises (code, libelle, taux, actif) VALUES
('USD', 'Dollar américain', 1.000000, 1),
('EUR', 'Euro', 1.100000, 1),
('CDF', 'Franc congolais', 0.000500, 1),
('XAF', 'Franc CFA BEAC', 0.001700, 1),
('XOF', 'Franc CFA BCEAO', 0.001700, 1);
