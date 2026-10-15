DELIMITER $$
DROP PROCEDURE IF EXISTS allow_trial_subscriptions$$
CREATE PROCEDURE allow_trial_subscriptions()
BEGIN
  DECLARE column_nullable VARCHAR(3) DEFAULT 'NO';
  SELECT IS_NULLABLE INTO column_nullable
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'abonnements_ecoles'
      AND COLUMN_NAME = 'plan_id'
    LIMIT 1;

  IF column_nullable = 'NO' THEN
    ALTER TABLE `abonnements_ecoles` MODIFY `plan_id` int(11) DEFAULT NULL;
  END IF;
END$$
DELIMITER ;

CALL allow_trial_subscriptions();
DROP PROCEDURE IF EXISTS allow_trial_subscriptions;
