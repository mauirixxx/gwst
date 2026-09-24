-- GWTTT miniature tracker seed data
-- Source: user-supplied Guild Wars Wiki Miniature PDF, current as shown in that source.
-- This first seed covers the requested Birthday Year 1-7 groups plus Historical / Unobtainable.
-- Year 6 intentionally has no members: the sixth-year present contains a tonic, not a miniature.
-- A miniature can belong to multiple groups; its inventory/dedication state remains one shared row.

INSERT IGNORE INTO gwminiature_groups (groupkey, groupname, display_order, group_note) VALUES
('year1', 'Year 1', 10, NULL),
('year2', 'Year 2', 20, NULL),
('year3', 'Year 3', 30, NULL),
('year4', 'Year 4', 40, NULL),
('year5', 'Year 5', 50, NULL),
('year6', 'Year 6', 60, 'Sixth-year Birthday Presents contain a tonic rather than a miniature.'),
('year7', 'Year 7', 70, 'Seventh-year Birthday Presents can contain Purple, Gold, or Green miniatures from years 1-5 plus several previously exclusive miniatures.'),
('historical', 'Historical / Unobtainable', 80, NULL);

INSERT IGNORE INTO gwminiatures (mininame) VALUES
('Fungal Wallow'),('Hydra'),('Jade Armor'),('Jungle Troll'),('Necrid Horseman'),('Siege Turtle'),('Temple Guardian'),('Whiptail Devourer'),('Burning Titan'),('Charr Shaman'),('Kirin'),('Prince Rurik'),('Shiro'),('Bone Dragon'),
('Aatxe'),('Fire Imp'),('Harpy Ranger'),('Heket Warrior'),('Juggernaut'),('Mandragor Imp'),('Thorn Wolf'),('Wind Rider'),('Elf'),('Koss'),('Palawa Joko'),('Lich'),('Water Djinn'),('Gwen'),
('Abyssal'),('Cave Spider'),('Cloudtouched Simian'),('Forest Minotaur'),('Irukandji'),('Mursaat'),('Raptor'),('Roaring Ether'),('Freezie'),('Nornbear'),('Ooze'),('Black Beast of Aaaaarrrrrrggghhh'),('White Rabbit'),('Mad King Thorn'),
('Abomination'),('Desert Griffon'),('Dredge Brute'),('Krait Neoss'),('Kveldulf'),('Flowstone Elemental'),('Jora'),('Nian'),('Dagnar Stonepate'),('Flame Djinn'),('Eye of Janthir'),('Quetzal Sly'),('Terrorweb Dryder'),('Word of Madness'),
('Cobalt Scabara'),('Fire Drake'),('Ophil Nahualli'),('Scourge Manta'),('Seer'),('Shard Wolf'),('Siege Devourer'),('Summit Giant Herder'),('Candysmith Marley'),('Oola'),('Ventari'),('King Adelbern'),('Zhu Hanuku'),('M.O.X.'),
('Naga Raincaller'),('Oni'),('Shiro''ken Assassin'),('Zhed Shadowhoof'),('Vizu'),
('Kuunavang'),('Varesh Ossa'),('Asura'),('Destroyer of Flesh'),('Gray Giant'),('Grawl'),('Ceratadon'),('Longhair Yeti'),('Island Guardian'),('Mad King''s Guard'),('Panda'),('Kanaxai');

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='year1' AND m.mininame IN ('Fungal Wallow','Hydra','Jade Armor','Jungle Troll','Necrid Horseman','Siege Turtle','Temple Guardian','Whiptail Devourer','Burning Titan','Charr Shaman','Kirin','Prince Rurik','Shiro','Bone Dragon');

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='year2' AND m.mininame IN ('Aatxe','Fire Imp','Harpy Ranger','Heket Warrior','Juggernaut','Mandragor Imp','Thorn Wolf','Wind Rider','Elf','Koss','Palawa Joko','Lich','Water Djinn','Gwen');

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='year3' AND m.mininame IN ('Abyssal','Cave Spider','Cloudtouched Simian','Forest Minotaur','Irukandji','Mursaat','Raptor','Roaring Ether','Freezie','Nornbear','Ooze','Black Beast of Aaaaarrrrrrggghhh','White Rabbit','Mad King Thorn');

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='year4' AND m.mininame IN ('Abomination','Desert Griffon','Dredge Brute','Krait Neoss','Kveldulf','Flowstone Elemental','Jora','Nian','Dagnar Stonepate','Flame Djinn','Eye of Janthir','Quetzal Sly','Terrorweb Dryder','Word of Madness');

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='year5' AND m.mininame IN ('Cobalt Scabara','Fire Drake','Ophil Nahualli','Scourge Manta','Seer','Shard Wolf','Siege Devourer','Summit Giant Herder','Candysmith Marley','Oola','Ventari','King Adelbern','Zhu Hanuku','M.O.X.');

-- Year 6 deliberately receives no membership rows.

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='year7' AND m.mininame IN (
'Burning Titan','Candysmith Marley','Charr Shaman','Elf','Flowstone Elemental','Freezie','Jora','Kirin','Koss','Nian','Nornbear','Oola','Ooze','Palawa Joko','Ventari',
'Black Beast of Aaaaarrrrrrggghhh','Dagnar Stonepate','Flame Djinn','King Adelbern','Lich','Prince Rurik','Shiro','Water Djinn','White Rabbit','Zhu Hanuku',
'Bone Dragon','Eye of Janthir','Gwen','M.O.X.','Mad King Thorn',
'Naga Raincaller','Oni','Shiro''ken Assassin','Zhed Shadowhoof','Vizu');

INSERT IGNORE INTO gwminiature_group_members (groupid, miniid, display_order)
SELECT g.groupid, m.miniid, 0 FROM gwminiature_groups g JOIN gwminiatures m
WHERE g.groupkey='historical' AND m.mininame IN (
'Kuunavang','Varesh Ossa','Asura','Destroyer of Flesh','Gray Giant','Grawl','Ceratadon',
'Naga Raincaller','Oni','Longhair Yeti','Shiro''ken Assassin','Zhed Shadowhoof','Island Guardian','Mad King''s Guard','Panda','Vizu','Kanaxai');
