-- ============================================================
-- GROCEESARY – Seed Data
-- Run AFTER schema.sql
-- ============================================================

USE `groceesary`;

-- ============================================================
-- USERS  (passwords hashed with PHP password_hash())
-- All passwords set to: password
-- ============================================================
INSERT INTO `users` (`name`, `email`, `phone`, `password_hash`, `role`, `is_active`, `email_verified`) VALUES
('Super Admin',    'admin@groceesary.com',    '9000000001', '$2y$10$hVj.yy/P2qT/3yrYMC1dh.sprPrH768cZdKKFcEMlagLzcRDm7LNn.', 'admin',    1, 1),
('Main Admin',     'admin@gmail.com',         '9111111111', '$2y$10$QRF3ROKizHuRGYralyYFLuASC4lS5/Alw/Ot5oNlymDBCZFFLIFnK', 'admin',    1, 1),
('Fresh Farms',    'vendor@groceesary.com',   '9000000002', '$2y$10$hVj.yy/P2qT/3yrYMC1dh.sprPrH768cZdKKFcEMlagLzcRDm7LNn.', 'vendor',   1, 1),
('Organic Basket', 'vendor2@groceesary.com',  '9000000003', '$2y$10$hVj.yy/P2qT/3yrYMC1dh.sprPrH768cZdKKFcEMlagLzcRDm7LNn.', 'vendor',   1, 1),
('Rahul Sharma',   'customer@groceesary.com', '9000000004', '$2y$10$hVj.yy/P2qT/3yrYMC1dh.sprPrH768cZdKKFcEMlagLzcRDm7LNn.', 'customer', 1, 1)
ON DUPLICATE KEY UPDATE `password_hash` = VALUES(`password_hash`);

-- ============================================================
-- VENDORS
-- ============================================================
INSERT INTO `vendors` (`user_id`, `shop_name`, `shop_description`, `shop_address`, `city`, `state`, `pincode`,
  `verification_status`, `kyc_status`, `commission_rate`, `delivery_time`, `min_order_amount`) VALUES
((SELECT `id` FROM `users` WHERE `email` = 'vendor@groceesary.com'), 'Fresh Farms', 'Premium fresh vegetables and fruits delivered to your doorstep.',
 '42, Green Market Lane, Banjara Hills', 'Hyderabad', 'Telangana', '500034', 'approved', 'approved', 8.00, '30-45 min', 100.00),
((SELECT `id` FROM `users` WHERE `email` = 'vendor2@groceesary.com'), 'Organic Basket', 'Certified organic groceries from local farmers.',
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
-- PRODUCTS
-- ============================================================
-- Using subqueries to avoid hardcoded IDs
INSERT INTO `products` (`vendor_id`, `category_id`, `name`, `slug`, `description`, `price`, `discount_price`, `unit`, `stock`, `is_active`, `is_featured`) VALUES
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Fresh Farms'), (SELECT `id` FROM `categories` WHERE `slug` = 'fruits-vegetables'), 'Fresh Tomatoes', 'fresh-tomatoes', 'Farm-fresh red tomatoes, perfect for cooking.', 35.00, 30.00, '500g', 200, 1, 1),
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Fresh Farms'), (SELECT `id` FROM `categories` WHERE `slug` = 'fruits-vegetables'), 'Baby Spinach', 'baby-spinach', 'Tender organic baby spinach leaves.', 55.00, 49.00, '250g', 150, 1, 1),
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Fresh Farms'), (SELECT `id` FROM `categories` WHERE `slug` = 'fruits-vegetables'), 'Alphonso Mangoes', 'alphonso-mangoes', 'Premium Ratnagiri Alphonso mangoes.', 299.00, 249.00, '1kg', 80, 1, 1),
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Fresh Farms'), (SELECT `id` FROM `categories` WHERE `slug` = 'dairy-eggs'), 'Full Cream Milk', 'full-cream-milk', 'Fresh pasteurized full cream cow milk.', 65.00, 60.00, '1L', 300, 1, 1),
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Fresh Farms'), (SELECT `id` FROM `categories` WHERE `slug` = 'dairy-eggs'), 'Paneer Block', 'paneer-block', 'Soft homemade-style cottage cheese.', 120.00, 110.00, '200g', 100, 1, 1),
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Organic Basket'), (SELECT `id` FROM `categories` WHERE `slug` = 'fruits-vegetables'), 'Organic Carrots', 'organic-carrots', 'Certified organic carrots, pesticide-free.', 75.00, 65.00, '500g', 180, 1, 1),
((SELECT `id` FROM `vendors` WHERE `shop_name` = 'Organic Basket'), (SELECT `id` FROM `categories` WHERE `slug` = 'dairy-eggs'), 'Farm Eggs', 'farm-eggs', 'Free-range country eggs.', 85.00, 75.00, '12pcs', 200, 1, 1)
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
-- SAMPLE ADDRESS
-- ============================================================
INSERT INTO `addresses` (`user_id`, `label`, `full_name`, `phone`, `line1`, `city`, `state`, `pincode`, `is_default`) VALUES
((SELECT `id` FROM `users` WHERE `email` = 'customer@groceesary.com'), 'Home', 'Rahul Sharma', '9000000004', '12, Lake View Apartments, Jubilee Hills', 'Hyderabad', 'Telangana', '500033', 1)
ON DUPLICATE KEY UPDATE `id`=`id`;
