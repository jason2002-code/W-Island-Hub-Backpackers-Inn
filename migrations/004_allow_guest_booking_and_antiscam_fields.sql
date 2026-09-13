-- ====================================================================
-- MIGRATION: 004_allow_guest_booking_and_antiscam_fields.sql
-- Description: Ensures bookings.user_id is nullable for guest checkout
--              and confirms indexes for guest reservation lookup.
-- ====================================================================

USE w_island_hub;

-- Allow user_id to be NULL for direct guest reservations without an account
ALTER TABLE `bookings` MODIFY COLUMN `user_id` INT(11) NULL;

-- Ensure foreign key constraint drops on delete set null (if existing allows)
-- Note: MySQL default in schema is CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

