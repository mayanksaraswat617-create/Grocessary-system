-- ============================================================
-- GROCEESARY – Multi-Vendor Grocery Marketplace
-- Database Schema v1.0
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `groceesary` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `groceesary`;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL UNIQUE,
  `phone`        VARCHAR(15),
  `password_hash` VARCHAR(255) NOT NULL,
  `role`         ENUM('customer','vendor','admin') NOT NULL DEFAULT 'customer',
  `avatar`       VARCHAR(255) DEFAULT NULL,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `reset_token`  VARCHAR(100) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role`  (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT,
  `icon`        VARCHAR(255) DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `parent_id`   INT UNSIGNED DEFAULT NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: vendors
-- ============================================================
CREATE TABLE IF NOT EXISTS `vendors` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`             INT UNSIGNED NOT NULL UNIQUE,
  `shop_name`           VARCHAR(150) NOT NULL,
  `shop_description`    TEXT,
  `shop_address`        TEXT,
  `city`                VARCHAR(100),
  `state`               VARCHAR(100),
  `pincode`             VARCHAR(10),
  `shop_logo`           VARCHAR(255) DEFAULT NULL,
  `shop_banner`         VARCHAR(255) DEFAULT NULL,
  `aadhar_no`           VARCHAR(20) DEFAULT NULL,
  `pan_no`              VARCHAR(20) DEFAULT NULL,
  `bank_account`        VARCHAR(30) DEFAULT NULL,
  `bank_ifsc`           VARCHAR(15) DEFAULT NULL,
  `bank_name`           VARCHAR(100) DEFAULT NULL,
  `aadhar_doc`          VARCHAR(255) DEFAULT NULL,
  `pan_doc`             VARCHAR(255) DEFAULT NULL,
  `verification_status` ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `kyc_status`          ENUM('pending','submitted','approved','rejected') NOT NULL DEFAULT 'pending',
  `kyc_notes`           TEXT DEFAULT NULL,
  `commission_rate`     DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `total_sales`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `avg_rating`          DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews`       INT UNSIGNED NOT NULL DEFAULT 0,
  `delivery_time`       VARCHAR(50) DEFAULT '30-60 min',
  `min_order_amount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_verification` (`verification_status`),
  INDEX `idx_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id`      INT UNSIGNED NOT NULL,
  `category_id`    INT UNSIGNED NOT NULL,
  `name`           VARCHAR(200) NOT NULL,
  `slug`           VARCHAR(220) NOT NULL,
  `description`    TEXT,
  `price`          DECIMAL(10,2) NOT NULL,
  `discount_price` DECIMAL(10,2) DEFAULT NULL,
  `unit`           VARCHAR(30) DEFAULT 'piece',
  `stock`          INT NOT NULL DEFAULT 0,
  `images`         JSON DEFAULT NULL,
  `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured`    TINYINT(1) NOT NULL DEFAULT 0,
  `is_flagged`     TINYINT(1) NOT NULL DEFAULT 0,
  `views`          INT UNSIGNED NOT NULL DEFAULT 0,
  `avg_rating`     DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews`  INT UNSIGNED NOT NULL DEFAULT 0,
  `weight`         DECIMAL(8,3) DEFAULT NULL,
  `tags`           VARCHAR(500) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
  UNIQUE KEY `uniq_vendor_slug` (`vendor_id`, `slug`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_is_featured` (`is_featured`),
  FULLTEXT INDEX `ft_search` (`name`, `description`, `tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: cart
-- ============================================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `vendor_id`  INT UNSIGNED NOT NULL,
  `quantity`   INT UNSIGNED NOT NULL DEFAULT 1,
  `added_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vendor_id`)  REFERENCES `vendors`(`id`)  ON DELETE CASCADE,
  UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: addresses
