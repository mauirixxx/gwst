-- GWTTT treasure drop-entry fields
-- Apply to gwsttesting first after 2026-09-treasure-tracker.sql.

ALTER TABLE gwtreasure_history
    ADD COLUMN drop_type VARCHAR(20) NOT NULL DEFAULT 'nothing' AFTER gold_received,
    ADD COLUMN drop_description VARCHAR(255) DEFAULT NULL AFTER drop_type,
    ADD CONSTRAINT chk_gwtreasure_drop_type
        CHECK (drop_type IN ('weapon','material','rune_insignia','nothing'));
