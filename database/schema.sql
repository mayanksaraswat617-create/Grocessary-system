SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- NOTE: Database must be created via InfinityFree Control Panel
-- ============================================================

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
