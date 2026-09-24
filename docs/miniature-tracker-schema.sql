-- GWTTT miniature tracker database foundation
-- Apply once to the gwst MariaDB database before testing the miniature tracker UI.
-- The catalog is intentionally separate from per-account state. A missing state
-- row means Dedicated = No and On hand = 0.

CREATE TABLE IF NOT EXISTS gwminiatures (
    miniid INT UNSIGNED NOT NULL AUTO_INCREMENT,
    mininame VARCHAR(100) NOT NULL,
    category VARCHAR(32) NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (miniid),
    UNIQUE KEY uq_gwminiatures_name (mininame),
    KEY idx_gwminiatures_category_order (category, display_order, mininame)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gwminiature_inventory (
    userid INT NOT NULL,
    accid INT NOT NULL,
    miniid INT UNSIGNED NOT NULL,
    dedicated TINYINT(1) NOT NULL DEFAULT 0,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (userid, accid, miniid),
    KEY idx_gwminiature_inventory_accid (accid),
    KEY idx_gwminiature_inventory_miniid (miniid),
    CONSTRAINT fk_gwminiature_inventory_mini
        FOREIGN KEY (miniid) REFERENCES gwminiatures (miniid)
        ON DELETE CASCADE,
    CONSTRAINT chk_gwminiature_inventory_dedicated
        CHECK (dedicated IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed rows are added separately from the supplied Guild Wars Wiki miniature
-- source so the schema can be reviewed/applied independently from catalog data.
