-- ========================================================
-- MIGRATION: Dynamic Room Capacity via MySQL Triggers
-- Automatically adjusts rooms.capacity based on bookings
-- ========================================================

USE w_island_hub;

-- 1. Add max_capacity to store total physical bed limit
ALTER TABLE `rooms` 
ADD COLUMN IF NOT EXISTS `max_capacity` INT(11) NOT NULL DEFAULT 2 AFTER `capacity`;

-- Ensure all existing rooms have valid max_capacity values
UPDATE `rooms` 
SET `max_capacity` = GREATEST(1, `capacity`) 
WHERE `max_capacity` IS NULL OR `max_capacity` = 0;

-- 2. Drop legacy triggers if present
DROP TRIGGER IF EXISTS `trg_rooms_before_insert`;
DROP TRIGGER IF EXISTS `trg_rooms_before_update`;
DROP TRIGGER IF EXISTS `trg_bookings_after_insert`;
DROP TRIGGER IF EXISTS `trg_bookings_after_update`;
DROP TRIGGER IF EXISTS `trg_bookings_after_delete`;

DELIMITER $$

-- 3. Trigger: Automatically initialize max_capacity on room insert
CREATE TRIGGER `trg_rooms_before_insert`
BEFORE INSERT ON `rooms`
FOR EACH ROW
BEGIN
    IF NEW.max_capacity IS NULL OR NEW.max_capacity <= 0 THEN
        SET NEW.max_capacity = GREATEST(1, COALESCE(NEW.capacity, 2));
    END IF;
END$$

-- 4. Trigger: Update room available capacity after a new booking is made
CREATE TRIGGER `trg_bookings_after_insert`
AFTER INSERT ON `bookings`
FOR EACH ROW
BEGIN
    UPDATE `rooms` r
    SET r.capacity = GREATEST(0, r.max_capacity - (
        SELECT COUNT(*)
        FROM `bookings` b
        WHERE b.room_id = r.id
          AND b.status IN ('Pending', 'Confirmed')
          AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
          AND CURRENT_DATE() >= COALESCE(b.check_in_date, b.check_in)
          AND CURRENT_DATE() < COALESCE(b.check_out_date, b.check_out)
    ))
    WHERE r.id = NEW.room_id;
END$$

-- 5. Trigger: Update room available capacity when booking status or dates change
CREATE TRIGGER `trg_bookings_after_update`
AFTER UPDATE ON `bookings`
FOR EACH ROW
BEGIN
    -- Update new room_id
    UPDATE `rooms` r
    SET r.capacity = GREATEST(0, r.max_capacity - (
        SELECT COUNT(*)
        FROM `bookings` b
        WHERE b.room_id = r.id
          AND b.status IN ('Pending', 'Confirmed')
          AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
          AND CURRENT_DATE() >= COALESCE(b.check_in_date, b.check_in)
          AND CURRENT_DATE() < COALESCE(b.check_out_date, b.check_out)
    ))
    WHERE r.id = NEW.room_id;

    -- If room_id changed, also recalculate the old room
    IF OLD.room_id != NEW.room_id THEN
        UPDATE `rooms` r
        SET r.capacity = GREATEST(0, r.max_capacity - (
            SELECT COUNT(*)
            FROM `bookings` b
            WHERE b.room_id = r.id
              AND b.status IN ('Pending', 'Confirmed')
              AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
              AND CURRENT_DATE() >= COALESCE(b.check_in_date, b.check_in)
              AND CURRENT_DATE() < COALESCE(b.check_out_date, b.check_out)
        ))
        WHERE r.id = OLD.room_id;
    END IF;
END$$

-- 6. Trigger: Update room available capacity when a booking is deleted
CREATE TRIGGER `trg_bookings_after_delete`
AFTER DELETE ON `bookings`
FOR EACH ROW
BEGIN
    UPDATE `rooms` r
    SET r.capacity = GREATEST(0, r.max_capacity - (
        SELECT COUNT(*)
        FROM `bookings` b
        WHERE b.room_id = r.id
          AND b.status IN ('Pending', 'Confirmed')
          AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
          AND CURRENT_DATE() >= COALESCE(b.check_in_date, b.check_in)
          AND CURRENT_DATE() < COALESCE(b.check_out_date, b.check_out)
    ))
    WHERE r.id = OLD.room_id;
END$$

DELIMITER ;

-- 7. Execute initial sync for all rooms based on current active stays
UPDATE `rooms` r
SET r.capacity = GREATEST(0, r.max_capacity - (
    SELECT COUNT(*)
    FROM `bookings` b
    WHERE b.room_id = r.id
      AND b.status IN ('Pending', 'Confirmed')
      AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
      AND CURRENT_DATE() >= COALESCE(b.check_in_date, b.check_in)
      AND CURRENT_DATE() < COALESCE(b.check_out_date, b.check_out)
));

