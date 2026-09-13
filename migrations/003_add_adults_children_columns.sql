-- ====================================================================
-- MIGRATION: 003_add_adults_children_columns.sql
-- Description: Adds adults (18+), children (0-17), and rooms_count columns to bookings
-- ====================================================================

USE w_island_hub;

-- Add columns if not already present
ALTER TABLE ookings
ADD COLUMN IF NOT EXISTS dults INT(11) NOT NULL DEFAULT 1 AFTER guest_phone,
ADD COLUMN IF NOT EXISTS children INT(11) NOT NULL DEFAULT 0 AFTER dults,
ADD COLUMN IF NOT EXISTS ooms_count INT(11) NOT NULL DEFAULT 1 AFTER 
ights;

-- Update existing records with default values if null or zero
UPDATE ookings SET dults = 1 WHERE dults IS NULL OR dults <= 0;
UPDATE ookings SET children = 0 WHERE children IS NULL;
UPDATE ookings SET ooms_count = 1 WHERE ooms_count IS NULL OR ooms_count <= 0;
