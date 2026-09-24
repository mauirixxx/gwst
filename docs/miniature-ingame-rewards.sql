-- GWTTT in-game reward miniature catalog expansion
-- Apply once after miniature-tracker-schema.sql, miniature-tracker-seed.sql,
-- and miniature-admin-migration.sql.
--
-- Source: Guild Wars Wiki In-game Reward table supplied by the operator.
-- These miniatures can be dedicated in the Hall of Monuments, so they use the
-- normal miniature inventory model (Dedicated + On hand).

INSERT IGNORE INTO gwminiature_groups (groupkey, groupname, display_order, group_note) VALUES
('ingame', 'In-game Rewards', 90, 'Miniatures obtained from in-game rewards, events, PvP rewards, and anniversary quests. These miniatures can be dedicated in the Hall of Monuments.');

INSERT IGNORE INTO gwminiatures (mininame) VALUES
('Black Moa Chick'),
('Brown Rabbit'),
('Dhuum'),
('Forest Griffon'),
('Forgemaster'),
('Gwen Doll'),
('Ghozer Dhuum'),
('Kazhad Dhuum'),
('Madruk Dhuum'),
('Thul Za Dhuum'),
('Smite Crawler'),
('Wailing Lord'),
('Yakkington'),
('Ghost of Althea'),
('Undead Prince'),
('Mallyx'),
('Confessor Dorian'),
('Confessor Isaiah'),
('Ecclesiate Xun Rao'),
('Evennia'),
('Livia'),
('Peacekeeper Enforcer'),
('Princess Salma'),
('Minister Reiko'),
('Celestial Dog'),
('Celestial Dragon'),
('Celestial Horse'),
('Celestial Monkey'),
('Celestial Ox'),
('Celestial Pig'),
('Celestial Rabbit'),
('Celestial Rat'),
('Celestial Rooster'),
('Celestial Sheep'),
('Celestial Snake'),
('Celestial Tiger'),
('Legionnaire'),
('Polar Bear'),
('World-Famous Racing Beetle'),
('Guild Lord'),
('Ghostly Hero'),
('Ghostly Priest'),
('High Priest Zhang'),
('Rift Warden'),
('Greased Lightning'),
('Pig');

-- Several 20th Anniversary Quest rewards already exist in the catalog
-- (Asura, Ceratadon, Destroyer of Flesh, Grawl, Gray Giant, Island Guardian,
-- Kanaxai, Longhair Yeti, Mad King's Guard, Panda). INSERT IGNORE above preserves
-- the single shared miniature identity when a miniature belongs to multiple groups.

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0
FROM gwminiature_groups g
JOIN gwminiatures m
WHERE g.groupkey = 'ingame'
  AND m.mininame IN (
    'Black Moa Chick','Brown Rabbit','Dhuum','Forest Griffon','Forgemaster','Gwen Doll',
    'Ghozer Dhuum','Kazhad Dhuum','Madruk Dhuum','Thul Za Dhuum','Smite Crawler','Wailing Lord','Yakkington',
    'Ghost of Althea','Undead Prince','Mallyx',
    'Confessor Dorian','Confessor Isaiah','Ecclesiate Xun Rao','Evennia','Livia','Peacekeeper Enforcer','Princess Salma','Minister Reiko',
    'Celestial Dog','Celestial Dragon','Celestial Horse','Celestial Monkey','Celestial Ox','Celestial Pig','Celestial Rabbit','Celestial Rat',
    'Celestial Rooster','Celestial Sheep','Celestial Snake','Celestial Tiger','Legionnaire','Polar Bear','World-Famous Racing Beetle',
    'Guild Lord','Ghostly Hero','Ghostly Priest','High Priest Zhang','Rift Warden',
    'Asura','Ceratadon','Destroyer of Flesh','Grawl','Gray Giant','Greased Lightning','Island Guardian','Kanaxai','Longhair Yeti','Mad King''s Guard','Panda','Pig'
  );

-- Give newly-created entries the same default Wiki-link convention used by the
-- miniature admin migration. Any exceptional redirect can be corrected later in
-- the Miniature Editor without touching SQL.
UPDATE gwminiatures m
JOIN gwminiature_group_members gm ON gm.miniid = m.miniid
JOIN gwminiature_groups g ON g.groupid = gm.groupid AND g.groupkey = 'ingame'
SET m.wiki_url = CONCAT('https://wiki.guildwars.com/wiki/Miniature_', REPLACE(m.mininame, ' ', '_'))
WHERE m.wiki_url IS NULL OR m.wiki_url = '';
