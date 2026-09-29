-- ========================================================
-- Migration 007: User Archiving (Soft Delete)
-- ========================================================
-- Adds archive support to the `users` table so that removing
-- a user from the admin panel archives them instead of
-- destroying their record and orphaning bookings/reports/reviews.
--
-- Permanent deletion remains possible, but only from the
-- Archive view and only as an explicit admin action.
-- ========================================================

USE `w_island_hub`;

-- Archive flag (0 = active, 1 = archived)
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `is_archived` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role`;

-- Timestamp of when the account was archived
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `archived_at` DATETIME NULL DEFAULT NULL AFTER `is_archived`;

-- Speeds up the Active / Archived list filtering
ALTER TABLE `users`
    ADD INDEX IF NOT EXISTS `idx_users_is_archived` (`is_archived`);
