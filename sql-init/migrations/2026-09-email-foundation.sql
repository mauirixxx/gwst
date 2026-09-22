-- GWST email foundation
-- Run once against an existing GWST database.
-- Birthday reminders are opt-in: no preference row means disabled/defaults.

CREATE TABLE `user_preferences` (
  `userid` INT NOT NULL,
  `birthday_email_enabled` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `birthday_reminder_days` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`),
  CONSTRAINT `fk_user_preferences_user`
    FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE,
  CONSTRAINT `chk_user_preferences_birthday_email`
    CHECK (`birthday_email_enabled` IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `mail_settings` (
  `settings_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `enabled` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `smtp_host` VARCHAR(255) NOT NULL DEFAULT '',
  `smtp_port` SMALLINT UNSIGNED NOT NULL DEFAULT 587,
  `smtp_encryption` VARCHAR(10) NOT NULL DEFAULT 'tls',
  `smtp_username` VARCHAR(255) NOT NULL DEFAULT '',
  `from_address` VARCHAR(255) NOT NULL DEFAULT '',
  `from_name` VARCHAR(100) NOT NULL DEFAULT 'Guild Wars Stats Tracker',
  `reply_to_address` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`settings_id`),
  CONSTRAINT `chk_mail_settings_singleton` CHECK (`settings_id` = 1),
  CONSTRAINT `chk_mail_settings_enabled` CHECK (`enabled` IN (0,1)),
  CONSTRAINT `chk_mail_settings_encryption` CHECK (`smtp_encryption` IN ('none','tls','ssl'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mail_settings` (`settings_id`) VALUES (1);
