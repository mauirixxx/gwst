-- GWTTT sixth-year birthday tonic tracking
-- Apply after miniature-tracker-schema.sql.
-- Sixth-year Birthday Presents contain everlasting tonics rather than miniatures.

CREATE TABLE IF NOT EXISTS gwtonics (
    tonicid INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tonicname VARCHAR(120) NOT NULL,
    rarity ENUM('White','Purple','Gold','Green') NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (tonicid),
    UNIQUE KEY uq_gwtonics_name (tonicname),
    KEY idx_gwtonics_order (rarity, display_order, tonicname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gwtonic_inventory (
    userid INT NOT NULL,
    accid INT NOT NULL,
    tonicid INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userid, accid, tonicid),
    KEY idx_gwtonic_inventory_accid (accid),
    KEY idx_gwtonic_inventory_tonicid (tonicid),
    CONSTRAINT fk_gwtonic_inventory_tonic
        FOREIGN KEY (tonicid) REFERENCES gwtonics (tonicid)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO gwtonics (tonicname, rarity, display_order) VALUES
('Everlasting Acolyte Jin Tonic','White',10),
('Everlasting Acolyte Sousuke Tonic','White',20),
('Everlasting Dunkoro Tonic','White',30),
('Everlasting Goren Tonic','White',40),
('Everlasting Hayda Tonic','White',50),
('Everlasting Kahmu Tonic','White',60),
('Everlasting Livia Tonic','White',70),
('Everlasting Margrid the Sly Tonic','White',80),
('Everlasting Melonni Tonic','White',90),
('Everlasting Morgahn Tonic','White',100),
('Everlasting Norgu Tonic','White',110),
('Everlasting Olias Tonic','White',120),
('Everlasting Tahlkora Tonic','White',130),
('Everlasting Vekk Tonic','White',140),
('Everlasting Xandra Tonic','White',150),
('Everlasting Zenmai Tonic','White',160),
('Everlasting Anton Tonic','Purple',170),
('Everlasting Jora Tonic','Purple',180),
('Everlasting Koss Tonic','Purple',190),
('Everlasting M.O.X. Tonic','Purple',200),
('Everlasting Master of Whispers Tonic','Purple',210),
('Everlasting Ogden Stonehealer Tonic','Purple',220),
('Everlasting Pyre Fierceshot Tonic','Purple',230),
('Everlasting Queen Salma Tonic','Purple',240),
('Everlasting Razah Tonic','Purple',250),
('Everlasting Zhed Shadowhoof Tonic','Purple',260),
('Everlasting Gwen Tonic','Gold',270),
('Everlasting Keiran Thackeray Tonic','Gold',280),
('Everlasting Miku Tonic','Gold',290),
('Everlasting Shiro Tonic','Gold',300),
('Everlasting Prince Rurik Tonic','Gold',310),
('Everlasting Destroyer Tonic','Green',320),
('Everlasting Kuunavang Tonic','Green',330),
('Everlasting Margonite Tonic','Green',340),
('Everlasting Slightly Mad King Tonic','Green',350)
ON DUPLICATE KEY UPDATE rarity = VALUES(rarity), display_order = VALUES(display_order);
