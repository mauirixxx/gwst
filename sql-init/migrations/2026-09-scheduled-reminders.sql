-- GWTTT scheduled reminder foundation
-- Apply to gwsttesting first.

ALTER TABLE user_preferences
    ADD COLUMN treasure_email_enabled TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER birthday_reminder_days,
    ADD CONSTRAINT chk_user_preferences_treasure_email
        CHECK (treasure_email_enabled IN (0,1));

CREATE TABLE reminder_notifications (
    notification_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    userid INT NOT NULL,
    reminder_type VARCHAR(30) NOT NULL,
    reference_key VARCHAR(191) NOT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id),
    UNIQUE KEY uq_reminder_notification (userid, reminder_type, reference_key),
    KEY idx_reminder_notifications_sent_at (sent_at),
    CONSTRAINT fk_reminder_notification_user
        FOREIGN KEY (userid) REFERENCES userinfo(userid) ON DELETE CASCADE,
    CONSTRAINT chk_reminder_notification_type
        CHECK (reminder_type IN ('treasure','birthday'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
