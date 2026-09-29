-- Create Database
CREATE DATABASE IF NOT EXISTS w_island_hub;
USE w_island_hub;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(30) DEFAULT NULL,
  `emergency_relation` varchar(50) DEFAULT NULL,
  `travel_preferences` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `bio` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_is_archived` (`is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'Guest User', 'guest@wisland.com', 'guest123');

INSERT INTO `admins` (`id`, `name`, `email`, `password`) VALUES
(1, 'Admin', 'admin@wisland.com', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `max_capacity` int(11) NOT NULL DEFAULT 2,
  `image` varchar(255) NOT NULL,
  `description` text,
  `amenities` json DEFAULT NULL,
  `status` enum('Available','Occupied','Maintenance') DEFAULT 'Available',
  `rating` decimal(3,1) DEFAULT '5.0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `type`, `price`, `capacity`, `image`, `description`, `amenities`, `rating`) VALUES
(1, 'Backpacker Dorm', 'Dorm', 15.00, 1, 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'Cozy bunk in a mixed dorm with A/C and locker.', '["WiFi", "A/C", "Shared Bath", "Locker"]', 4.5),
(2, 'Standard Double', 'Standard', 45.00, 2, 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'Private room with queen bed and garden view.', '["WiFi", "A/C", "Private Bath", "TV"]', 4.7),
(3, 'Ocean Deluxe', 'Deluxe', 85.00, 2, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'Premium room with balcony overlooking the sea.', '["WiFi", "A/C", "Bath", "Balcony", "Breakfast"]', 4.9),
(4, 'Family Suite', 'Family', 120.00, 4, 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'Spacious suite perfect for families or groups.', '["WiFi", "Kitchenette", "Living Area", "2 Baths"]', 4.8);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref_id` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(50) DEFAULT NULL,
  `adults` int(11) NOT NULL DEFAULT 1,
  `children` int(11) NOT NULL DEFAULT 0,
  `rooms_count` int(11) NOT NULL DEFAULT 1,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `expected_arrival_time` time DEFAULT NULL,
  `nights` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'full_gcash',
  `gcash_reference_no` varchar(100) DEFAULT NULL,
  `paymongo_session_id` varchar(150) DEFAULT NULL,
  `paymongo_payment_id` varchar(150) DEFAULT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `booking_status` enum('pending_verification','confirmed','deposit_paid','pending_arrival','completed','cancelled_expired','no_show') NOT NULL DEFAULT 'pending_verification',
  `expires_at` datetime DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_id` (`ref_id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`),
  KEY `booking_status` (`booking_status`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `type` enum('feedback','complaint','suggestion') DEFAULT 'feedback',
  `status` enum('Pending','Reviewed','Resolved') DEFAULT 'Pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `carousel_sliders`
--

CREATE TABLE IF NOT EXISTS `carousel_sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `type` enum('hero','marquee') DEFAULT 'hero',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `carousel_sliders`
--

INSERT INTO `carousel_sliders` (`id`, `title`, `image_url`, `type`, `sort_order`, `is_active`) VALUES
(1, 'Hero Welcome', 'assets/image/background.jpg', 'hero', 1, 1),
(2, 'Hero Room', 'assets/image/69b3bfd75e102.jpg', 'hero', 2, 1),
(3, 'Hero Gallery', 'assets/image/69b3c84d5988d.jpg', 'hero', 3, 1),
(4, 'Gallery 1', 'assets/image/background.jpg', 'marquee', 1, 1),
(5, 'Gallery 2', 'assets/image/69b3bfd75e102.jpg', 'marquee', 2, 1),
(6, 'Gallery 3', 'assets/image/69b3c84d5988d.jpg', 'marquee', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE IF NOT EXISTS `amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'fas fa-star',
  `category` varchar(50) NOT NULL DEFAULT 'General',
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`) VALUES
(1, 'Beachfront & Coral Coast', 'Steps away from pristine white sands. Enjoy early morning ocean dips, snorkeling among reef life, and front-row seats to world-famous sunsets.', 'fas fa-umbrella-beach', 'Location', 'assets/image/amenity_beachfront.jpg', 1, 1),
(2, 'Nomad-Grade Starlink WiFi', 'Lightning-fast, reliable satellite internet across all dorms, private rooms, and chill lounges. Seamless for remote work, video calls, and streaming.', 'fas fa-wifi', 'Connectivity', 'assets/image/amenity_starlink.jpg', 2, 1),
(3, 'Sunset Social Lounge & Bar', 'Vibrant open-air communal hub with board games, craft beers, refreshing tropical fruit shakes, and evening acoustic music jams.', 'fas fa-cocktail', 'Social', 'assets/image/amenity_lounge_bar.jpg', 3, 1),
(4, 'Fresh & Easy Laundry Hub', 'Travel light and stay fresh! Enjoy our fast, hassle-free laundry service designed for adventurers and island explorers. Drop off beachwear and pick up clean clothes ready for your journey.', 'fas fa-soap', 'Comfort', 'assets/image/amenity_6aa79d1dbec73.jpg', 4, 1),
(5, 'Keycard Security & Lockers', 'Travel with total peace of mind. Every dorm guest receives an oversized security locker, personal bedside power socket, and reading lamp.', 'fas fa-key', 'Security', 'assets/image/amenity_lockers.jpg', 5, 1),
(6, 'Free Coffee & Water Refills', 'Complimentary fresh-brewed local island coffee every morning. Unlimited filtered cold drinking water stations to keep you energized and eco-friendly.', 'fas fa-mug-hot', 'Dining', 'assets/image/amenity_coffee_water.jpg', 6, 1);


-- --------------------------------------------------------
--
-- Table structure for table `reviews`
--

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `rating` decimal(3,1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `comment` text,
`status` enum('approved','rejected') DEFAULT 'approved',
  `visible` tinyint(1) DEFAULT 1,
  `admin_reply` text NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Dynamic Room Capacity Triggers
-- --------------------------------------------------------

DELIMITER $$

CREATE TRIGGER `trg_rooms_before_insert`
BEFORE INSERT ON `rooms`
FOR EACH ROW
BEGIN
    IF NEW.max_capacity IS NULL OR NEW.max_capacity <= 0 THEN
        SET NEW.max_capacity = GREATEST(1, COALESCE(NEW.capacity, 2));
    END IF;
END$$

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

CREATE TRIGGER `trg_bookings_after_update`
AFTER UPDATE ON `bookings`
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