-- ============================================================
CREATE TABLE IF NOT EXISTS `addresses` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT UNSIGNED NOT NULL,
  `label`        VARCHAR(50) DEFAULT 'Home',
  `full_name`    VARCHAR(100) NOT NULL,
  `phone`        VARCHAR(15) NOT NULL,
  `line1`        VARCHAR(200) NOT NULL,
  `line2`        VARCHAR(200) DEFAULT NULL,
  `city`         VARCHAR(100) NOT NULL,
  `state`        VARCHAR(100) NOT NULL,
  `pincode`      VARCHAR(10) NOT NULL,
  `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: orders
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number`   VARCHAR(20) NOT NULL UNIQUE,
  `user_id`        INT UNSIGNED NOT NULL,
  `address_id`     INT UNSIGNED DEFAULT NULL,
  `delivery_name`  VARCHAR(100),
  `delivery_phone` VARCHAR(15),
  `delivery_address` TEXT,
  `subtotal`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount`   DECIMAL(12,2) NOT NULL,
  `payment_method` ENUM('cod','upi','card','wallet') NOT NULL DEFAULT 'cod',
  `payment_status` ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_ref`    VARCHAR(100) DEFAULT NULL,
  `order_status`   ENUM('placed','confirmed','processing','shipped','delivered','cancelled','disputed') NOT NULL DEFAULT 'placed',
  `delivery_slot`  VARCHAR(100) DEFAULT NULL,
  `notes`          TEXT DEFAULT NULL,
  `is_disputed`    TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  INDEX `idx_user`   (`user_id`),
  INDEX `idx_status` (`order_status`),
  INDEX `idx_payment_status` (`payment_status`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`    INT UNSIGNED NOT NULL,
  `vendor_id`   INT UNSIGNED NOT NULL,
  `product_id`  INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `product_image` VARCHAR(255) DEFAULT NULL,
  `quantity`    INT UNSIGNED NOT NULL,
  `unit_price`  DECIMAL(10,2) NOT NULL,
  `subtotal`    DECIMAL(12,2) NOT NULL,
  `commission`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `vendor_earning` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `item_status` ENUM('pending','accepted','rejected','packed','shipped','delivered') NOT NULL DEFAULT 'pending',
  `notes`       TEXT DEFAULT NULL,
  FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`vendor_id`)  REFERENCES `vendors`(`id`)  ON DELETE RESTRICT,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
  INDEX `idx_order`  (`order_id`),
  INDEX `idx_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id`  INT UNSIGNED NOT NULL,
  `vendor_id`   INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `order_id`    INT UNSIGNED NOT NULL,
  `rating`      TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `comment`     TEXT,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vendor_id`)  REFERENCES `vendors`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  UNIQUE KEY `uniq_user_product_order` (`user_id`, `product_id`, `order_id`),
  INDEX `idx_product` (`product_id`),
  INDEX `idx_vendor`  (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: commissions
-- ============================================================
CREATE TABLE IF NOT EXISTS `commissions` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id`   INT UNSIGNED DEFAULT NULL COMMENT 'NULL = global rule',
  `min_amount`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_amount`  DECIMAL(10,2) DEFAULT NULL,
  `rate`        DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payouts
-- ============================================================
CREATE TABLE IF NOT EXISTS `payouts` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id`     INT UNSIGNED NOT NULL,
  `amount`        DECIMAL(12,2) NOT NULL,
  `status`        ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `payment_ref`   VARCHAR(100) DEFAULT NULL,
  `notes`         TEXT DEFAULT NULL,
  `requested_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at`  DATETIME DEFAULT NULL,
  `processed_by`  INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (`vendor_id`)    REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`)   ON DELETE SET NULL,
  INDEX `idx_vendor` (`vendor_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `message`    TEXT NOT NULL,
  `type`       ENUM('order','payout','system','kyc','review') NOT NULL DEFAULT 'system',
  `link`       VARCHAR(300) DEFAULT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: banners
-- ============================================================
CREATE TABLE IF NOT EXISTS `banners` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(200) NOT NULL,
  `subtitle`   VARCHAR(300) DEFAULT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `link`       VARCHAR(300) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: fraud_flags
-- ============================================================
CREATE TABLE IF NOT EXISTS `fraud_flags` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `type`        ENUM('high_returns','unusual_orders','suspicious_account','payment_fraud') NOT NULL,
  `description` TEXT,
  `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
  `resolved_by` INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`resolved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================================
-- GROCEESARY – Seed Data
-- Run AFTER schema.sql
-- ============================================================

USE `groceesary`;

-- ============================================================
-- USERS  (passwords hashed with PHP password_hash())
-- Admin@123  → $2y$10$...
-- ============================================================
INSERT INTO `users` (`name`, `email`, `phone`, `password_hash`, `role`, `is_active`, `email_verified`) VALUES
('Super Admin',    'admin@groceesary.com',    '9000000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',    1, 1),
('Fresh Farms',    'vendor@groceesary.com',   '9000000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor',   1, 1),
('Organic Basket', 'vendor2@groceesary.com',  '9000000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor',   1, 1),
('Rahul Sharma',   'customer@groceesary.com', '9000000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 1, 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- NOTE: password for ALL seeded users is:  password
-- For production use, regenerate with password_hash('YourPassword', PASSWORD_BCRYPT)

-- ============================================================
-- VENDORS
-- ============================================================
INSERT INTO `vendors` (`user_id`, `shop_name`, `shop_description`, `shop_address`, `city`, `state`, `pincode`,
  `verification_status`, `kyc_status`, `commission_rate`, `delivery_time`, `min_order_amount`) VALUES
(2, 'Fresh Farms', 'Premium fresh vegetables and fruits delivered to your doorstep.',
 '42, Green Market Lane, Banjara Hills', 'Hyderabad', 'Telangana', '500034', 'approved', 'approved', 8.00, '30-45 min', 100.00),
(3, 'Organic Basket', 'Certified organic groceries from local farmers.',
 '15, Anna Nagar West, Chennai', 'Chennai', 'Tamil Nadu', '600040', 'approved', 'approved', 10.00, '45-60 min', 150.00)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- ============================================================
-- CATEGORIES
-- ============================================================
INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`, `is_active`) VALUES
('Fruits & Vegetables', 'fruits-vegetables', 'Fresh seasonal produce', 1, 1),
('Dairy & Eggs',        'dairy-eggs',        'Milk, cheese, butter & eggs', 2, 1),
('Staples & Grains',    'staples-grains',    'Rice, wheat, pulses & flour', 3, 1),
('Snacks & Beverages',  'snacks-beverages',  'Chips, juices, soft drinks', 4, 1),
('Meat & Seafood',      'meat-seafood',      'Fresh chicken, fish & mutton', 5, 1),
('Bakery',              'bakery',            'Bread, cakes, biscuits', 6, 1),
('Frozen Foods',        'frozen-foods',      'Ice cream, frozen vegetables', 7, 1),
('Personal Care',       'personal-care',     'Shampoo, soap, skin care', 8, 1),
('Household',           'household',         'Cleaning supplies, laundry', 9, 1),
('Baby Care',           'baby-care',         'Baby food, diapers, care products', 10, 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- ============================================================
-- PRODUCTS (vendor 1 = id 1, vendor 2 = id 2)
-- ============================================================
INSERT INTO `products` (`vendor_id`, `category_id`, `name`, `slug`, `description`, `price`, `discount_price`, `unit`, `stock`, `is_active`, `is_featured`) VALUES
-- Fresh Farms (vendor_id=1)
(1, 1, 'Fresh Tomatoes',      'fresh-tomatoes',      'Farm-fresh red tomatoes, perfect for cooking.',   35.00,  30.00, '500g', 200, 1, 1),
(1, 1, 'Baby Spinach',        'baby-spinach',        'Tender organic baby spinach leaves.',              55.00,  49.00, '250g', 150, 1, 1),
(1, 1, 'Alphonso Mangoes',    'alphonso-mangoes',    'Premium Ratnagiri Alphonso mangoes.',             299.00, 249.00, '1kg',   80, 1, 1),
(1, 2, 'Full Cream Milk',     'full-cream-milk',     'Fresh pasteurized full cream cow milk.',           65.00,  60.00, '1L',   300, 1, 1),
(1, 2, 'Paneer Block',        'paneer-block',        'Soft homemade-style cottage cheese.',             120.00, 110.00, '200g', 100, 1, 1),
(1, 3, 'Basmati Rice',        'basmati-rice',        'Premium aged Basmati rice, extra long grain.',    180.00, 165.00, '1kg',  250, 1, 1),
(1, 3, 'Toor Dal',            'toor-dal',            'High quality split pigeon peas.',                  99.00,  89.00, '500g', 200, 1, 0),
(1, 4, 'Orange Juice',        'orange-juice',        '100% natural cold-pressed orange juice.',          85.00,  75.00, '1L',   120, 1, 1),
(1, 6, 'Whole Wheat Bread',   'whole-wheat-bread',   'Soft whole wheat loaf, no preservatives.',         45.00,  40.00, 'loaf', 100, 1, 0),
(1, 1, 'Red Onions',          'red-onions',          'Fresh locally sourced red onions.',                40.00,  35.00, '1kg',  500, 1, 0),
-- Organic Basket (vendor_id=2)
(2, 1, 'Organic Carrots',     'organic-carrots',     'Certified organic carrots, pesticide-free.',       75.00,  65.00, '500g', 180, 1, 1),
(2, 1, 'Broccoli Head',       'broccoli-head',       'Fresh green broccoli, rich in vitamins.',          89.00,  79.00, 'piece',  90, 1, 1),
(2, 2, 'Farm Eggs',           'farm-eggs',           'Free-range country eggs.',                         85.00,  75.00, '12pcs',200, 1, 1),
(2, 2, 'Greek Yogurt',        'greek-yogurt',        'Thick, creamy Greek-style yogurt.',               110.00,  99.00, '400g', 150, 1, 0),
(2, 3, 'Organic Brown Rice',  'organic-brown-rice',  'Unpolished organic brown rice.',                  160.00, 149.00, '1kg',  200, 1, 1),
(2, 4, 'Green Tea',           'green-tea',           'Premium Darjeeling green tea bags.',               220.00, 199.00, '25bags',100,1, 0),
(2, 5, 'Fresh Chicken',       'fresh-chicken',       'Antibiotic-free broiler chicken, cleaned.',       250.00, 230.00, '500g', 100, 1, 1),
(2, 7, 'Mixed Vegetables',    'mixed-vegetables',    'Frozen mixed vegetables, ready to cook.',          89.00,  79.00, '500g', 150, 1, 0),
(2, 8, 'Neem Face Wash',      'neem-face-wash',      'Natural neem and tulsi herbal face wash.',        149.00, 129.00, '100ml', 80, 1, 0),
(2, 9, 'Dishwash Liquid',     'dishwash-liquid',     'Eco-friendly lemon dishwash liquid.',              89.00,  75.00, '500ml',120, 1, 0)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- ============================================================
-- BANNERS
-- ============================================================
INSERT INTO `banners` (`title`, `subtitle`, `image`, `link`, `sort_order`, `is_active`) VALUES
('Fresh Groceries, Delivered Fast', 'Order from local vendors in your neighborhood', 'assets/images/banners/banner1.jpg', 'pages/customer/browse.php', 1, 1),
('Organic & Natural Products',      'Certified organic, straight from the farm',      'assets/images/banners/banner2.jpg', 'pages/customer/browse.php?category=1', 2, 1),
('Earn More, Sell More',            'Join as a vendor and grow your business',        'assets/images/banners/banner3.jpg', 'pages/vendor/onboarding.php', 3, 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- ============================================================
-- DEFAULT COMMISSION RULE
-- ============================================================
INSERT INTO `commissions` (`vendor_id`, `min_amount`, `max_amount`, `rate`, `is_active`) VALUES
(NULL, 0.00,    500.00, 10.00, 1),
(NULL, 500.01, 2000.00,  8.00, 1),
(NULL, 2000.01,    NULL,  6.00, 1)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- ============================================================
-- SAMPLE ADDRESS for customer
-- ============================================================
INSERT INTO `addresses` (`user_id`, `label`, `full_name`, `phone`, `line1`, `city`, `state`, `pincode`, `is_default`) VALUES
(4, 'Home', 'Rahul Sharma', '9000000004', '12, Lake View Apartments, Jubilee Hills', 'Hyderabad', 'Telangana', '500033', 1)
ON DUPLICATE KEY UPDATE `id`=`id`;
