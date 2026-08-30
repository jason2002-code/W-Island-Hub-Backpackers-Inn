-- Migration 001: Multi-tier Reservation Payment Engine Schema
-- Adds support for Full GCash, 30% Deposit GCash, Pay at Hotel, receipt proofs, arrival times, and hold expiry.

-- 1. Ensure all required columns exist in bookings table
ALTER TABLE `bookings`
    ADD COLUMN IF NOT EXISTS `guest_email` varchar(100) DEFAULT NULL AFTER `guest_name`,
    ADD COLUMN IF NOT EXISTS `guest_phone` varchar(50) DEFAULT NULL AFTER `guest_email`,
    ADD COLUMN IF NOT EXISTS `check_in_date` date DEFAULT NULL AFTER `guest_phone`,
    ADD COLUMN IF NOT EXISTS `check_out_date` date DEFAULT NULL AFTER `check_in_date`,
    ADD COLUMN IF NOT EXISTS `expected_arrival_time` time DEFAULT NULL AFTER `check_out_date`,
    ADD COLUMN IF NOT EXISTS `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `nights`,
    ADD COLUMN IF NOT EXISTS `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`,
    ADD COLUMN IF NOT EXISTS `balance_due` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`,
    ADD COLUMN IF NOT EXISTS `gcash_reference_no` varchar(100) DEFAULT NULL AFTER `payment_method`,
    ADD COLUMN IF NOT EXISTS `receipt_url` varchar(255) DEFAULT NULL AFTER `gcash_reference_no`,
    ADD COLUMN IF NOT EXISTS `booking_status` enum('pending_verification','confirmed','deposit_paid','pending_arrival','completed','cancelled_expired','no_show') NOT NULL DEFAULT 'pending_verification' AFTER `status`,
    ADD COLUMN IF NOT EXISTS `expires_at` datetime DEFAULT NULL AFTER `booking_status`,
    ADD COLUMN IF NOT EXISTS `special_requests` text DEFAULT NULL AFTER `expires_at`,
    ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 2. Populate/sync existing data so no past bookings are broken
UPDATE `bookings` SET `check_in_date` = `check_in` WHERE `check_in_date` IS NULL AND `check_in` IS NOT NULL;
UPDATE `bookings` SET `check_out_date` = `check_out` WHERE `check_out_date` IS NULL AND `check_out` IS NOT NULL;
UPDATE `bookings` SET `total_amount` = `total_price` WHERE `total_amount` = 0.00 AND `total_price` > 0.00;
UPDATE `bookings` SET `total_price` = `total_amount` WHERE (`total_price` IS NULL OR `total_price` = 0.00) AND `total_amount` > 0.00;

-- 3. Update legacy status mappings for booking_status
UPDATE `bookings` SET `booking_status` = 'confirmed', `paid_amount` = `total_amount`, `balance_due` = 0.00 WHERE `status` = 'Confirmed' AND `booking_status` = 'pending_verification';
UPDATE `bookings` SET `booking_status` = 'cancelled_expired' WHERE `status` = 'Cancelled';
UPDATE `bookings` SET `booking_status` = 'pending_arrival', `balance_due` = `total_amount` WHERE `status` = 'Pending' AND (`payment_method` LIKE '%Hotel%' OR `payment_method` = 'pay_at_hotel');

