-- ====================================================================
-- MIGRATION: 006_add_children_ages_to_bookings.sql
-- Description: Adds children_ages column to bookings table to store child ages (0-17)
-- ====================================================================

USE w_island_hub;

-- Add children_ages column to store child ages as JSON text if not already present
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS children_ages TEXT NULL AFTER children;
