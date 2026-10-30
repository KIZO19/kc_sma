CREATE TABLE IF NOT EXISTS ecole_school_message_recipient_settings (
    ecole_id INT NOT NULL PRIMARY KEY,
    updated_by_user_id INT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_school_message_recipient_settings_updated_by (updated_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ecole_school_message_recipients (
    ecole_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    PRIMARY KEY (ecole_id, utilisateur_id),
    KEY idx_school_message_recipients_user (utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
