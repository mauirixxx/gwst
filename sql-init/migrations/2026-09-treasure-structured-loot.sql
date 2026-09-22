-- GWTTT structured treasure loot catalog
-- Apply to gwsttesting first. Additive: existing treasure history remains valid.

CREATE TABLE IF NOT EXISTS gwtreasure_rarities (
    rarity_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    rarity_name VARCHAR(20) NOT NULL,
    PRIMARY KEY (rarity_id), UNIQUE KEY uq_gwtreasure_rarity (rarity_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_rarities (rarity_id, rarity_name) VALUES
(1,'White'),(2,'Blue'),(3,'Purple'),(4,'Gold'),(5,'Green');

CREATE TABLE IF NOT EXISTS gwtreasure_requirements (
    requirement TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (requirement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_requirements VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9),(10),(11),(12),(13);

CREATE TABLE IF NOT EXISTS gwtreasure_weapon_types (
    weapon_type_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    weapon_type_name VARCHAR(40) NOT NULL,
    PRIMARY KEY (weapon_type_id), UNIQUE KEY uq_gwtreasure_weapon_type (weapon_type_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_weapon_types (weapon_type_id, weapon_type_name) VALUES
(1,'Axe'),(2,'Hammer'),(3,'Sword'),(4,'Dagger'),(5,'Scythe'),(6,'Bow (Flatbow)'),(7,'Bow (Hornbow)'),(8,'Bow (Longbow)'),(9,'Bow (Recurve)'),(10,'Bow (Shortbow)'),(11,'Spear'),(12,'Staff'),(13,'Wand'),(14,'Focus'),(15,'Shield');

CREATE TABLE IF NOT EXISTS gwtreasure_attributes (
    attribute_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    attribute_name VARCHAR(50) NOT NULL,
    PRIMARY KEY (attribute_id), UNIQUE KEY uq_gwtreasure_attribute (attribute_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_attributes (attribute_name) VALUES
('Strength'),('Swordsmanship'),('Axe Mastery'),('Hammer Mastery'),('Tactics'),('Expertise'),('Marksmanship'),('Wilderness Survival'),('Beast Mastery'),('Divine Favor'),('Healing Prayers'),('Protection Prayers'),('Smiting Prayers'),('Soul Reaping'),('Blood Magic'),('Curses'),('Death Magic'),('Fast Casting'),('Inspiration Magic'),('Domination Magic'),('Illusion Magic'),('Energy Storage'),('Fire Magic'),('Water Magic'),('Air Magic'),('Earth Magic'),('Critical Strikes'),('Dagger Mastery'),('Deadly Arts'),('Shadow Arts'),('Spawning Power'),('Channeling Magic'),('Communing'),('Restoration Magic'),('Leadership'),('Spear Mastery'),('Command'),('Motivation'),('Mysticism'),('Scythe Mastery'),('Wind Prayers'),('Earth Prayers');

CREATE TABLE IF NOT EXISTS gwtreasure_weapon_attribute_map (
    weapon_type_id TINYINT UNSIGNED NOT NULL,
    attribute_id TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (weapon_type_id, attribute_id),
    KEY idx_gwtreasure_weapon_attr_attribute (attribute_id),
    CONSTRAINT fk_gwtreasure_weapon_attr_type FOREIGN KEY (weapon_type_id) REFERENCES gwtreasure_weapon_types(weapon_type_id) ON DELETE CASCADE,
    CONSTRAINT fk_gwtreasure_weapon_attr_attribute FOREIGN KEY (attribute_id) REFERENCES gwtreasure_attributes(attribute_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_weapon_attribute_map (weapon_type_id, attribute_id)
SELECT t.weapon_type_id, a.attribute_id FROM gwtreasure_weapon_types t JOIN gwtreasure_attributes a ON
(t.weapon_type_name='Axe' AND a.attribute_name='Axe Mastery') OR
(t.weapon_type_name='Hammer' AND a.attribute_name='Hammer Mastery') OR
(t.weapon_type_name='Sword' AND a.attribute_name='Swordsmanship') OR
(t.weapon_type_name='Dagger' AND a.attribute_name='Dagger Mastery') OR
(t.weapon_type_name='Scythe' AND a.attribute_name='Scythe Mastery') OR
(t.weapon_type_name LIKE 'Bow (%)' AND a.attribute_name='Marksmanship') OR
(t.weapon_type_name='Spear' AND a.attribute_name='Spear Mastery') OR
(t.weapon_type_name='Shield' AND a.attribute_name IN ('Strength','Tactics','Leadership','Command','Motivation')) OR
(t.weapon_type_name IN ('Focus','Wand','Staff') AND a.attribute_name IN ('Divine Favor','Healing Prayers','Protection Prayers','Smiting Prayers','Soul Reaping','Blood Magic','Curses','Death Magic','Fast Casting','Inspiration Magic','Domination Magic','Illusion Magic','Energy Storage','Fire Magic','Water Magic','Air Magic','Earth Magic','Spawning Power','Channeling Magic','Communing','Restoration Magic'));

CREATE TABLE IF NOT EXISTS gwtreasure_materials (
    material_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    material_name VARCHAR(50) NOT NULL,
    PRIMARY KEY (material_id), UNIQUE KEY uq_gwtreasure_material (material_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_materials (material_name) VALUES
('Amber Chunk'),('Bolt of Damask'),('Bolt of Linen'),('Bolt of Silk'),('Deldrimor Steel Ingot'),('Diamond'),('Elonian Leather Square'),('Fur Square'),('Glob of Ectoplasm'),('Jadeite Shard'),('Leather Square'),('Lump of Charcoal'),('Monstrous Claw'),('Monstrous Eye'),('Monstrous Fang'),('Obsidian Shard'),('Onyx Gemstone'),('Roll of Parchment'),('Roll of Vellum'),('Ruby'),('Sapphire'),('Spiritwood Plank'),('Steel Ingot'),('Tempered Glass Vial'),('Vial of Ink');

CREATE TABLE IF NOT EXISTS gwtreasure_professions (
    profession_id TINYINT UNSIGNED NOT NULL,
    profession_name VARCHAR(30) NOT NULL,
    PRIMARY KEY (profession_id), UNIQUE KEY uq_gwtreasure_profession (profession_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_professions VALUES
(1,'None'),(2,'Warrior'),(3,'Ranger'),(4,'Monk'),(5,'Necromancer'),(6,'Mesmer'),(7,'Elementalist'),(8,'Assassin'),(9,'Ritualist'),(10,'Paragon'),(11,'Dervish');

CREATE TABLE IF NOT EXISTS gwtreasure_runes (
    rune_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    profession_id TINYINT UNSIGNED NOT NULL,
    rune_name VARCHAR(50) NOT NULL,
    PRIMARY KEY (rune_id), UNIQUE KEY uq_gwtreasure_rune (profession_id, rune_name),
    CONSTRAINT fk_gwtreasure_rune_profession FOREIGN KEY (profession_id) REFERENCES gwtreasure_professions(profession_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_runes (profession_id,rune_name) VALUES
(1,'Attunement'),(1,'Clarity'),(1,'Purity'),(1,'Recovery'),(1,'Restoration'),(1,'Vigor'),(1,'Vitae'),
(2,'Absorption'),(2,'Axe Mastery'),(2,'Hammer Mastery'),(2,'Strength'),(2,'Swordsmanship'),(2,'Tactics'),
(3,'Beast Mastery'),(3,'Expertise'),(3,'Marksmanship'),(3,'Wilderness Survival'),
(4,'Divine Favor'),(4,'Healing Prayers'),(4,'Protection Prayers'),(4,'Smiting Prayers'),
(5,'Blood Magic'),(5,'Curses'),(5,'Death Magic'),(5,'Soul Reaping'),
(6,'Domination Magic'),(6,'Fast Casting'),(6,'Illusion Magic'),(6,'Inspiration Magic'),
(7,'Air Magic'),(7,'Earth Magic'),(7,'Energy Storage'),(7,'Fire Magic'),(7,'Water Magic'),
(8,'Critical Strikes'),(8,'Dagger Mastery'),(8,'Deadly Arts'),(8,'Shadow Arts'),
(9,'Channeling Magic'),(9,'Communing'),(9,'Restoration Magic'),(9,'Spawning Power'),
(10,'Command'),(10,'Leadership'),(10,'Motivation'),(10,'Spear Mastery'),
(11,'Earth Prayers'),(11,'Mysticism'),(11,'Scythe Mastery'),(11,'Wind Prayers');

CREATE TABLE IF NOT EXISTS gwtreasure_insignias (
    insignia_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    profession_id TINYINT UNSIGNED NOT NULL,
    insignia_name VARCHAR(60) NOT NULL,
    PRIMARY KEY (insignia_id), UNIQUE KEY uq_gwtreasure_insignia (insignia_name),
    CONSTRAINT fk_gwtreasure_insignia_profession FOREIGN KEY (profession_id) REFERENCES gwtreasure_professions(profession_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO gwtreasure_insignias (profession_id,insignia_name) VALUES
(1,'Survivor Insignia'),(1,'Radiant Insignia'),(1,'Stalwart Insignia'),(1,'Brawler''s Insignia'),(1,'Blessed Insignia'),(1,'Herald''s Insignia'),(1,'Sentry''s Insignia'),
(2,'Knight''s Insignia'),(2,'Stonefist Insignia'),(2,'Dreadnought Insignia'),(2,'Sentinel''s Insignia'),(2,'Lieutenant''s Insignia'),
(3,'Frostbound Insignia'),(3,'Pyrebound Insignia'),(3,'Stormbound Insignia'),(3,'Scout''s Insignia'),(3,'Earthbound Insignia'),(3,'Beastmaster''s Insignia'),
(4,'Wanderer''s Insignia'),(4,'Disciple''s Insignia'),(4,'Anchorite''s Insignia'),
(5,'Bloodstained Insignia'),(5,'Tormentor''s Insignia'),(5,'Bonelace Insignia'),(5,'Minion Master''s Insignia'),(5,'Blighter''s Insignia'),(5,'Undertaker''s Insignia'),
(6,'Virtuoso''s Insignia'),(6,'Artificer''s Insignia'),(6,'Prodigy''s Insignia'),
(7,'Hydromancer Insignia'),(7,'Geomancer Insignia'),(7,'Pyromancer Insignia'),(7,'Aeromancer Insignia'),(7,'Prismatic Insignia'),
(8,'Vanguard''s Insignia'),(8,'Infiltrator''s Insignia'),(8,'Saboteur''s Insignia'),(8,'Nightstalker''s Insignia'),
(9,'Shaman''s Insignia'),(9,'Ghost Forge Insignia'),(9,'Mystic''s Insignia'),(10,'Centurion''s Insignia'),(11,'Windwalker Insignia'),(11,'Forsaken Insignia');

ALTER TABLE gwtreasure_history
    ADD COLUMN rarity_id TINYINT UNSIGNED DEFAULT NULL AFTER drop_description,
    ADD COLUMN requirement TINYINT UNSIGNED DEFAULT NULL AFTER rarity_id,
    ADD COLUMN weapon_type_id TINYINT UNSIGNED DEFAULT NULL AFTER requirement,
    ADD COLUMN attribute_id TINYINT UNSIGNED DEFAULT NULL AFTER weapon_type_id,
    ADD COLUMN item_name VARCHAR(150) DEFAULT NULL AFTER attribute_id,
    ADD COLUMN material_id TINYINT UNSIGNED DEFAULT NULL AFTER item_name,
    ADD COLUMN rune_id TINYINT UNSIGNED DEFAULT NULL AFTER material_id,
    ADD COLUMN insignia_id TINYINT UNSIGNED DEFAULT NULL AFTER rune_id,
    ADD KEY idx_gwtreasure_history_structured_drop (drop_type, weapon_type_id, attribute_id),
    ADD CONSTRAINT fk_gwtreasure_history_rarity FOREIGN KEY (rarity_id) REFERENCES gwtreasure_rarities(rarity_id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_gwtreasure_history_requirement FOREIGN KEY (requirement) REFERENCES gwtreasure_requirements(requirement) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_gwtreasure_history_weapon_type FOREIGN KEY (weapon_type_id) REFERENCES gwtreasure_weapon_types(weapon_type_id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_gwtreasure_history_attribute FOREIGN KEY (attribute_id) REFERENCES gwtreasure_attributes(attribute_id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_gwtreasure_history_material FOREIGN KEY (material_id) REFERENCES gwtreasure_materials(material_id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_gwtreasure_history_rune FOREIGN KEY (rune_id) REFERENCES gwtreasure_runes(rune_id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_gwtreasure_history_insignia FOREIGN KEY (insignia_id) REFERENCES gwtreasure_insignias(insignia_id) ON DELETE RESTRICT;
