-- GWTTT miniature tracker database foundation
-- Apply once to the gwst MariaDB database before testing the miniature tracker UI.
--
-- Important model detail: one physical miniature identity may appear in more than
-- one acquisition group. For example, seventh-year Birthday Presents reuse many
-- miniatures from years 1-5. The master catalog therefore does NOT store a single
-- category. Group membership is normalized separately so one inventory state is
-- shared everywhere that miniature is displayed.
--
-- A missing gwminiature_inventory row means Dedicated = No and On hand = 0.

CREATE TABLE IF NOT EXISTS gwminiatures (
    miniid INT UNSIGNED NOT NULL AUTO_INCREMENT,
    mininame VARCHAR(100) NOT NULL,
    PRIMARY KEY (miniid),
    UNIQUE KEY uq_gwminiatures_name (mininame)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gwminiature_groups (
    groupid SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    groupkey VARCHAR(32) NOT NULL,
    groupname VARCHAR(64) NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    group_note VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (groupid),
    UNIQUE KEY uq_gwminiature_groups_key (groupkey),
    KEY idx_gwminiature_groups_order (display_order, groupname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gwminiature_group_members (
    groupid SMALLINT UNSIGNED NOT NULL,
    miniid INT UNSIGNED NOT NULL,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (groupid, miniid),
    KEY idx_gwminiature_group_members_miniid (miniid),
    KEY idx_gwminiature_group_members_order (groupid, display_order, miniid),
    CONSTRAINT fk_gwminiature_group_members_group
        FOREIGN KEY (groupid) REFERENCES gwminiature_groups (groupid)
        ON DELETE CASCADE,
    CONSTRAINT fk_gwminiature_group_members_mini
        FOREIGN KEY (miniid) REFERENCES gwminiatures (miniid)
        ON DELETE CASCADE
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
