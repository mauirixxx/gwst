-- GWTTT miniature catalog administration upgrade
-- Apply once after miniature-tracker-schema.sql / miniature-tracker-seed.sql.
ALTER TABLE gwminiatures
    ADD COLUMN IF NOT EXISTS rarity VARCHAR(16) NOT NULL DEFAULT 'White' AFTER mininame,
    ADD COLUMN IF NOT EXISTS wiki_url VARCHAR(255) DEFAULT NULL AFTER rarity;

-- Existing catalog entries use the Guild Wars Wiki's standard miniature page naming.
-- The admin editor can correct any exceptional destination without touching SQL.
UPDATE gwminiatures
SET wiki_url = CONCAT('https://wiki.guildwars.com/wiki/Miniature_', REPLACE(mininame, ' ', '_'))
WHERE wiki_url IS NULL OR wiki_url = '';
