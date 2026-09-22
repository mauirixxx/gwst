-- GWST treasure tracker foundation
-- Apply to gwsttesting first. This migration is additive and does not import legacy GWTT history.

CREATE TABLE IF NOT EXISTS gwtreasure_locations (
    location_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    location_name VARCHAR(100) NOT NULL,
    wiki_url VARCHAR(255) DEFAULT NULL,
    reset_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (location_id),
    UNIQUE KEY uq_gwtreasure_location_name (location_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO gwtreasure_locations (location_name, wiki_url, reset_days, display_order) VALUES
('Issnur Isles','https://wiki.guildwars.com/wiki/Issnur_Isles',30,1),
('Mehtani Keys','https://wiki.guildwars.com/wiki/Mehtani_Keys',30,2),
('Arkjok Ward','https://wiki.guildwars.com/wiki/Arkjok_Ward',30,3),
('Jahai Bluffs','https://wiki.guildwars.com/wiki/Jahai_Bluffs',30,4),
('Bahdok Caverns','https://wiki.guildwars.com/wiki/Bahdok_Caverns',30,5),
('The Mirror of Lyss','https://wiki.guildwars.com/wiki/The_Mirror_of_Lyss',30,6),
('The Hidden City of Ahdashim','https://wiki.guildwars.com/wiki/The_Hidden_City_of_Ahdashim',30,7),
('Forum Highlands','https://wiki.guildwars.com/wiki/Forum_Highlands',30,8),
('The Sulfurous Wastes','https://wiki.guildwars.com/wiki/The_Sulfurous_Wastes',30,9),
('The Ruptured Heart','https://wiki.guildwars.com/wiki/The_Ruptured_Heart',30,10),
('Nightfallen Jahai','https://wiki.guildwars.com/wiki/Nightfallen_Jahai',30,11),
('Domain of Pain','https://wiki.guildwars.com/wiki/Domain_of_Pain',30,12)
ON DUPLICATE KEY UPDATE
    wiki_url = VALUES(wiki_url),
    reset_days = VALUES(reset_days),
    display_order = VALUES(display_order);

CREATE TABLE IF NOT EXISTS gwtreasure_history (
    treasure_history_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    userid INT NOT NULL,
    accid INT NOT NULL,
    charid INT NOT NULL,
    location_id SMALLINT UNSIGNED NOT NULL,
    collected_on DATE NOT NULL,
    gold_received MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
    notes VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (treasure_history_id),
    KEY idx_gwtreasure_character_location_date (userid, charid, location_id, collected_on),
    KEY idx_gwtreasure_account_character (accid, charid),
    KEY idx_gwtreasure_location (location_id),
    CONSTRAINT fk_gwtreasure_history_user FOREIGN KEY (userid) REFERENCES userinfo(userid) ON DELETE CASCADE,
    CONSTRAINT fk_gwtreasure_history_account FOREIGN KEY (accid) REFERENCES gwaccounts(accid) ON DELETE CASCADE,
    CONSTRAINT fk_gwtreasure_history_character FOREIGN KEY (charid) REFERENCES gwchars(charid) ON DELETE CASCADE,
    CONSTRAINT fk_gwtreasure_history_location FOREIGN KEY (location_id) REFERENCES gwtreasure_locations(location_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
