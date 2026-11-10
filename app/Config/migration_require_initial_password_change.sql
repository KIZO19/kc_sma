SET @must_change_password_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'utilisateurs'
      AND COLUMN_NAME = 'must_change_password'
);

SET @must_change_password_migration = IF(
    @must_change_password_column_exists = 0,
    'ALTER TABLE utilisateurs ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER mot_de_passe',
    'SET @must_change_password_migration_status = ''already present'''
);

PREPARE must_change_password_statement FROM @must_change_password_migration;
EXECUTE must_change_password_statement;
DEALLOCATE PREPARE must_change_password_statement;
