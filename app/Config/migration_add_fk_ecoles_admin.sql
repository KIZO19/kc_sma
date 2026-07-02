-- Add foreign key constraint fk_ecoles_admin only if it does not already exist
-- This uses information_schema to detect existing constraints and runs the ALTER only when needed.

SELECT COUNT(*) INTO @exists FROM information_schema.key_column_usage
 WHERE constraint_schema = DATABASE()
   AND table_name = 'ecoles'
   AND column_name = 'admin_ecole_id'
   AND referenced_table_name = 'utilisateurs';

SET @sql = IF(@exists = 0,
  'ALTER TABLE `ecoles` ADD CONSTRAINT fk_ecoles_admin FOREIGN KEY (`admin_ecole_id`) REFERENCES `utilisateurs`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT "fk_ecoles_admin already exists";'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
