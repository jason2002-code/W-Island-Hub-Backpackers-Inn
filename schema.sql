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
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@wisland.com', 'admin123', 'admin'),
(2, 'Guest User', 'guest@wisland.com', 'guest123', 'user');

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
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `nights` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_id` (`ref_id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
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
(1, 'Hero Slide 1', 'assets/image/background.jpg', 'hero', 1, 1),
(2, 'Hero Slide 2', 'assets/image/background.jpg', 'hero', 2, 1),
(3, 'Hero Slide 3', 'assets/image/background.jpg', 'hero', 3, 1),
(4, 'Hero Slide 4', 'assets/image/background.jpg', 'hero', 4, 1),
(5, 'Marquee Image 1', 'assets/image/background.jpg', 'marquee', 1, 1),
(6, 'Marquee Image 2', 'assets/image/background.jpg', 'marquee', 2, 1),
(7, 'Marquee Image 3', 'assets/image/background.jpg', 'marquee', 3, 1),
(8, 'Marquee Image 4', 'assets/image/background.jpg', 'marquee', 4, 1),
(9, 'Marquee Image 5', 'assets/image/background.jpg', 'marquee', 5, 1),
(10, 'Marquee Image 6', 'assets/image/background.jpg', 'marquee', 6, 1);
