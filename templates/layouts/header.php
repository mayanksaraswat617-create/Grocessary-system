<?php
/**
 * Universal Page Header
 * Include at the very top of each page.
 * Requires: $page_title, $page_description (optional), BASE_URL defined.
 */
$base            = BASE_URL;
$page_title      = $page_title      ?? APP_NAME;
$page_description = $page_description ?? 'Fresh groceries from local vendors, delivered to your doorstep.';
$extra_css       = $extra_css       ?? '';
$body_class      = $body_class      ?? 'has-navbar';
$hide_navbar     = $hide_navbar     ?? false;

if ($hide_navbar) $body_class .= ' no-navbar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"   content="<?= htmlspecialchars($page_description) ?>">
  <meta name="theme-color"   content="#FF6B35">
  <meta name="base-url"      content="<?= $base ?>">
  <meta name="csrf-token"    content="<?= CSRF_TOKEN ?>">

  <title><?= htmlspecialchars($page_title) ?> – <?= APP_NAME ?></title>

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- CSS -->
  <link rel="stylesheet" href="<?= $base ?>/templates/css/base.css">
  <link rel="stylesheet" href="<?= $base ?>/templates/css/layout.css">
  <link rel="stylesheet" href="<?= $base ?>/templates/css/components.css">
  <link rel="stylesheet" href="<?= $base ?>/templates/css/theme.css">
  <link rel="stylesheet" href="<?= $base ?>/templates/css/responsive.css">

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= $base ?>/assets/images/logos/favicon.ico">

  <?php if ($extra_css): echo $extra_css; endif; ?>
</head>
<body class="<?= trim($body_class) ?>">
