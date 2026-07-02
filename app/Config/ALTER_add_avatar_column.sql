-- Add avatar column to utilisateurs table
ALTER TABLE `utilisateurs`
  ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL AFTER `created_at`;
