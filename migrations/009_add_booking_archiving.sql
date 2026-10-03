-- ========================================================
-- Migration 009: Booking Archiving (Soft Delete)
-- ========================================================
-- Adds archive support to the `bookings` table so that
-- removing or managing past/cancelled reservations archives
-- them instead of destroying financial, receipt, and audit records.
--
-- Permanent deletion remains possible, but only from the
-- Archive view and only as an explicit admin action.
-- ========================================================

USE `w_island_hub`;

-- Archive flag (0 = active, 1 = archived)
ALTER TABLE `bookings`
    ADD COLUMN IF NOT EXISTS `is_archived` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

-- Timestamp of when the booking was archived
ALTER TABLE `bookings`
    ADD COLUMN IF NOT EXISTS `archived_at` DATETIME NULL DEFAULT NULL AFTER `is_archived`;

-- Speeds up the Active / Archived list filtering
ALTER TABLE `bookings`
    ADD INDEX IF NOT EXISTS `idx_bookings_is_archived` (`is_archived`);
