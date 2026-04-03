<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

// 1. Update Banners to use .png
$db->query("UPDATE banners SET image = 'assets/images/banners/banner1.png' WHERE id = 1");
$db->query("UPDATE banners SET image = 'assets/images/banners/banner2.png' WHERE id = 2");
$db->query("UPDATE banners SET image = 'assets/images/banners/banner3.png' WHERE id = 3");

// 2. Update specific products with our high-quality generated images
$tomatoes_img = json_encode(['assets/images/products/tomatoes.png']);
$milk_img = json_encode(['assets/images/products/milk.png']);

$db->prepare("UPDATE products SET images = ? WHERE slug = 'fresh-tomatoes'", 's', $tomatoes_img);
$db->prepare("UPDATE products SET images = ? WHERE slug = 'full-cream-milk'", 's', $milk_img);

echo "Database successfully updated to map new e-commerce high resolution graphics!";
