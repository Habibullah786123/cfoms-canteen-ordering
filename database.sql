-- ============================================
-- CFOMS Database Schema
-- Café Food Order Management System
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `cfoms`;
USE `cfoms`;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Menu Table
-- ============================================
CREATE TABLE IF NOT EXISTS `menu` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255),
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_name` (`name`),
  INDEX `idx_available` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Orders Table
-- ============================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `items` LONGTEXT NOT NULL COMMENT 'JSON-encoded list of order items (id, name, quantity, price)',
  `total_price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Pending', 'Processing', 'Completed', 'Cancelled') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Inventory Table
-- ============================================
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_name` VARCHAR(100) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `unit` VARCHAR(20) NOT NULL COMMENT 'e.g., pcs, kg, liters',
  `threshold` INT DEFAULT 10 COMMENT 'Stock alert threshold',
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_item_name` (`item_name`),
  INDEX `idx_quantity` (`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Data - Users
-- ============================================
-- Admin user: username: admin, password: admin123
INSERT INTO `users` (`username`, `password_hash`, `role`) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMye6gXI6J7c/e5Z5L.Z/xHe6JTQz8/1T/e', 'admin'),
-- Regular user: username: user1, password: user123
('user1', '$2y$10$N9qo8uLOickgx2ZMRZoMye6gXI6J7c/e5Z5L.Z/xHe6JTQz8/1T/e', 'user'),
-- Regular user: username: user2, password: user123
('user2', '$2y$10$N9qo8uLOickgx2ZMRZoMye6gXI6J7c/e5Z5L.Z/xHe6JTQz8/1T/e', 'user');

-- ============================================
-- Sample Data - Menu Items
-- ============================================
INSERT INTO `menu` (`name`, `description`, `price`, `image`, `is_available`) VALUES
('Margherita Pizza', 'Classic cheese and tomato pizza with fresh basil', 12.99, 'margherita.jpg', 1),
('Veggie Burger', 'Delicious veggie patty with fresh lettuce and tomato', 9.49, 'veggie_burger.jpg', 1),
('Caesar Salad', 'Fresh romaine lettuce with parmesan cheese and croutons', 8.99, 'caesar_salad.jpg', 1),
('Grilled Chicken Sandwich', 'Tender grilled chicken breast with mayo and lettuce', 10.99, 'chicken_sandwich.jpg', 1),
('Chocolate Cake', 'Rich and moist chocolate cake with chocolate frosting', 5.99, 'chocolate_cake.jpg', 1),
('Espresso', 'Strong and bold espresso shot', 3.50, 'espresso.jpg', 1),
('Cappuccino', 'Creamy cappuccino with steamed milk and foam', 4.50, 'cappuccino.jpg', 1),
('Iced Tea', 'Refreshing cold iced tea', 2.99, 'iced_tea.jpg', 1),
('Pasta Carbonara', 'Italian pasta with bacon, egg, and parmesan', 13.99, 'pasta_carbonara.jpg', 1),
('Cheesecake', 'Delicious New York style cheesecake', 6.99, 'cheesecake.jpg', 1);

-- ============================================
-- Sample Data - Inventory
-- ============================================
INSERT INTO `inventory` (`item_name`, `quantity`, `unit`, `threshold`) VALUES
('Tomato', 50, 'kg', 10),
('Cheese', 25, 'kg', 5),
('Flour', 100, 'kg', 20),
('Chicken Breast', 30, 'kg', 10),
('Lettuce', 40, 'pcs', 15),
('Eggs', 200, 'pcs', 50),
('Milk', 100, 'liters', 20),
('Coffee Beans', 50, 'kg', 10),
('Sugar', 50, 'kg', 10),
('Salt', 10, 'kg', 2);

-- ============================================
-- Sample Data - Orders
-- ============================================
INSERT INTO `orders` (`user_id`, `items`, `total_price`, `status`) VALUES
(2, '[{"id": 1, "name": "Margherita Pizza", "quantity": 2, "price": 12.99}]', 25.98, 'Completed'),
(2, '[{"id": 7, "name": "Cappuccino", "quantity": 1, "price": 4.50}]', 4.50, 'Pending'),
(3, '[{"id": 3, "name": "Caesar Salad", "quantity": 1, "price": 8.99}, {"id": 4, "name": "Grilled Chicken Sandwich", "quantity": 1, "price": 10.99}]', 19.98, 'Processing');

-- ============================================
-- Create Indexes for Better Performance
-- ============================================
ALTER TABLE `orders` ADD INDEX `idx_user_created` (`user_id`, `created_at`);
ALTER TABLE `menu` ADD INDEX `idx_price` (`price`);
ALTER TABLE `inventory` ADD INDEX `idx_low_stock` (`quantity`, `threshold`);

-- ============================================
-- Database Setup Complete
-- ============================================
-- Note: All sample passwords are hashed using PASSWORD_DEFAULT (bcrypt)
-- All sample users have the password: user123
-- You can change these after logging in
