CREATE TABLE IF NOT EXISTS school_message_threads (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    student_id INT NOT NULL,
    parent_user_id INT NOT NULL,
    staff_user_id INT NOT NULL,
    subject VARCHAR(160) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_school_message_thread (school_id, student_id, parent_user_id, staff_user_id),
    KEY idx_school_message_parent (parent_user_id, updated_at),
    KEY idx_school_message_staff (staff_user_id, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS school_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    thread_id BIGINT UNSIGNED NOT NULL,
    sender_user_id INT NOT NULL,
    recipient_user_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_school_messages_recipient_unread (recipient_user_id, read_at, id),
    KEY idx_school_messages_thread (thread_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS school_message_typing (
    thread_id BIGINT UNSIGNED NOT NULL,
    user_id INT NOT NULL,
    typing_until DATETIME NOT NULL,
    PRIMARY KEY (thread_id, user_id),
    KEY idx_school_message_typing_expiry (typing_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
