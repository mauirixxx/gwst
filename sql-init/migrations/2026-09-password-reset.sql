-- GWST password reset tokens
-- Safe to apply to an existing installation after the schema-hardening migration.

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    userid INT(11) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (token_id),
    UNIQUE KEY uq_password_reset_token_hash (token_hash),
    KEY idx_password_reset_user (userid),
    KEY idx_password_reset_expires (expires_at),
    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (userid) REFERENCES userinfo (userid)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
