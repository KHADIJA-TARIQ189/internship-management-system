-- Run ONLY if you already have an existing internship_db (new installs use database.sql)
USE internship_db;
ALTER TABLE users
  ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN verify_hash CHAR(64) NULL,
  ADD COLUMN verify_expires DATETIME NULL,
  ADD COLUMN verify_sent_at DATETIME NULL;
UPDATE users SET email_verified=1 WHERE role='admin';
ALTER TABLE applications ADD COLUMN cover_letter TEXT NULL AFTER cv;
