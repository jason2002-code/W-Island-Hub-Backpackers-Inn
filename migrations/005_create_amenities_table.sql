-- Migration: Create Amenities Table
-- Description: Stores inn amenities with custom icon, category, description, sort order, and active state.

USE `w_island_hub`;

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

-- Seed initial signature amenities if table is empty
INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`)
SELECT 1, 'Beachfront & Coral Coast', 'Steps away from pristine white sands. Enjoy early morning ocean dips, snorkeling among reef life, and front-row seats to world-famous sunsets.', 'fas fa-umbrella-beach', 'Location', 'assets/image/amenity_beachfront.jpg', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `amenities` WHERE `id` = 1);

INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`)
SELECT 2, 'Nomad-Grade Starlink WiFi', 'Lightning-fast, reliable satellite internet across all dorms, private rooms, and chill lounges. Seamless for remote work, video calls, and streaming.', 'fas fa-wifi', 'Connectivity', 'assets/image/amenity_starlink.jpg', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM `amenities` WHERE `id` = 2);

INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`)
SELECT 3, 'Sunset Social Lounge & Bar', 'Vibrant open-air communal hub with board games, craft beers, refreshing tropical fruit shakes, and evening acoustic music jams.', 'fas fa-cocktail', 'Social', 'assets/image/amenity_lounge_bar.jpg', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM `amenities` WHERE `id` = 3);

INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`)
SELECT 4, 'Scooter Rentals & Island Tours', 'Rent scooters right from the front desk. Book hassle-free tours to Chocolate Hills, Tarsier Sanctuaries, and dolphin watching expeditions.', 'fas fa-motorcycle', 'Adventures', 'assets/image/amenity_scooter_tour.jpg', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM `amenities` WHERE `id` = 4);

INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`)
SELECT 5, 'Keycard Security & Lockers', 'Travel with total peace of mind. Every dorm guest receives an oversized security locker, personal bedside power socket, and reading lamp.', 'fas fa-key', 'Security', 'assets/image/amenity_lockers.jpg', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM `amenities` WHERE `id` = 5);

INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`)
SELECT 6, 'Free Coffee & Water Refills', 'Complimentary fresh-brewed local island coffee every morning. Unlimited filtered cold drinking water stations to keep you energized and eco-friendly.', 'fas fa-mug-hot', 'Dining', 'assets/image/amenity_coffee_water.jpg', 6, 1
WHERE NOT EXISTS (SELECT 1 FROM `amenities` WHERE `id` = 6);

