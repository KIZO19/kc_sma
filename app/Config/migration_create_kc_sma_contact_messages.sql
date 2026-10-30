CREATE TABLE IF NOT EXISTS kc_sma_contact_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sender_user_id INT NOT NULL,
    school_id INT NULL,
    sender_name VARCHAR(160) NOT NULL,
    sender_role VARCHAR(100) NOT NULL,
    subject VARCHAR(160) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_kc_sma_contact_sender (sender_user_id, created_at),
    KEY idx_kc_sma_contact_school (school_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
