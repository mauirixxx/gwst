-- GWST production schema hardening migration
-- Target: legacy GWST schema on MariaDB 10.11+
-- IMPORTANT: back up the database and test this migration on a production copy first.
-- This migration preserves existing rows; it does not reload reference data.
--
-- Deliberately retained:
--   * gwstats.charid = 0 for account-wide titles, so charid is not foreign-keyed.
--   * userinfo.prefaccid/prefcharid = 0 sentinel values, so those columns are not foreign-keyed.

SET NAMES utf8mb4;

-- Convert legacy latin1 tables to the charset used by new installations.
ALTER TABLE userinfo      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE gwaccounts    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE gwchars       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE gwprofessions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE gwtitles      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE gwsubtitles   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE gwstats       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tighten columns that the production-data audit proved contain no NULL values.
ALTER TABLE userinfo
  MODIFY username VARCHAR(30) NOT NULL,
  MODIFY userpass VARCHAR(255) NOT NULL,
  MODIFY usermail VARCHAR(50) NOT NULL,
  MODIFY admin TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = normal user, 1 = administrator',
  MODIFY prefaccid INT NOT NULL DEFAULT 0 COMMENT 'preferred Guild Wars account; 0 = none selected',
  MODIFY prefaccname VARCHAR(50) NOT NULL DEFAULT 'No default selected',
  MODIFY prefcharid INT NOT NULL DEFAULT 0 COMMENT 'preferred character; 0 = none selected',
  MODIFY prefcharname VARCHAR(19) NOT NULL DEFAULT 'No default selected',
  ADD UNIQUE KEY uq_userinfo_username (username),
  ADD UNIQUE KEY uq_userinfo_usermail (usermail),
  ADD CONSTRAINT chk_userinfo_admin CHECK (admin IN (0,1));

ALTER TABLE gwaccounts
  MODIFY userid INT NOT NULL,
  MODIFY accemail VARCHAR(50) NOT NULL,
  ADD KEY idx_gwaccounts_userid (userid);

ALTER TABLE gwchars
  MODIFY accid INT NOT NULL,
  MODIFY userid INT NOT NULL,
  MODIFY charname VARCHAR(19) NOT NULL,
  MODIFY profid INT NOT NULL,
  ADD KEY idx_gwchars_userid (userid),
  ADD KEY idx_gwchars_accid (accid),
  ADD KEY idx_gwchars_profid (profid);

ALTER TABLE gwprofessions
  MODIFY profession VARCHAR(12) NOT NULL,
  MODIFY profcolor CHAR(4) NOT NULL,
  ADD UNIQUE KEY uq_gwprofessions_profession (profession);

ALTER TABLE gwtitles
  MODIFY titlename VARCHAR(40) NOT NULL,
  MODIFY titletype TINYINT UNSIGNED NOT NULL COMMENT '0 = account, 1 = character',
  MODIFY titlemaxrank INT NOT NULL,
  MODIFY autofilled TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = no, 1 = yes',
  MODIFY gwamm TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = no, 1 = yes',
  ADD UNIQUE KEY uq_gwtitles_titlename (titlename),
  ADD CONSTRAINT chk_gwtitles_titletype CHECK (titletype IN (0,1)),
  ADD CONSTRAINT chk_gwtitles_autofilled CHECK (autofilled IN (0,1)),
  ADD CONSTRAINT chk_gwtitles_gwamm CHECK (gwamm IN (0,1));

ALTER TABLE gwsubtitles
  MODIFY titlenameid INT NOT NULL,
  MODIFY stname VARCHAR(50) NOT NULL,
  MODIFY stpoints INT NOT NULL,
  MODIFY strank INT NOT NULL,
  ADD KEY idx_gwsubtitles_titlenameid (titlenameid),
  ADD UNIQUE KEY uq_gwsubtitles_title_rank (titlenameid, strank);

ALTER TABLE gwstats
  MODIFY titlenameid INT NOT NULL,
  MODIFY stnameid INT DEFAULT NULL,
  MODIFY percent INT DEFAULT NULL,
  MODIFY gwamm TINYINT UNSIGNED NOT NULL DEFAULT 0,
  MODIFY charid INT NOT NULL DEFAULT 0 COMMENT '0 denotes an account-wide title',
  MODIFY accid INT NOT NULL,
  MODIFY userid INT NOT NULL,
  ADD PRIMARY KEY (userid, accid, charid, titlenameid),
  ADD KEY idx_gwstats_accid (accid),
  ADD KEY idx_gwstats_titlenameid (titlenameid),
  ADD KEY idx_gwstats_stnameid (stnameid),
  ADD CONSTRAINT chk_gwstats_gwamm CHECK (gwamm IN (0,1));

-- Add relational enforcement only after charset/column/index changes succeed.
ALTER TABLE gwaccounts
  ADD CONSTRAINT fk_gwaccounts_user
    FOREIGN KEY (userid) REFERENCES userinfo (userid) ON DELETE CASCADE;

ALTER TABLE gwchars
  ADD CONSTRAINT fk_gwchars_user
    FOREIGN KEY (userid) REFERENCES userinfo (userid) ON DELETE CASCADE,
  ADD CONSTRAINT fk_gwchars_account
    FOREIGN KEY (accid) REFERENCES gwaccounts (accid) ON DELETE CASCADE,
  ADD CONSTRAINT fk_gwchars_profession
    FOREIGN KEY (profid) REFERENCES gwprofessions (profid);

ALTER TABLE gwsubtitles
  ADD CONSTRAINT fk_gwsubtitles_title
    FOREIGN KEY (titlenameid) REFERENCES gwtitles (titlenameid) ON DELETE CASCADE;

ALTER TABLE gwstats
  ADD CONSTRAINT fk_gwstats_user
    FOREIGN KEY (userid) REFERENCES userinfo (userid) ON DELETE CASCADE,
  ADD CONSTRAINT fk_gwstats_account
    FOREIGN KEY (accid) REFERENCES gwaccounts (accid) ON DELETE CASCADE,
  ADD CONSTRAINT fk_gwstats_title
    FOREIGN KEY (titlenameid) REFERENCES gwtitles (titlenameid) ON DELETE CASCADE,
  ADD CONSTRAINT fk_gwstats_subtitle
    FOREIGN KEY (stnameid) REFERENCES gwsubtitles (stnameid) ON DELETE SET NULL;
