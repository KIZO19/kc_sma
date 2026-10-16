-- Give each school's option catalog independent records while preserving
-- existing class and option-scoped fee references.

DELIMITER $$
DROP PROCEDURE IF EXISTS add_option_school_scope$$
CREATE PROCEDURE add_option_school_scope()
BEGIN
  DECLARE column_count INT DEFAULT 0;
  DECLARE index_count INT DEFAULT 0;

  SELECT COUNT(*) INTO column_count
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'options'
    AND COLUMN_NAME = 'ecole_id';

  IF column_count = 0 THEN
    ALTER TABLE options ADD COLUMN ecole_id INT DEFAULT NULL AFTER id;
  END IF;

  SELECT COUNT(*) INTO index_count
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'options'
    AND INDEX_NAME = 'idx_options_ecole_section';

  IF index_count = 0 THEN
    ALTER TABLE options ADD INDEX idx_options_ecole_section (ecole_id, section_id);
  END IF;
END$$
DELIMITER ;

CALL add_option_school_scope();
DROP PROCEDURE IF EXISTS add_option_school_scope;

DROP TEMPORARY TABLE IF EXISTS option_school_scope_map;
CREATE TEMPORARY TABLE option_school_scope_map (
  old_option_id INT NOT NULL,
  ecole_id INT NOT NULL,
  mapped_option_id INT DEFAULT NULL,
  PRIMARY KEY (old_option_id, ecole_id)
);

INSERT IGNORE INTO option_school_scope_map (old_option_id, ecole_id)
SELECT option_id, ecole_id
FROM classes
WHERE option_id IS NOT NULL
UNION
SELECT scope_id, ecole_id
FROM frais_scolaires
WHERE scope = 'option'
  AND scope_id IS NOT NULL
  AND ecole_id IS NOT NULL;

INSERT IGNORE INTO option_school_scope_map (old_option_id, ecole_id)
SELECT o.id, e.id
FROM options o
CROSS JOIN ecoles e
WHERE o.ecole_id IS NULL
  AND NOT EXISTS (
    SELECT 1
    FROM option_school_scope_map m
    WHERE m.old_option_id = o.id
  )
  AND (
    o.section_id IS NULL
    OR EXISTS (
      SELECT 1
      FROM classes c
      WHERE c.ecole_id = e.id
        AND c.section_id = o.section_id
    )
  );

UPDATE options o
INNER JOIN (
  SELECT old_option_id, MIN(ecole_id) AS first_school_id
  FROM option_school_scope_map
  GROUP BY old_option_id
) ownership ON ownership.old_option_id = o.id
SET o.ecole_id = ownership.first_school_id
WHERE o.ecole_id IS NULL;

DELIMITER $$
DROP PROCEDURE IF EXISTS migrate_option_school_scope$$
CREATE PROCEDURE migrate_option_school_scope()
BEGIN
  DECLARE finished INT DEFAULT 0;
  DECLARE v_old_option_id INT;
  DECLARE v_school_id INT;
  DECLARE v_mapped_option_id INT;
  DECLARE v_owner_school_id INT;
  DECLARE v_option_name VARCHAR(100);
  DECLARE v_option_section_id INT;

  DECLARE scope_cursor CURSOR FOR
    SELECT old_option_id, ecole_id
    FROM option_school_scope_map
    ORDER BY old_option_id, ecole_id;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

  OPEN scope_cursor;
  scope_loop: LOOP
    FETCH scope_cursor INTO v_old_option_id, v_school_id;
    IF finished = 1 THEN
      LEAVE scope_loop;
    END IF;

    SELECT ecole_id, nom_option, section_id
    INTO v_owner_school_id, v_option_name, v_option_section_id
    FROM options
    WHERE id = v_old_option_id;

    IF v_owner_school_id = v_school_id THEN
      SET v_mapped_option_id = v_old_option_id;
    ELSE
      INSERT INTO options (ecole_id, nom_option, section_id)
      VALUES (v_school_id, v_option_name, v_option_section_id);
      SET v_mapped_option_id = LAST_INSERT_ID();
    END IF;

    UPDATE classes
    SET option_id = v_mapped_option_id
    WHERE option_id = v_old_option_id
      AND ecole_id = v_school_id;

    UPDATE frais_scolaires
    SET scope_id = v_mapped_option_id
    WHERE scope = 'option'
      AND scope_id = v_old_option_id
      AND ecole_id = v_school_id;

    UPDATE option_school_scope_map
    SET mapped_option_id = v_mapped_option_id
    WHERE old_option_id = v_old_option_id
      AND ecole_id = v_school_id;
  END LOOP;
  CLOSE scope_cursor;
END$$
DELIMITER ;

CALL migrate_option_school_scope();
DROP PROCEDURE IF EXISTS migrate_option_school_scope;
DROP TEMPORARY TABLE IF EXISTS option_school_scope_map;
